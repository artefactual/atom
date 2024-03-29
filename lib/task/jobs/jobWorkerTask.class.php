<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Gearman worker task.
 */
class jobWorkerTask extends arBaseTask
{
    public const LIMIT_RETURN_STATUS = 111;
    private $maxJobCount = 0;
    private $jobsCompleted = 0;
    private $memoryProfiler;
    private $maxMemUsage = 0;

    public function gearmanWorkerLogger(sfEvent $event)
    {
        $this->log($event['message']);
    }

    /**
     * @see sfTask
     *
     * @param mixed $message
     */
    public function log($message)
    {
        parent::log(date('Y-m-d H:i:s > ').$message);
    }

    public function maxJobCountReached()
    {
        if ($this->getMaxJobCount() > 0 && $this->getJobsCompleted() >= $this->getMaxJobCount()) {
            return true;
        }

        return false;
    }

    public function getMaxJobCount()
    {
        return $this->maxJobCount;
    }

    public function setMaxJobCount(int $maxJobCount)
    {
        if ($maxJobCount > 0) {
            $this->maxJobCount = $maxJobCount;
        }
    }

    public function getJobsCompleted()
    {
        return $this->jobsCompleted;
    }

    public function getMemoryUsageString()
    {
        return sprintf(
            "Worker memory usage (PHP - VmRSS): %skB - %dkB\n",
            $this->getPhpReportedMemoryUsage(),
            $this->getLinuxReportedMemoryUsage()
        );
    }

    /* Get OS reported mem usage from /prod/self/status.
     * Grabs the memory value from the VmRSS entry in the status file.
     * VmRSS is the resident mem set size in kB.
     *
     * Returns the resident memory in kB. If the lookup fails this function will
     * return zero and the caller should deal with that as an error.
     */
    public function getLinuxReportedMemoryUsage()
    {
        try {
            preg_match('/^VmRSS:\s(.*)/m', file_get_contents('/proc/self/status'), $matches);
            $memUsage = (int) trim($matches[1]);
        } catch (Exception $e) {
            return 0;
        }

        return $memUsage;
    }

    public function getPhpReportedMemoryUsage()
    {
        return sprintf('%.0f', memory_get_usage(true) / 1024);
    }

    public function getMaxMemUsage()
    {
        return $this->maxMemUsage;
    }

    public function setMaxMemUsage(int $maxMemUsage)
    {
        if ($maxMemUsage > 0) {
            $this->maxMemUsage = $maxMemUsage;
        }
    }

    public function maxMemUsageReached()
    {
        if ($this->getMaxMemUsage() > 0 && $this->getLinuxReportedMemoryUsage() >= $this->getMaxMemUsage()) {
            return true;
        }

        return false;
    }

    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'worker'),
            new sfCommandOption('types', null, sfCommandOption::PARAMETER_REQUIRED, 'Type of jobs to perform (check config/gearman.yml for details)', ''),
            new sfCommandOption('abilities', null, sfCommandOption::PARAMETER_REQUIRED, 'A comma separated string indicating which jobs this worker can do.', ''),
            new sfCommandOption('max-job-count', null, sfCommandOption::PARAMETER_OPTIONAL, 'Maximum number of jobs this worker will run before shutting down.'),
            new sfCommandOption('max-mem-usage', null, sfCommandOption::PARAMETER_OPTIONAL, 'Memory threshold this worker will consume before triggering the worker to shut down (value in kB).'),
        ]);

        $this->addArguments([
        ]);

        $this->namespace = 'jobs';
        $this->name = 'worker';
        $this->briefDescription = 'Gearman worker daemon';
        $this->detailedDescription = <<<'EOF'
Usage: php symfony [jobs:worker|INFO] [--abilities="myAbility1, myAbility2, ..."][--types="general, sword, ..."]
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], false);
        $context = sfContext::createInstance($configuration);

        // Using the current context, get the event dispatcher and suscribe an event in it
        $context->getEventDispatcher()->connect('gearman.worker.log', [$this, 'gearmanWorkerLogger']);

        // QubitSetting are not available for tasks? See lib/SiteSettingsFilter.class.php
        sfConfig::add(QubitSetting::getSettingsArray());

        // Unset default net_gearman prefix for jobs
        define('NET_GEARMAN_JOB_CLASS_PREFIX', '');

        if (0 < strlen($options['abilities'])) {
            $abilities = array_filter(explode(',', $options['abilities']));
        } else {
            $opts = [];
            if (0 < strlen($options['types'])) {
                $opts['types'] = $options['types'];
            }

            $abilities = arGearman::getAbilities($opts);
        }

        if (isset($options['max-job-count']) && $options['max-job-count'] > 0) {
            $this->setMaxJobCount($options['max-job-count']);

            $this->log(
                sprintf(
                    'Worker will shut down after %u jobs have completed.',
                    $this->getMaxJobCount()
                )
            );
        }

        if (isset($options['max-mem-usage']) && $options['max-mem-usage'] > 0) {
            $this->setMaxMemUsage($options['max-mem-usage']);

            $this->log(
                sprintf(
                    'Worker will shut down if memory consumption exceeds %ukB.',
                    $this->getMaxMemUsage()
                )
            );
        }

        $servers = arGearman::getServers();

        $worker = new Net_Gearman_Worker($servers);

        if (arPhpMemoryProfiler::getMemprofEnabled()) {
            $this->memoryProfiler = new arPhpMemoryProfiler();
        }

        // Register abilities (jobs)
        foreach ($abilities as $ability) {
            if (!class_exists($ability)) {
                $this->log("Ability not defined: {$ability}. Please ensure the job is in the lib/task/job directory or that the plugin is enabled.");

                continue;
            }

            $this->log("New ability: {$ability}");
            $worker->addAbility(QubitJob::getJobPrefix().$ability);
        }

        $worker->attachCallback(
            function ($handle, $job, $e) {
                $this->log('Job failed: '.$e->getMessage());
            },
            Net_Gearman_Worker::JOB_FAIL
        );

        $worker->attachCallback(
            function ($handle, $job, $e) {
                ++$this->jobsCompleted;
                $this->log(sprintf('Jobs completed: %u', $this->getJobsCompleted()));

                if (arPhpMemoryProfiler::getMemprofEnabled()) {
                    $this->log(self::getMemoryUsageString());
                    $this->log(
                        sprintf(
                            'Memprof enabled. Dumping grind file: %s',
                            $this->memoryProfiler->createMemprofGrindFile()
                        )
                    );
                }
            },
            Net_Gearman_Worker::JOB_COMPLETE
        );

        $this->log('Running worker...');
        $this->log('PID '.getmypid());

        if (arPhpMemoryProfiler::getMemprofEnabled()) {
            $this->log(sprintf('Memprof profile: %s', $this->memoryProfiler->getMemprofProfile()));
            $this->log(self::getMemoryUsageString());
        }

        $this->activateTerminationHandlers();

        $counter = 0;

        // The worker loop!
        $worker->beginWork(
            // This callback function will be called once per second when a
            // job is not being processed.
            function ($idle, $lastJob) use (&$counter) {
                if ($this->maxJobCountReached()) {
                    $this->log(
                        sprintf(
                            'Max job count reached: %u jobs completed.',
                            $this->getJobsCompleted()
                        ),
                        sfLogger::INFO
                    );

                    // Notify the worker that beginWork() work loop should exit.
                    return true;
                }

                if ($this->maxMemUsageReached()) {
                    $this->log(
                        sprintf(
                            'Max memory usage reached (%ukB): %ukB in use.',
                            $this->getMaxMemUsage(),
                            $this->getLinuxReportedMemoryUsage()
                        ),
                        sfLogger::INFO
                    );

                    // Notify the worker that beginWork() work loop should exit.
                    return true;
                }

                // Ping the database every ~30 seconds in order to keep the
                // connection alive. AtoM connects to MySQL in a persistent
                // way that timeouts when running the worker for a long time.
                // Another option would be to catch the ProperException from the worker
                // and restablish the connection when needed. Also, the persistent mode
                // could be disabled for this worker. See issue #4182.
                if (30 == $counter++) {
                    $counter = 0;

                    QubitPdo::prepareAndExecute('SELECT 1');
                }
            }
        );

        // Return code '111' when worker shuts down due to reaching max job count.
        if ($this->maxJobCountReached()) {
            $this->log('Worker shutting down - max-job-count reached.');
            $this->shutdownWorker(self::LIMIT_RETURN_STATUS);
        }

        // Return code '111' when worker shuts down due to reaching max mem usage.
        if ($this->maxMemUsageReached()) {
            $this->log('Worker shutting down - max-mem-usage reached.');
            $this->shutdownWorker(self::LIMIT_RETURN_STATUS);
        }

        // Force worker's __destruct() to run so gearman connection is closed nicely.
        unset($worker);
    }

    protected function shutdownWorker(int $exitcode = 0)
    {
        // Force worker's __destruct() to run so gearman connection is closed nicely.
        unset($worker);

        exit($exitcode);
    }

    protected function activateTerminationHandlers()
    {
        pcntl_async_signals(true);

        // Define signal handler function within the object scope to allow it
        // to access the object's methods, etc.
        $signalHandler = function ($signal) {
            $messages = [
                SIGINT => 'Job worker termination requested by user.',
                SIGHUP => 'Job worker hang up requested.',
                SIGTERM => 'Job worker termination requested.',
                SIGQUIT => 'Job worker quit requested.',
            ];

            $this->log($messages[$signal]);

            $this->shutdownWorker();
        };

        pcntl_signal(SIGINT, $signalHandler);
        pcntl_signal(SIGHUP, $signalHandler);
        pcntl_signal(SIGTERM, $signalHandler);
        pcntl_signal(SIGQUIT, $signalHandler);

        // Define shutdown function
        register_shutdown_function(function () {
            $this->log('Job worker stopped.');

            if (arPhpMemoryProfiler::getMemprofEnabled()) {
                $this->log(sprintf('Memprof enabled. Dumping shutdown grind file: %s', $this->memoryProfiler->createMemprofGrindFile()));
            }
        });
    }
}
