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
 * @copyright 2007-2008 Digg.com, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Net_Gearman
 * @link      http://www.danga.com/gearman/
 */


/**
 * Gearman worker class
 *
 * Run an instance of a worker to listen for jobs. It then manages the running
 * of jobs, etc.
 *
 * <code>
 * <?php
 *
 * $servers = array(
 *     '127.0.0.1:7003',
 *     '127.0.0.1:7004'
 * );
 *
 * $abilities = array('HelloWorld', 'Foo', 'Bar');
 *
 * try {
 *     $worker = new Net_Gearman_Worker($servers);
 *     foreach ($abilities as $ability) {
 *         $worker->addAbility('HelloWorld');
 *     }
 *     $worker->beginWork();
 * } catch (Net_Gearman_Exception $e) {
 *     echo $e->getMessage() . "\n";
 *     exit;
 * }
 *
 *
 * </code>
 *
 * @category  Net
 * @package   Net_Gearman
 * @author    Joe Stump <joe@joestump.net>
 * @copyright 2007-2008 Digg.com, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   Release: @package_version@
 * @link      http://www.danga.com/gearman/
 * @see       Net_Gearman_Job, Net_Gearman_Connection
 */
class Net_Gearman_Worker
{
    /**
     * Pool of connections to Gearman servers
     *
     * @var array $conn
     */
    protected $conn = array();

    /**
     * Pool of retry connections
     *
     * @var array $conn
     */
    protected $retryConn = array();

    /**
     * List of servers that have failed a connection
     *
     * @var array $conn
     */
    protected $failedConn = array();

    /**
     * Holds a count of jobs done for each server
     *
     * @var array $stats
     */
    protected $stats = array();

    /**
     * Pool of worker abilities
     *
     * @var array $conn
     */
    protected $abilities = array();

    /**
     * Parameters for job contructors, indexed by ability name
     *
     * @var array $initParams
     */
    protected $initParams = array();

    /**
     * Number of seconds to wait to retry a connection after it has failed.
     * If a server is already in the retry list when a new connection is
     * attempted, the retry time for that server will be increased using
     * this value as the base + a multiple.
     *
     * @var float
     */
    protected $retryTime = 3;

    /**
     * The maximum of amount of time in seconds to wait before trying to
     * reconnect to a server.
     *
     * @var integer
     */
    protected $maxRetryTime = 60;

    /**
     * Stores the minimum current retry time of the servers in the retry list.
     * We use this when putting workers into pre_sleep so we can wake up
     * after this time and retry connections.
     *
     * @var integer
     */
    protected $minCurrentRetryTime = null;

    /**
     * The time in seconds to to read on the sockets when we have had no work
     * to do. This prevents the worker from constantly requesting jobs
     * from the server.
     *
     * @var integer
     */
    protected $sleepTime = 30;

    /**
     * Callbacks registered for this worker
     *
     * @var array $callback
     * @see Net_Gearman_Worker::JOB_START
     * @see Net_Gearman_Worker::JOB_COMPLETE
     * @see Net_Gearman_Worker::JOB_FAIL
     * @see Net_Gearman_Worker::WORKER_STATUS
     */
    protected $callback = array(
        self::JOB_START     => array(),
        self::JOB_COMPLETE  => array(),
        self::JOB_FAIL      => array(),
        self::WORKER_STATUS => array(),
    );

    /**
     * Unique id for this worker
     *
     * @var string $id
     */
    protected $id = "";

    /**
     * Socket timeout in milliseconds
     *
     * @var int $socket_timeout
     */
    protected $socket_timeout = 250;

    /**
     * Callback type
     *
     * @const integer JOB_START Runs when a job is started
     */
    const JOB_START         = 1;

    /**
     * Callback type
     *
     * @const integer JOB_COMPLETE Runs when a job is finished
     */
    const JOB_COMPLETE      = 2;

    /**
     * Callback type
     *
     * @const integer JOB_COMPLETE Runs when a job is finished
     */
    const JOB_FAIL          = 3;

    /**
     * Callback type
     *
     * @const integer WORKER_STATUS Runs to send status info for servers
     */
    const WORKER_STATUS = 4;

    /**
     * Constructor
     *
     * @param array $servers        List of servers to connect to
     * @param string $id            Optional unique id for this worker
     * @param int $socket_timeout   Timout for the socket select
     *
     * @return void
     * @throws Net_Gearman_Exception
     * @see Net_Gearman_Connection
     */
    public function __construct($servers, $id = "", $socket_timeout=null)
    {
        if (!is_array($servers) && strlen($servers)) {
            $servers = array($servers);
        } elseif (is_array($servers) && !count($servers)) {
            throw new Net_Gearman_Exception('Invalid servers specified');
        }

        if (empty($id)) {
            $id = "pid_".getmypid()."_".uniqid();
        }

        $this->id = $id;

        if (!is_null($socket_timeout)) {
            if (is_numeric($socket_timeout)) {
                $this->socket_timeout = (int)$socket_timeout;
            } else {
                throw new Net_Gearman_Exception("Invalid valid for socket timeout");
            }
        }

        /**
         * Randomize the server list so all the workers don't try and connect
         * to the same server first causing a connection stampede
         */
        shuffle($servers);

        foreach ($servers as $s) {

            $this->connect($s);

        }

    }


    /**
     * Connects to a gearman server and puts failed connections into the retry
     * list.
     *
     * @param  string $server Server name/ip and optional port to connect
     *
     * @return bool
     */
    private function connect($server) {
        $success = false;

        try {

            /**
             * If this is a reconnect, be sure we close the old connection
             * before making a new one.
             */
            if (isset($this->conn[$server]) && is_resource($this->conn[$server])) {
                $this->close($server);
            }

            $this->conn[$server] = new Net_Gearman_Connection($server, $this->socket_timeout);

            $this->conn[$server]->send("set_client_id", array("client_id" => $this->id));

            $this->addAbilities($this->conn[$server]);

            if (isset($this->retryConn[$server])) {
                unset($this->retryConn[$server]);
                $this->status("Removing server from the retry list.", $server);
            }

            $this->status("Connected to $server", $server);

            $success = true;

        } catch (Net_Gearman_Exception $e) {

            $this->sleepConnection($server);

            $this->status(
                "Connection failed",
                $server
            );
        }

        return $success;

    }

    /**
     * Removes a server from the connection list and adds it to a
     * reconnect list.
     *
     * @param  string $server Server and port
     * @return void
     */
    private function sleepConnection($server) {

        if (isset($this->conn[$server])) {
            $this->close($server);
        }

        if (empty($this->failedConn[$server])) {
            $this->failedConn[$server] = 1;
        } else {
            $this->failedConn[$server]++;
        }

        $waitTime = $this->retryTime($this->failedConn[$server]);
        $this->retryConn[$server] = time() + $waitTime;

        if (is_null($this->minCurrentRetryTime)) {
            $this->minCurrentRetryTime = $waitTime;
        } else {
            $this->minCurrentRetryTime = min(array_values($this->retryConn)) - time();
        }

        $this->status(
            "Putting $server connection to sleep for ".$waitTime." seconds",
            $server
        );
    }

    /**
     * Returns the status of the gearmand connections for this object
     *
     * @return array    An array containing a connected count, disconnected count
     *                  and array that lists each server and true/false for connected
     */
    public function connection_status()
    {
        $servers = array();

        foreach ($this->conn as $server=>$socket) {
            $servers[$server] = true;
        }
        foreach ($this->retryConn as $server=>$status) {
            $servers[$server] = false;
        }

        return array(
            "connected" => count($this->conn),
            "disconnected" => count($this->retryConn),
            "servers" => $servers,
            "stats" => $this->stats
        );
    }


    /**
     * Announce an ability to the job server
     *
     * @param string  $ability    Name of functcion/ability
     * @param integer $timeout    How long to give this job
     * @param array   $initParams Parameters for job constructor
     * @param int     $conn       Optional connection to add ability to. if not set, all
     *                            connections are used
     *
     * @return void
     * @see $conn->send()
     */
    public function addAbility($ability, $timeout = null, $initParams=array(), $conn=null)
    {
        $call   = 'can_do';
        $params = array('func' => $ability);
        if (is_int($timeout) && $timeout > 0) {
            $params['timeout'] = $timeout;
            $call              = 'can_do_timeout';
        }

        $this->initParams[$ability] = $initParams;

        $this->abilities[$ability] = $timeout;

        if ($conn) {
            $conn->send($call, $params);
        } else {
            foreach ($this->conn as $conn) {
                $conn->send($call, $params);
            }
        }
    }

    /**
     * Announce all abilities to all servers or one server
     *
     * @param int     $conn    Optional connection to add ability to. if not set, all
     *                         connections are used
     * @return void
     */
    protected function addAbilities($conn=null)
    {
        foreach ($this->abilities as $ability => $timeout) {
            $this->addAbility(
                $ability, $timeout, $this->initParams[$ability], $conn
            );
        }
    }


    /**
     * Begin working
     *
     * This starts the worker on its journey of actually working. The first
     * argument is a PHP callback to a function that can be used to monitor
     * the worker. If no callback is provided then the worker works until it
     * is killed. The monitor is passed two arguments; whether or not the
     * worker is idle and when the last job was ran. If the monitor returns
     * true, then the worker will stop working.
     *
     * @param  callback $monitor Function to monitor work
     *
     * @return void
     *
     * @see $conn->send(), $conn->connect()
     * @see Net_Gearman_Worker::doWork(), Net_Gearman_Worker::addAbility()
     */
    public function beginWork($monitor = null)
    {
        if (!is_callable($monitor)) {
            $monitor = array($this, 'stopWork');
        }

        $keep_working = true;
        $lastJobTime  = time();

        while ($keep_working) {

            $worked = false;

            $this->retryConnections();

            if (!empty($this->conn)) {
                $worked = $this->askForWork();

                if ($worked) {
                    $lastJobTime = time();
                }
            }

            if ($this->retryConnections()) {
                $sleep = false;
            } else {
                $sleep = !$worked;
            }

            if ($sleep && !empty($this->conn)) {
                $this->waitQuietly($monitor, $lastJobTime);
            }

            if (empty($this->conn)) {
                $this->deepSleep($monitor, $lastJobTime);
            }

            $keep_working = !call_user_func($monitor, !$worked, $lastJobTime);
        }
    }

    /**
     * Monitors the sockets for incoming data which should cause an
     * immediate wake to perform work
     *
     * @return bool True if there was data on any socket; false if not
     */
    protected function waitQuietly($monitor, $lastJobTime)
    {
        // This is sent to notify the server that the worker is about to
        // sleep, and that it should be woken up with a NOOP packet if a
        // job comes in for a function the worker is able to perform.
        foreach ($this->conn as $server => $conn) {
            try {
                $conn->send('pre_sleep');
            } catch (Net_Gearman_Exception $e) {
                $this->sleepConnection($server);
            }
        }

        $this->status(
            "Worker going quiet for ".$this->sleepTime." seconds"
        );

        $idle   = true;
        $write  = null;
        $except = null;

        $wakeTime = time() + $this->sleepTime;

        $socket_timeout = Net_Gearman_Connection::calculateTimeout($this->socket_timeout);

        while ($idle && $wakeTime > time()) {

            if (!empty($this->conn)) {

                foreach ($this->conn as $conn) {
                    $read_conns[] = $conn->socket;
                    socket_clear_error($conn->socket);
                }

                $success = @socket_select($read_conns, $write, $except, $socket_timeout[0], $socket_timeout[1]);

                if (call_user_func($monitor, true, $lastJobTime)) {
                    break;
                }

                // check for errors on any sockets
                if ($success === false) {
                    foreach ($this->conn as $server => $conn) {
                        $errno = socket_last_error($conn->socket);
                        if ($errno > 0) {
                            $this->status(
                                "Error while listening for wake up; Socket error ($errno): ".socket_strerror($errno),
                                $server
                            );
                            $this->sleepConnection($server);
                        }
                    }
                }

                // if we have any read connections left
                // after the socket_select call, then there
                // is work to do and we need to break
                $idle = empty($read_conns);
            }
        }

        return !$idle;
    }

    /**
     * If we have no open connections, sleep for the retry time. We don't
     * actually want to call sleep() for the whole time as the process will
     * not respond to signals. So, we will loop and sleep for 1s until the
     * retry time has passed.
     *
     * @return void
     */
    protected function deepSleep($monitor, $lastJobTime)
    {
        $retryTime = !empty($this->minCurrentRetryTime) ? $this->minCurrentRetryTime : $this->retryTime;

        $this->status(
            "No open connections. Sleeping for ".$retryTime." seconds"
        );

        $now = time();
        do {
            sleep(1);
            if (call_user_func($monitor, true, $lastJobTime)) {
                break;
            }
        }
        while (microtime(true) - $now < $retryTime);
    }

    /**
     * Asks each server for work and performs any work that is sent
     *
     * @return bool True if any work was done, false if not
     */
    protected function askForWork($monitor = null, $lastJobTime = null)
    {

        $workDone = false;

        /**
         * Shuffle the list so we are not always starting with the same
         * server on every loop through the while loop.
         *
         * shuffle() destroys keys, so we have to loop a shuffle of the
         * keys.
         */

        $servers = array_keys($this->conn);
        shuffle($servers);

        foreach ($servers as $server) {

            $conn = $this->conn[$server];

            $worked = false;

            try {
                $this->status(
                    "Asking $server for work",
                    $server
                );

                $worked = $this->doWork($conn);

                if ($worked) {
                    $workDone = true;
                    if (empty($this->stats[$server])) {
                        $this->stats[$server] = 0;
                    }
                    $this->stats[$server]++;
                }

            } catch (Net_Gearman_Exception $e) {

                $this->status(
                    "Exception caught while doing work: ".$e->getMessage(),
                    $server
                );

                $this->sleepConnection($server);
            }

            if ($monitor && call_user_func($monitor, true, $lastJobTime)) {
                break;
            }
        }

        return $workDone;
    }

    /**
     * Attempts to reconnect to servers which are in a failed state
     *
     * @return bool True if new connections were created, false if not
     */
    protected function retryConnections()
    {
        $newConnections = false;

        if (count($this->retryConn)) {

            $now = time();

            foreach ($this->retryConn as $server => $retryTime) {

                if ($retryTime <= $now) {

                    $this->status(
                        "Attempting to reconnect to $server",
                        $server
                    );

                    /**
                     * If we reconnect to a server, don't sleep
                     */
                    if ($this->connect($server)) {
                        $newConnections = true;
                    }
                }
            }

            // reset the min retry time as needed
            if (empty($this->retryConn)) {
                $this->minCurrentRetryTime = null;
            } else {
                $this->minCurrentRetryTime = min(array_values($this->retryConn)) - time();
            }
        }

        return $newConnections;
    }

    /**
     * Calculates the connection retry timeout
     *
     * @param  int $retry_count The number of times the connection has been retried
     * @return int              The number of seconds to wait before retrying
     */
    protected function retryTime($retry_count)
    {
        return min($this->maxRetryTime, $this->retryTime << ($retry_count - 1));
    }

    /**
     * Listen on the socket for work
     *
     * Sends the 'grab_job' command and then listens for either the 'noop' or
     * the 'no_job' command to come back. If the 'job_assign' comes down the
     * pipe then we run that job.
     *
     * @param resource $socket The socket to work on
     *
     * @return boolean Returns true if work was done, false if not
     * @throws Net_Gearman_Exception
     * @see $conn->send()
     */
    protected function doWork($conn)
    {
        $conn->send('grab_job');

        $resp = array('function' => 'noop');
        while (count($resp) && $resp['function'] == 'noop') {
            $resp = $conn->blockingRead();
        }

        /**
         * The response can be empty during shut down. We don't need to proceed
         * in those cases. But, most of the time, it should not be.
         */
        if (!is_array($resp) || empty($resp)) {
            foreach ($this->conn as $s => $this_conn) {
                if ($conn == $this_conn) {
                    $server = $s;
                    break;
                }
            }

            $this->sleepConnection($server);

            $this->status(
                "No job was returned from the server",
                $server
            );

            return false;
        }

        if (in_array($resp['function'], array('noop', 'no_job'))) {
            return false;
        }

        if ($resp['function'] != 'job_assign') {
            throw new Net_Gearman_Exception('Holy Cow! What are you doing?!');
        }

        $name   = $resp['data']['func'];
        $handle = $resp['data']['handle'];
        $arg    = array();

        if (isset($resp['data']['arg']) &&
            Net_Gearman_Connection::stringLength($resp['data']['arg'])) {
            $arg = json_decode($resp['data']['arg'], true);
            if ($arg === null) {
                $arg = $resp['data']['arg'];
            }
        }

        try {

            if (empty($this->initParams[$name])) {
                $this->initParams[$name] = [];
            }

            $job = Net_Gearman_Job::factory(
                $name, $conn, $handle, $this->initParams[$name]
            );

            $this->start($handle, $name, $arg);
            $res = $job->run($arg);

            if (!is_array($res)) {
                $res = array('result' => $res);
            }

            $job->complete($res);
            $this->complete($handle, $name, $res);
        } catch (Net_Gearman_Job_Exception $e) {
            // If the factory method call fails, we won't have a job.
            if (isset($job) && $job instanceof Net_Gearman_Job_Common) {
                $job->fail();
            }

            $this->fail($handle, $name, $e);
        }

        // Force the job's destructor to run
        $job = null;

        return true;
    }

    /**
     * Attach a callback
     *
     * @param callback $callback A valid PHP callback
     * @param integer  $type     Type of callback
     *
     * @return void
     * @throws Net_Gearman_Exception When an invalid callback is specified.
     * @throws Net_Gearman_Exception When an invalid type is specified.
     */
    public function attachCallback($callback, $type = self::JOB_COMPLETE)
    {
        if (!is_callable($callback)) {
            throw new Net_Gearman_Exception('Invalid callback specified');
        }
        if (!isset($this->callback[$type])) {
            throw new Net_Gearman_Exception('Invalid callback type specified.');
        }
        $this->callback[$type][] = $callback;
    }

    /**
     * Run the job start callbacks
     *
     * @param string $handle The job's Gearman handle
     * @param string $job    The name of the job
     * @param mixed  $args   The job's argument list
     *
     * @return void
     */
    protected function start($handle, $job, $args)
    {
        if (count($this->callback[self::JOB_START]) == 0) {
            return; // No callbacks to run
        }

        foreach ($this->callback[self::JOB_START] as $callback) {
            call_user_func($callback, $handle, $job, $args);
        }
    }

    /**
     * Run the complete callbacks
     *
     * @param string $handle The job's Gearman handle
     * @param string $job    The name of the job
     * @param array  $result The job's returned result
     *
     * @return void
     */
    protected function complete($handle, $job, array $result)
    {
        if (count($this->callback[self::JOB_COMPLETE]) == 0) {
            return; // No callbacks to run
        }

        foreach ($this->callback[self::JOB_COMPLETE] as $callback) {
            call_user_func($callback, $handle, $job, $result);
        }
    }

    /**
     * Run the fail callbacks
     *
     * @param string $handle The job's Gearman handle
     * @param string $job    The name of the job
     * @param object $error  The exception thrown
     *
     * @return void
     */
    protected function fail($handle, $job, Exception $error)
    {
        if (count($this->callback[self::JOB_FAIL]) == 0) {
            return; // No callbacks to run
        }

        foreach ($this->callback[self::JOB_FAIL] as $callback) {
            call_user_func($callback, $handle, $job, $error);
        }
    }

    /**
     * Run the worker status callbacks
     *
     * @param string $message    A message about the worker's status.
     * @param string $server     The server name related to the status
     *
     * @return void
     */
    protected function status($message, $server = null)
    {
        if (count($this->callback[self::WORKER_STATUS]) == 0) {
            return; // No callbacks to run
        }

        if (!empty($server)) {
            $failed_conns = isset($this->failedConn[$server]) ? $this->failedConn[$server] : 0;
            $connected = isset($this->conn[$server]) && $this->conn[$server]->isConnected();
        } else {
            $failed_conns = null;
            $connected = null;
        }

        foreach ($this->callback[self::WORKER_STATUS] as $callback) {
            call_user_func(
                $callback,
                $message,
                $server,
                $connected,
                $failed_conns
            );
        }
    }

    /**
     * Stop working
     *
     * @return void
     */
    public function endWork()
    {
        foreach ($this->conn as $server => $conn) {
            $this->close($server);
        }
    }

    protected function close($server) {
        if (isset($this->conn[$server])) {
            $conn = $this->conn[$server];

            try {
              $conn->send("reset_abilities");
            } catch (Net_Gearman_Exception $e) {

            }
            $conn->close();
            unset($this->conn[$server]);
        }
    }

    /**
     * Destructor
     *
     * @return void
     * @see Net_Gearman_Worker::stop()
     */
    public function __destruct()
    {
        $this->endWork();
    }

    /**
     * Should we stop work?
     *
     * @return boolean
     */
    public function stopWork()
    {
        return false;
    }
}
