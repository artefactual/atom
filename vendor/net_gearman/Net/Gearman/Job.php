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

// Define this if you want your Jobs to be stored in a different
// path than the default.
if (!defined('NET_GEARMAN_JOB_PATH')) {
    define('NET_GEARMAN_JOB_PATH', 'Net/Gearman/Job');
}

// Define this if you want your Jobs to have a prefix requirement
if (!defined('NET_GEARMAN_JOB_CLASS_PREFIX')) {
    define('NET_GEARMAN_JOB_CLASS_PREFIX', 'Net_Gearman_Job_');
}

/**
 * Job creation class
 *
 * @category  Net
 * @package   Net_Gearman
 * @author    Joe Stump <joe@joestump.net>
 * @author    Brian Moon <brianm@dealnews.com>
 * @copyright 2007-2008 Digg.com, Inc.
 * @link      https://github.com/brianlmoon/net_gearman
 */
abstract class Net_Gearman_Job
{
    /**
     * Create an instance of a job
     *
     * The Net_Geraman_Worker class creates connections to multiple job servers
     * and then fires off jobs using this function. It hands off the connection
     * which made the request for the job so that the job can communicate its
     * status from there on out.
     *
     * @param string $job    Name of job (func in Gearman terms)
     * @param object $conn   Instance of Net_Gearman_Connection
     * @param string $handle Gearman job handle of job
     * @param string $initParams initialisation parameters for job
     *
     * @return object Instance of Net_Gearman_Job_Common child
     * @see Net_Gearman_Job_Common
     * @throws Net_Gearman_Exception
     */
    static public function factory($job, $conn, $handle, $initParams=array())
    {
        if (empty($initParams['path'])) {
            $paths = explode(',', NET_GEARMAN_JOB_PATH);
            $file = null;

            foreach ($paths as $path) {
                $tmpFile = $path . '/' . $job . '.php';

                if (file_exists(realpath($tmpFile))) {
                    $file = $tmpFile;
                    break;
                }
            }
        }
        else {
            $file = $initParams['path'];
        }

        include_once $file;

        if (empty($initParams['class_name'])) {
            $class = NET_GEARMAN_JOB_CLASS_PREFIX . $job;
        }
        else {
            $class = $initParams['class_name'];
        }

	// Strip out any potential prefixes used in uniquely identifying AtoM installs.
	// // e.g. "fdd4764376d2f763-arFindingAidJob" -> "arFindingAidJob"
	// // See issue #7423.
	$prefix = QubitJob::getJobPrefix();
	if (substr($class, 0, strlen($prefix)) === $prefix) {
	   $class = substr($class, strlen($prefix));
	}

        if (!class_exists($class)) {
            throw new Net_Gearman_Job_Exception('Invalid Job class: ' . (empty($class) ? '<empty>' : $class) . ' in ' . (empty($file) ? '<empty>' : $file) );
        }

        $instance = new $class($conn, $handle, $initParams);
        if (!$instance instanceof Net_Gearman_Job_Common) {
            throw new Net_Gearman_Job_Exception('Job is of invalid type: ' . get_class($instance));
        }

        return $instance;
    }
}
