<?php

/**
 * Interface for Danga's Gearman job scheduling system
 *
 * PHP version 5.1.0+
 *
 * LICENSE: This source file is subject to the New BSD license that is
 * available through the world-wide-web at the following URI:
 * http://www.opensource.org/licenses/bsd-license.php. If you did not receive
 * a copy of the New BSD License and are unable to obtain it through the web,
 * please send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Net
 * @package   Net_Gearman
 * @author    Joe Stump <joe@joestump.net>
 * @author    Brian Moon <brianm@dealnews.com>
 * @copyright 2007-2008 Digg.com, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   CVS: $Id$
 * @link      https://github.com/brianlmoon/net_gearman
 */

/**
 * The base connection class
 *
 * @category  Net
 * @package   Net_Gearman
 * @author    Joe Stump <joe@joestump.net>
 * @author    Brian Moon <brianm@dealnews.com>
 * @copyright 2007-2008 Digg.com, Inc.
 * @link      https://github.com/brianlmoon/net_gearman
 */
class Net_Gearman_Connection
{
    /**
     * A list of valid Gearman commands
     *
     * This is a list of valid Gearman commands (the key of the array), their
     * integery type (first key in second array) used in the binary header, and
     * the arguments / order of arguments to send/receive.
     *
     * @var array $commands
     * @see Net_Gearman_Connection::$magic
     * @see Net_Gearman_Connection::connect()
     */
    protected $commands = array(
        'can_do' => array(1, array('func')),
        'can_do_timeout' => array(23, array('func', 'timeout')),
        'cant_do' => array(2, array('func')),
        'reset_abilities' => array(3, array()),
        'set_client_id' => array(22, array('client_id')),
        'pre_sleep' => array(4, array()),
        'noop' => array(6, array()),
        'submit_job' => array(7, array('func', 'uniq', 'arg')),
        'submit_job_high' => array(21, array('func', 'uniq', 'arg')),
        'submit_job_bg' => array(18, array('func', 'uniq', 'arg')),
        'submit_job_high_bg' => array(32, array('func', 'uniq', 'arg')),
        'submit_job_low' => array(33, array('func', 'uniq', 'arg')),
        'submit_job_low_bg' => array(34, array('func', 'uniq', 'arg')),
        'job_created' => array(8, array('handle')),
        'grab_job' => array(9, array()),
        'no_job' => array(10, array()),
        'job_assign' => array(11, array('handle', 'func', 'arg')),
        'work_status' => array(12, array('handle', 'numerator', 'denominator')),
        'work_complete' => array(13, array('handle', 'result')),
        'work_fail' => array(14, array('handle')),
        'get_status' => array(15, array('handle')),
        'status_res' => array(20, array('handle', 'known', 'running', 'numerator', 'denominator')),
        'echo_req' => array(16, array('text')),
        'echo_res' => array(17, array('text')),
        'error' => array(19, array('err_code', 'err_text')),
        'all_yours' => array(24, array())
    );

    /**
     * The reverse of Net_Gearman_Connection::$commands
     *
     * This is the same as the Net_Gearman_Connection::$commands array only
     * it's keyed by the magic (integer value) value of the command.
     *
     * @var array $magic
     * @see Net_Gearman_Connection::$commands
     * @see Net_Gearman_Connection::connect()
     */
    protected $magic = array();

    /**
     * Tasks waiting for a handle
     *
     * Tasks are popped onto this queue as they're submitted so that they can
     * later be popped off of the queue once a handle has been assigned via
     * the job_created command.
     *
     * @access      public
     * @var         array           $waiting
     * @static
     */
    protected $waiting = array();

    /**
     * Is PHP's multibyte overload turned on?
     *
     * @var integer $multiByteSupport
     */
    static protected $multiByteSupport = null;

    public $socket;

    /**
     * Gearmand Server Version
     *
     * @var        string
     */
    protected $serverVersion;

    public function __construct($host=null, $timeout=250) {
        if ($host) {
            $this->connect($host, $timeout);
        }
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * Connect to Gearman
     *
     * Opens the socket to the Gearman Job server. It throws an exception if
     * a socket error occurs. Also populates Net_Gearman_Connection::$magic.
     *
     * @param string              $host    e.g. 127.0.0.1 or 127.0.0.1:7003
     * @param int                 $timeout Timeout in milliseconds
     *
     * @return resource A connection to a Gearman server
     * @throws Net_Gearman_Exception when it can't connect to server
     * @see Net_Gearman_Connection::$waiting
     * @see Net_Gearman_Connection::$magic
     * @see Net_Gearman_Connection::$commands
     */
    public function connect($host, $timeout = 250)
    {

        $this->close();

        if (!count($this->magic)) {
            foreach ($this->commands as $cmd => $i) {
                $this->magic[$i[0]] = array($cmd, $i[1]);
            }
        }

        if (strpos($host, ':')) {
            list($host, $port) = explode(':', $host);
        } else {
            $port  = 4730;
        }

        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        /**
         * Set the send and receive timeouts super low so that socket_connect
         * will return to us quickly. We then loop and check the real timeout
         * and check the socket error to decide if its connected yet or not.
         */
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>0, "usec" => 100));
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>0, "usec" => 100));

        /**
         * Explicitly set this to blocking which should be the default
         */
        socket_set_block($this->socket);

        $now = microtime(true);
        $waitUntil = $now + $timeout / 1000;

        /**
         * Loop calling socket_connect. As long as the error is 115 (in progress)
         * or 114 (already called) and our timeout has not been reached, keep
         * trying.
         */
        $socket_connected = false;
        do {
            socket_clear_error($this->socket);
            $socket_connected = @socket_connect($this->socket, $host, $port);
            $err = @socket_last_error($this->socket);
        }
        while (($err === 115 || $err === 114) && (microtime(true) < $waitUntil));

        $elapsed = microtime(true) - $now;

        /**
         * For some reason, socket_connect can return true even when it is
         * not connected. Make sure it returned true the last error is zero
         */
        $socket_connected = $socket_connected && $err === 0;


        if ($socket_connected) {
            socket_set_nonblock($this->socket);

            socket_set_option($this->socket, SOL_SOCKET, SO_KEEPALIVE, 1);

            /**
             * set the real send/receive timeouts here now that we are connected
             */
            $timeout = self::calculateTimeout($timeout);
            socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>$timeout[0], "usec" => $timeout[1]));
            socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$timeout[0], "usec" => $timeout[1]));

            // socket_set_option($this->socket, SOL_TCP, SO_DEBUG, 1); // Debug

            $this->setServerVersion($host);

         } else {

            $errno = @socket_last_error($this->socket);
            $errstr = @socket_strerror($errno);

            /**
             * close the socket just in case it
             * is somehow alive to some degree
             */
            $this->close();

            throw new Net_Gearman_Exception(
                "Can't connect to server ($errno: $errstr)"
            );
        }

        $this->waiting = array();

    }

    public function addWaitingTask($task) {
        $this->waiting[] = $task;
    }

    public function getWaitingTask() {
        return array_shift($this->waiting);
    }

    /**
     * Send a command to Gearman
     *
     * This is the command that takes the string version of the command you
     * wish to run (e.g. 'can_do', 'grab_job', etc.) along with an array of
     * parameters (in key value pairings) and packs it all up to send across
     * the socket.
     *
     * @param string   $command Command to send (e.g. 'can_do')
     * @param array    $params  Params to send
     *
     * @see Net_Gearman_Connection::$commands, Net_Gearman_Connection::$this->socket
     * @return boolean
     * @throws Net_Gearman_Exception on invalid command or unable to write
     */
    public function send($command, array $params = array())
    {
        if (!isset($this->commands[$command])) {
            throw new Net_Gearman_Exception('Invalid command: ' . $command);
        }

        if ($command === 'can_do_timeout') {
            $params = $this->fixTimeout($params);
        }

        $data = array();
        foreach ($this->commands[$command][1] as $field) {
            if (isset($params[$field])) {
                $data[] = $params[$field];
            }
        }

        $d = implode("\x00", $data);

        $cmd = "\0REQ" . pack("NN",
                              $this->commands[$command][0],
                              $this->stringLength($d)) . $d;

        $cmdLength = $this->stringLength($cmd);
        $written = 0;
        $error = false;
        do {
            $check = @socket_write($this->socket,
                                   $this->subString($cmd, $written, $cmdLength),
                                   $cmdLength);

            if ($check === false) {
                if (socket_last_error($this->socket) == SOCKET_EAGAIN or
                    socket_last_error($this->socket) == SOCKET_EWOULDBLOCK or
                    socket_last_error($this->socket) == SOCKET_EINPROGRESS)
                {
                  // skip this is okay
                }
                else
                {
                    $error = true;
                    break;
                }
            }

            $written += (int)$check;
        } while ($written < $cmdLength);

        if ($error === true) {
            $errno = @socket_last_error($this->socket);
            $errstr = @socket_strerror($errno);
            throw new Net_Gearman_Exception(
                "Could not write command to socket ($errno: $errstr)"
            );
        }
    }

    /**
     * Read command from Gearman
     *
     * @see Net_Gearman_Connection::$magic
     * @return array Result read back from Gearman
     * @throws Net_Gearman_Exception connection issues or invalid responses
     */
    public function read()
    {
        $header = '';
        do {
            $buf = @socket_read($this->socket, 12 - $this->stringLength($header));
            $header .= $buf;
        } while ($buf !== false &&
                 $buf !== '' && $this->stringLength($header) < 12);

        if ($buf === '') {
            throw new Net_Gearman_Exception("Connection was reset");
        }

        if ($this->stringLength($header) == 0) {
            return array();
        }
        $resp = @unpack('a4magic/Ntype/Nlen', $header);

        if (!count($resp) == 3) {
            throw new Net_Gearman_Exception('Received an invalid response');
        }

        if (!isset($this->magic[$resp['type']])) {
            throw new Net_Gearman_Exception(
                'Invalid response magic returned: ' . $resp['type']
            );
        }

        $return = array();
        if ($resp['len'] > 0) {
            $data = '';
            while ($this->stringLength($data) < $resp['len']) {
                $data .= @socket_read($this->socket, $resp['len'] - $this->stringLength($data));
            }

            $d = explode("\x00", $data);
            foreach ($this->magic[$resp['type']][1] as $i => $a) {
                $return[$a] = $d[$i];
            }
        }

        $function = $this->magic[$resp['type']][0];
        if ($function == 'error') {
            if (!$this->stringLength($return['err_text'])) {
                $return['err_text'] = 'Unknown error; see error code.';
            }

            throw new Net_Gearman_Exception("({$return['err_code']}): {$return['err_text']}");
        }

        return array('function' => $this->magic[$resp['type']][0],
                     'type' => $resp['type'],
                     'data' => $return);
    }

    /**
     * Blocking socket read
     *
     * @param float    $timeout The timeout for the read
     *
     * @throws Net_Gearman_Exception on timeouts
     * @return array
     */
    public function blockingRead($timeout = 250)
    {
        $write  = null;
        $except = null;
        $read   = array($this->socket);

        $timeout = self::calculateTimeout($timeout);

        socket_clear_error($this->socket);
        $success = @socket_select($read, $write, $except, $timeout[0], $timeout[1]);
        if ($success === false) {
            $errno = @socket_last_error($this->socket);
            if ($errno != 0) {
                throw new Net_Gearman_Exception("Socket error: ($errno) ".socket_strerror($errno));
            }
        }

        if ($success === 0) {
            $errno = @socket_last_error($this->socket);
            throw new Net_Gearman_Exception(
                sprintf("Socket timeout (%.4fs, %.4fμs): (%s)", $timeout[0], $timeout[1], socket_strerror($errno))
            );
        }

        $cmd = $this->read();

        return $cmd;
    }

    /**
     * Close the connection
     *
     * @return void
     */
    public function close()
    {
        if (isset($this->socket) && is_resource($this->socket)) {

            socket_clear_error($this->socket);

            socket_set_block($this->socket);

            socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>0, "usec" => 500));
            socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>0, "usec" => 500));

            @socket_shutdown($this->socket);

            $err = socket_last_error($this->socket);
            if ($err != 0) {
                if ($err == 107) {
                    // 107 means Transport endpoint is not connected
                    unset($this->socket);
                    return;
                }
                throw new Net_Gearman_Exception("Socket error: ($err) ".socket_strerror($err));
            }

            /**
             * Read anything left on the buffer that we didn't get
             * due to a timeout or something
             */
            do {
                $err = 0;
                $buf = "";
                socket_clear_error($this->socket);
                socket_close($this->socket);
                if (isset($this->socket) && is_resource($this->socket)) {
                    $err = socket_last_error($this->socket);
                    // Check for EAGAIN error
                    // 11 on Linux
                    // 35 on BSD
                    if ($err == 11 || $err == 35) {
                        $buf = @socket_read($this->socket, 8192);
                        $err = socket_last_error($this->socket);
                    } else {
                        // Some other error was returned. We need to
                        // terminate the socket and get out. To do this,
                        // we set SO_LINGER to {on, 0} which causes
                        // the connection to be aborted.
                        socket_set_option(
                            $this->socket,
                            SOL_SOCKET,
                            SO_LINGER,
                            array(
                                'l_onoff' => 1,
                                'l_linger' => 0
                            )
                        );
                        socket_close($this->socket);
                        $err = 0;
                    }
                }
            } while ($err != 0 && strlen($buf) > 0);

            unset($this->socket);
        }
    }

    /**
     * Are we connected?
     *
     * @param resource $conn The connection/socket to check
     *
     * @return boolean False if we aren't connected
     */
    public function isConnected()
    {
        // PHP 8+ returns Socket object instead of resource
        if ($this->socket instanceof \Socket) {
            return true;
        }

        // PHP 5.x-7.x returns socket
        if (is_resource($this->socket) === true) {
            $type = strtolower(get_resource_type($this->socket));
            return $type === 'socket';
        }

        return false;
    }

    /**
     * Calculates the timeout values for socket_select
     *
     * @param  int $milliseconds Timeout in milliseconds
     * @return array The first value is the seconds and the second value
     *               is microseconds
     */
    public static function calculateTimeout($milliseconds)
    {
        if ($milliseconds >= 1000) {
            $ts_seconds = $milliseconds / 1000;
            $tv_sec = floor($ts_seconds);
            $tv_usec = ($ts_seconds - $tv_sec) * 1000000;
        } else {
            $tv_sec = 0;
            $tv_usec = $milliseconds * 1000;
        }
        return [$tv_sec, $tv_usec];
    }

    /**
     * Determine if we should use mb_strlen or stock strlen
     *
     * @param string $value The string value to check
     *
     * @return integer Size of string
     * @see Net_Gearman_Connection::$multiByteSupport
     */
    public static function stringLength($value)
    {
        if (is_null(self::$multiByteSupport)) {
            self::$multiByteSupport = intval(ini_get('mbstring.func_overload'));
        }

        if (self::$multiByteSupport & 2) {
            return mb_strlen($value, '8bit');
        } else {
            return strlen($value);
        }
    }

    /**
     * Multibyte substr() implementation
     *
     * @param string  $str    The string to substr()
     * @param integer $start  The first position used
     * @param integer $length The maximum length of the returned string
     *
     * @return string Portion of $str specified by $start and $length
     * @see Net_Gearman_Connection::$multiByteSupport
     * @link http://us3.php.net/mb_substr
     * @link http://us3.php.net/substr
     */
    public static function subString($str, $start, $length)
    {
        if (is_null(self::$multiByteSupport)) {
            self::$multiByteSupport = intval(ini_get('mbstring.func_overload'));
        }

        if (self::$multiByteSupport & 2) {
            return mb_substr($str, $start, $length, '8bit');
        } else {
            return substr($str, $start, $length);
        }
    }

    /**
     * Sets the server version.
     *
     * @param string              $host     The host
     * @param Net_Gearman_Manager $manager Optional manager object
     */
    protected function setServerVersion($host, $manager = null)
    {
        if (empty($manager)) {
            $manager = new \Net_Gearman_Manager($host);
        }
        $this->serverVersion = $manager->version();
        unset($manager);
    }

    protected function fixTimeout($params) {
        // In gearmand version 1.1.19 and greater, the timeout is
        // expected to be in milliseconds. Before that version, it
        // is expected to be in seconds.
        // https://github.com/gearman/gearmand/issues/196
        if (version_compare('1.1.18', $this->serverVersion)) {
            $params['timeout'] *= 1000;
        }
        return $params;
    }
}
