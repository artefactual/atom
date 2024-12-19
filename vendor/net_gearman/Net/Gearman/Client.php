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
 * A client for submitting jobs to Gearman
 *
 * This class is used by code submitting jobs to the Gearman server. It handles
 * taking tasks and sets of tasks and submitting them to the Gearman server.
 *
 * @category  Net
 * @package   Net_Gearman
 * @author    Joe Stump <joe@joestump.net>
 * @author    Brian Moon <brianm@dealnews.com>
 * @copyright 2007-2008 Digg.com, Inc.
 * @link      https://github.com/brianlmoon/net_gearman
 */
class Net_Gearman_Client
{
    /**
     * Our randomly selected connection
     *
     * @var resource $conn An open socket to Gearman
     */
    protected $conn = array();

    /**
     * A list of Gearman servers
     *
     * @var array $servers A list of potential Gearman servers
     */
    protected $servers = array();

    /**
     * The timeout for Gearman connections
     *
     * @var integer $timeout
     */
    protected $timeout = 1000;

    /**
     * Callbacks array for receiving connection status
     *
     * @var array $callback
     */
    protected $callback = array();

    /**
     * Constructor
     *
     * @param array   $servers An array of servers or a single server
     * @param integer $timeout Timeout in miliseconds for the server connect time
     *                         If multiple servers have to be tried, the total
     *                         timeout for getConnection will be $timeout * {servers tried}
     *
     * @return void
     * @throws Net_Gearman_Exception
     * @see Net_Gearman_Connection
     */
    public function __construct($servers, $timeout = 1000)
    {
        if (!is_array($servers) && strlen($servers) > 0) {
            $servers = array($servers);
        } elseif (is_array($servers) && !count($servers)) {
            throw new Net_Gearman_Exception('Invalid servers specified');
        }

        $this->servers = array_values($servers);

        $this->timeout = $timeout;
    }

    /**
     * Get a connection to a Gearman server
     *
     * @param string  $uniq    The unique id of the job
     * @param array   $servers Optional list of servers to limit use
     *
     * @return resource A connection to a Gearman server
     */
    protected function getConnection($uniq=null, $servers=null)
    {
        $conn = null;

        $start = microtime(true);
        $elapsed = 0;

        if (is_null($servers)) {
            $servers = $this->servers;
        }

        $try_servers = $servers;

        /**
         * Keep a list of the servers actually tried for the error message
         */
        $tried_servers = array();

        while ($conn === null && count($servers) > 0) {
            if (count($servers) === 1) {
                $key = key($servers);
            } elseif ($uniq === null) {
                $key = array_rand($servers);
            } else {
                $key = ord(substr(md5($uniq), -1)) % count($servers);
            }

            $server = $servers[$key];

            $tried_servers[] = $server;

            if (empty($this->conn[$server]) || !$this->conn[$server]->isConnected()) {

                $conn  = null;
                $start = microtime(true);
                $e     = null;

                try {
                    $conn = new Net_Gearman_Connection($server, $this->timeout);
                } catch (Net_Gearman_Exception $e) {
                    $conn = null;
                }

                if (!$conn || !$conn->isConnected()) {
                    $conn = null;
                    unset($servers[$key]);
                    // we need to rekey the array
                    $servers = array_values($servers);
                } else {
                    $this->conn[$server] = $conn;
                    break;
                }

                foreach ($this->callback as $callback) {
                    call_user_func(
                        $callback,
                        $server,
                        $conn !== null,
                        $this->timeout,
                        microtime(true) - $start,
                        $e
                    );
                }

            } else {
                $conn = $this->conn[$server];
            }

            $elapsed = microtime(true) - $start;

        }

        if (empty($conn)) {
            $message = "Failed to connect to a Gearman server. Attempted to connect to ".implode(",", $tried_servers).".";
            if (count($tried_servers) != count($try_servers)) {
                $message.= " Not all servers were tried. Full server list is ".implode(",", $try_servers).".";
            }
            throw new Net_Gearman_Exception($message);
        }

        return $conn;
    }

    /**
     * Attach a callback for connection status
     *
     * @param callback $callback A valid PHP callback
     *
     * @return void
     * @throws Net_Gearman_Exception When an invalid callback is specified.
     */
    public function attachCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Net_Gearman_Exception('Invalid callback specified');
        }
        $this->callback[] = $callback;
    }

    /**
     * Fire off a background task with the given arguments
     *
     * @param string $func Name of job to run
     * @param array  $args First key should be args to send
     *
     * @return void
     * @see Net_Gearman_Task, Net_Gearman_Set
     */
    public function __call($func, array $args = array())
    {
        $send = "";
        if (isset($args[0]) && !empty($args[0])) {
            $send = $args[0];
        }

        $task       = new Net_Gearman_Task($func, $send);
        $task->type = Net_Gearman_Task::JOB_BACKGROUND;

        $set = new Net_Gearman_Set();
        $set->addTask($task);
        $this->runSet($set);
        return $task->handle;
    }

    /**
     * Submit a task to Gearman
     *
     * @param object $task Task to submit to Gearman
     *
     * @return      void
     * @see         Net_Gearman_Task, Net_Gearman_Client::runSet()
     */
    protected function submitTask(Net_Gearman_Task $task)
    {
        switch ($task->type) {
        case Net_Gearman_Task::JOB_LOW:
            $type = 'submit_job_low';
            break;
        case Net_Gearman_Task::JOB_LOW_BACKGROUND:
            $type = 'submit_job_low_bg';
            break;
        case Net_Gearman_Task::JOB_HIGH_BACKGROUND:
            $type = 'submit_job_high_bg';
            break;
        case Net_Gearman_Task::JOB_BACKGROUND:
            $type = 'submit_job_bg';
            break;
        case Net_Gearman_Task::JOB_HIGH:
            $type = 'submit_job_high';
            break;
        default:
            $type = 'submit_job';
            break;
        }

        // if we don't have a scalar
        // json encode the data
        if (!is_scalar($task->arg)) {
            $arg = @json_encode($task->arg);
        } else {
            $arg = $task->arg;
        }

        $params = array(
            'func' => $task->func,
            'uniq' => $task->uniq,
            'arg'  => $arg
        );

        if (!empty($task->servers)) {
            $servers = $task->servers;
        } else {
            $servers = null;
        }

        $conn = $this->getConnection($task->uniq, $servers);
        $conn->send($type, $params);

        $conn->addWaitingTask($task);
    }

    /**
     * Run a set of tasks
     *
     * @param object $set     A set of tasks to run
     * @param int    $timeout Time in seconds for the socket timeout. Max is 10 seconds
     *
     * @return void
     * @see Net_Gearman_Set, Net_Gearman_Task
     */
    public function runSet(Net_Gearman_Set $set, $timeout = null)
    {
        $totalTasks = $set->tasksCount;
        $taskKeys   = array_keys($set->tasks);
        $t          = 0;

        if ($timeout !== null) {
            $socket_timeout = min(10, (int)$timeout);
        } else {
            $socket_timeout = 10;
        }

        while (!$set->finished()) {

            if ($timeout !== null) {

                if (empty($start)) {

                    $start = microtime(true);

                } else {

                    $now = microtime(true);

                    if ($now - $start >= $timeout) {
                        break;
                    }
                }

            }


            if ($t < $totalTasks) {
                $k = $taskKeys[$t];
                $this->submitTask($set->tasks[$k]);
                if ($set->tasks[$k]->type == Net_Gearman_Task::JOB_BACKGROUND ||
                    $set->tasks[$k]->type == Net_Gearman_Task::JOB_HIGH_BACKGROUND ||
                    $set->tasks[$k]->type == Net_Gearman_Task::JOB_LOW_BACKGROUND) {

                    $set->tasks[$k]->finished = true;
                    $set->tasksCount--;
                }

                $t++;
            }

            $write     = null;
            $except    = null;
            $read_cons = array();

            foreach ($this->conn as $conn) {
                $read_conns[] = $conn->socket;
            }

            @socket_select($read_conns, $write, $except, $socket_timeout);

            $error_messages = [];

            foreach ($this->conn as $server => $conn) {
                $err = socket_last_error($conn->socket);
                // Error 11 is EAGAIN and is normal in non-blocking mode
                // Error 35 happens on macOS often enough to be annoying
                if ($err && $err != 11 && $err != 35) {
                    $msg = socket_strerror($err);
                    list($remote_address, $remote_port) = explode(":", $server);
                    $error_messages[] = "socket_select failed: ($err) $msg; server: $remote_address:$remote_port";
                }
                socket_clear_error($conn->socket);
                $resp = $conn->read();
                if (count($resp)) {
                    $this->handleResponse($resp, $conn, $set);
                }
            }

            // if all connections threw errors, throw an exception
            if (count($error_messages) == count($this->conn)) {
                throw new Net_Gearman_Exception(implode("; ", $error_messages));
            }
        }
    }

    /**
     * Handle the response read in
     *
     * @param array    $resp  The raw array response
     * @param resource $s     The socket
     * @param object   $tasks The tasks being ran
     *
     * @return void
     * @throws Net_Gearman_Exception
     */
    protected function handleResponse($resp, $conn, Net_Gearman_Set $tasks)
    {
        if (isset($resp['data']['handle']) &&
            $resp['function'] != 'job_created') {
            $task = $tasks->getTask($resp['data']['handle']);
        }

        switch ($resp['function']) {
        case 'work_complete':
            $tasks->tasksCount--;
            $task->complete(json_decode($resp['data']['result'], true));
            break;
        case 'work_status':
            $n = (int)$resp['data']['numerator'];
            $d = (int)$resp['data']['denominator'];
            $task->status($n, $d);
            break;
        case 'work_fail':
            $tasks->tasksCount--;
            $task->fail();
            break;
        case 'job_created':
            $task         = $conn->getWaitingTask();
            $task->handle = $resp['data']['handle'];
            if ($task->type == Net_Gearman_Task::JOB_BACKGROUND) {
                $task->finished = true;
            }
            $tasks->handles[$task->handle] = $task->uniq;
            break;
        case 'error':
            throw new Net_Gearman_Exception('An error occurred');
        default:
            throw new Net_Gearman_Exception(
                'Invalid function ' . $resp['function']
            );
        }
    }

    /**
     * Disconnect from Gearman
     *
     * @return      void
     */
    public function disconnect()
    {
        if (!is_array($this->conn) || !count($this->conn)) {
            return;
        }

        foreach ($this->conn as $conn) {
            if (is_callable(array($conn, "close"))) {
                $conn->close();
            }
        }

        $this->conn = [];
    }

    /**
     * Destructor
     *
     * @return      void
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Creates a singleton instance of this class for reuse
     *
     * @param array   $servers An array of servers or a single server
     * @param integer $timeout Timeout in microseconds
     *
     * @return object
     *
     */
    public static function getInstance($servers, $timeout = 1000) {

        static $instances;

        $key = md5(json_encode($servers));

        if (!isset($instances[$key])) {
            $instances[$key] = new Net_Gearman_Client($servers, $timeout);
        }

        return $instances[$key];
    }
}
