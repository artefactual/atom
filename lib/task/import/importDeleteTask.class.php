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
 * Delete import data.
 *
 * @author     David Juhasz <djjuhasz@gmail.com>
 */
class importDeleteTask extends arBaseTask
{
    protected $namespace = 'import';
    protected $name = 'delete';
    protected $briefDescription = 'Delete data created by an import';

    protected $detailedDescription = <<<'EOF'
Delete data created by the named import from the AtoM database
EOF;

    protected $count = 0;
    protected $objectIds = [];
    protected $verbose = false;
    protected $timer;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $count = 0;

        parent::execute($arguments, $options);

        if ($options['logfile']) {
            $this->registerFileLogger($options['logfile']);
        }

        if ($options['verbose']) {
            $this->verbose = true;
        }

        $this->getImportObjectIds($arguments['name']);

        $this->confirmDeletion($options['force']);

        $this->timer = new QubitTimer();

        foreach ($this->objectIds as $id) {
            $this->deleteObject($id);

            $this->deleteKeymapRow($arguments['name'], $id);

            ++$count;
        }

        $this->logMsg(
            sprintf(
                'Deleted %u database records created by import "%s"',
                $count,
                $arguments['name']
            )
        );
    }

    public function logMsg($message, $priority = sfLogger::INFO, $timer = true)
    {
        if (sfLogger::DEBUG == $priority) {
            // If this is a debugging message, don't show it unless running in
            // "verbose" mode
            if (!$this->verbose) {
                return;
            }
        }

        // No timing output
        if (!$timer || null == $this->timer) {
            parent::log($message);

            return;
        }

        // Include timing output
        parent::log(
            sprintf(
                '[+%6.2fs] %s ',
                str_pad($this->timer->elapsed(), 7, '0', STR_PAD_LEFT),
                $message
            )
        );
    }

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                true
            ),
            new sfCommandOption(
                'env',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The environment',
                'cli'
            ),
            new sfCommandOption(
                'connection',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The connection name',
                'propel'
            ),
            new sfCommandOption(
                'force',
                'f',
                sfCommandOption::PARAMETER_NONE,
                'Don\'t prompt for confirmation before deleting data'
            ),
            new sfCommandOption(
                'logfile',
                'l',
                sfCommandOption::PARAMETER_OPTIONAL,
                'Log output to the named file'
            ),
            new sfCommandOption(
                'verbose',
                'v',
                sfCommandOption::PARAMETER_NONE,
                'More verbose output to aid in debugging'
            ),
        ]);

        $this->addArguments([
            new sfCommandArgument(
                'name',
                sfCommandArgument::REQUIRED,
                'The import "source" name (keymap.source value)'
            ),
        ]);
    }

    private function registerFileLogger($filename)
    {
        $flogger = new sfFileLogger($this->dispatcher, ['file' => $filename]);

        $this->dispatcher->connect(
            'command.log',
            [$flogger, 'listenToLogEvent']
        );
    }

    private function getImportObjectIds($name)
    {
        // Get import rows from keymap table in reverse order, so objects imported
        // last are deleted first (LIFO) to avoid parent_id constraint violations
        $sql = <<<'EOL'
SELECT target_id FROM keymap WHERE source_name=:name ORDER BY id DESC
EOL;

        $results = QubitPdo::fetchAll(
            $sql,
            [':name' => $name],
            ['fetchMode' => PDO::FETCH_COLUMN]
        );

        if (count($results) < 1) {
            throw new sfException(
                sprintf(
                    'No data for import "%s" found in the keymap table',
                    $name
                )
            );
        }

        $this->count = count($results);
        $this->objectIds = $results;

        $this->logMsg(
            sprintf(
                'Found %s database records created by import "%s"',
                $this->count,
                $name
            )
        );
    }

    /**
     * Allow the user to abort if they aren't ready to proceed with deletion.
     *
     * @param bool $isForced true to force delete without confirmation
     *
     * @return bool true to abort script, false to continue
     */
    private function confirmDeletion($isForced)
    {
        if ($isForced) {
            return;
        }

        $confirmed = $this->askConfirmation(
            [
                "Continuing will delete {$this->count} database records and related data.",
                '',
                'THIS DATA DELETION CAN NOT BE REVERSED!!!',
                '',
                'Creating a database backup before proceeding is HIGHLY recommended!',
                '',
                'Are you sure you want to delete this data? (y/N)',
            ],
            'QUESTION_LARGE',
            false
        );

        if (!$confirmed) {
            $this->logMsg(sprintf('Task aborted!'));

            exit(0);
        }
    }

    private function deleteObject($objectId)
    {
        $obj = QubitObject::getById($objectId);

        if (null == $obj) {
            throw sfException("Error: couldn\\'t get object id: {$objectId}");
        }

        $this->logMsg(
            sprintf('Deleting "%s"', $obj->slug)
        );

        $obj->delete();
    }

    private function deleteKeymapRow($importName, $objectId)
    {
        $sql = <<<'EOL'
DELETE FROM keymap WHERE source_name=:name AND target_id=:id
EOL;

        $count = QubitPdo::modify(
            $sql,
            [':name' => $importName, ':id' => $objectId]
        );

        $this->logMsg(
            sprintf(
                'Deleted keymap row for import "%s" target_id %s',
                $importName,
                $objectId
            ),
            sfLogger::DEBUG
        );
    }
}
