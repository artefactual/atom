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
 * Run ad-hoc PHP logic contained in a file.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class runCustomLogicTask extends arBaseTask
{
    protected $namespace = 'tools';
    protected $name = 'run';
    protected $briefDescription = 'Run ad-hoc logic contained in a PHP file';

    protected $detailedDescription = <<<'EOF'
Run ad-hoc logic contained in a PHP file
EOF;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        if (false === $fh = fopen($arguments['filename'], 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        include $arguments['filename'];

        // Optionally log script execution
        if ($options['log']) {
            $custom_logger = new sfFileLogger(new sfEventDispatcher(), ['file' => $options['log_file']]);
            $custom_logger->info($arguments['filename']);
        }
    }

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $logFileDefault = sfConfig::get('sf_log_dir').'/tools_run.log';

        $this->addOptions([
            new sfCommandOption('log', null, sfCommandOption::PARAMETER_NONE, 'Log execution of PHP file'),
            new sfCommandOption('log_file', null, sfCommandOption::PARAMETER_OPTIONAL, 'File to log to', $logFileDefault),
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
        ]);

        $this->addArguments([
            new sfCommandArgument('filename', sfCommandArgument::REQUIRED, 'The custom logic file (containing PHP logic).'),
        ]);

        // TODO: add capability to define ad-hoc arguments
    }
}
