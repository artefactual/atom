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

    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'worker'),
            new sfCommandOption('types', null, sfCommandOption::PARAMETER_REQUIRED, 'Type of jobs to perform (check config/gearman.yml for details)', ''),
            new sfCommandOption('abilities', null, sfCommandOption::PARAMETER_REQUIRED, 'A comma separated string indicating which jobs this worker can do.', ''),
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

        $servers = arGearman::getServers();

        $worker = new Net_Gearman_Worker($servers);

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

        $this->log('Running worker...');
        $this->log('PID '.getmypid());

        $this->activateTerminationHandlers();

        $counter = 0;

        // The worker loop!
        $worker->beginWork(
            // Pass a callback that pings the database every ~30 seconds
            // in order to keep the connection alive. AtoM connects to MySQL in a
            // persistent way that timeouts when running the worker for a long time.
            // Another option would be to catch the ProperException from the worker
            // and restablish the connection when needed. Also, the persistent mode
            // could be disabled for this worker. See issue #4182.
            function () use (&$counter) {
                if (30 == $counter++) {
                    $counter = 0;

                    QubitPdo::prepareAndExecute('SELECT 1');
                }
            }
        );
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

            exit();
        };

        pcntl_signal(SIGINT, $signalHandler);
        pcntl_signal(SIGHUP, $signalHandler);
        pcntl_signal(SIGTERM, $signalHandler);
        pcntl_signal(SIGQUIT, $signalHandler);

        // Define shutdown function
        register_shutdown_function(function () {
            $this->log('Job worker stopped.');
        });
    }
}
