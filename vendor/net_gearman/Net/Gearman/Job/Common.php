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
 * Base job class for all Gearman jobs
 *
 * @category  Net
 * @package   Net_Gearman
 * @author    Joe Stump <joe@joestump.net>
 * @copyright 2007-2008 Digg.com, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   Release: @package_version@
 * @link      http://www.danga.com/gearman/
 * @see       Net_Gearman_Job_Common, Net_Gearman_Worker
 */
abstract class Net_Gearman_Job_Common
{
    /**
     * Gearman job handle
     *
     * @var string $handle
     */
    protected $handle = '';

    /**
     * Connection to Gearman
     *
     * @var resource $conn
     * @see Net_Gearman_Connection
     */
    protected $conn = null;

    /**
     * Parameters for Job instantiation
     * @var array $initParams
     */
    protected $initParams;

    /**
     * Constructor
     *
     * @param resource $conn   Connection to communicate with
     * @param string   $handle Job ID / handle for this job
     * @param array $initParams initialization parameters
     *
     * @return void
     */
    public function __construct(Net_Gearman_Connection $conn, $handle, array $initParams=array())
    {
        $this->conn   = $conn;
        $this->handle = $handle;
        $this->initParams = $initParams;
    }

    /**
     * Run your job here
     *
     * @param array $arg Arguments passed from the client
     *
     * @return void
     * @throws Net_Gearman_Exception
     */
    abstract public function run($arg);

    /**
     * Update Gearman with your job's status
     *
     * @param integer $numerator   The numerator (e.g. 1)
     * @param integer $denominator The denominator  (e.g. 100)
     *
     * @return void
     * @see Net_Gearman_Connection::send()
     */
    public function status($numerator, $denominator)
    {
        $this->conn->send('work_status', array(
            'handle' => $this->handle,
            'numerator' => $numerator,
            'denominator' => $denominator
        ));
    }

    /**
     * Mark your job as complete with its status.
     *
     * Net_Gearman communicates between the client and jobs in JSON. The main
     * benefit of this is that we can send fairly complex data types between
     * different languages. You should always pass an array as the result to
     * this function.
     *
     * NOTE: Your actual worker code should not call this if you are using the GearmanManager
     *
     * @param array $result Result of your job
     *
     * @return void
     * @see Net_Gearman_Connection::send()
     */
    public function complete(array $result)
    {

        $this->conn->send('work_complete', array(
            'handle' => $this->handle,
            'result' => json_encode($result)
        ));
    }

    /**
     * Mark your job as failing
     *
     * If your job fails for some reason (e.g. a query fails) you need to run
     * this function and exit from your run() method. This will tell Gearman
     * (and the client by proxy) that the job has failed.
     *
     * NOTE: Your actual worker code should not call this if you are using the GearmanManager, you should
     * throw a Net_Gearman_Job_Exception instead.
     *
     * @return void
     * @see Net_Gearman_Connection::send()
     */
    public function fail()
    {
        $this->conn->send('work_fail', array(
            'handle' => $this->handle
        ));
    }
}
