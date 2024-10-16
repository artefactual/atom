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

require_once __DIR__.'/../vendor/composer/autoload.php';

/**
 * Auditer for CSV imports.
 *
 * @author  Mike Cantelon <mike@artefactual.com>
 */
class CsvImportAuditer
{
    protected $sourceName;
    protected $targetName = 'information_object';
    protected $filename;
    protected $ormClasses;
    protected $errorLogHandle;
    protected $rowsAudited = 0;
    protected $rowsTotal = 0;
    protected $missingIds = [];

    // Default options
    protected $options = [
        'quiet' => false,
        'errorLog' => null,
        'progressFrequency' => 1,
        'idColumnName' => 'legacyId',
    ];

    //
    // Public methods
    //

    public function __construct($options = [])
    {
        $this->setOrmClasses([
            'keymap' => QubitKeymap::class,
        ]);

        $this->setOptions(array_merge($this->options, $options));
    }

    public function setOrmClasses(array $classes): void
    {
        $this->ormClasses = $classes;
    }

    public function setSourceName($sourceName): void
    {
        $this->sourceName = $sourceName;
    }

    public function getSourceName(): string
    {
        return $this->sourceName ?? '';
    }

    public function setTargetName($targetName): void
    {
        if (empty($targetName)) {
            throw new ValueError("Target name can't be blank.");
        }

        $this->targetName = $targetName;
    }

    public function getTargetName(): string
    {
        return $this->targetName;
    }

    public function setFilename($filename): void
    {
        $this->filename = $this->validateFilename($filename);

        if (empty($this->getSourceName())) {
            $this->setSourceName(basename($filename));
        }
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function validateFilename($filename): string
    {
        if (empty($filename)) {
            throw new sfException('Please specify a filename for import');
        }

        if (!file_exists($filename)) {
            throw new sfException("Can not find file {$filename}");
        }

        if (!is_readable($filename)) {
            throw new sfException("Can not read {$filename}");
        }

        return $filename;
    }

    public function setOptions(?array $options = null): void
    {
        if (empty($options)) {
            return;
        }

        foreach ($options as $name => $val) {
            $this->setOption($name, $val);
        }
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOption(string $name, $value): void
    {
        $this->options[$name] = $value;
    }

    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    public function countRowsAudited(): int
    {
        return $this->rowsAudited;
    }

    public function countRowsTotal(): int
    {
        return $this->rowsTotal;
    }

    public function doAudit($filename = null): void
    {
        if (null !== $filename) {
            $this->setFilename($filename);
        }

        $records = $this->loadCsvData($this->filename);

        foreach ($records as $record) {
            try {
                $this->processRow($record);
            } catch (UnexpectedValueException $e) {
                $this->logError(sprintf(
                    'Warning! skipped row [%u/%u]: %s',
                    $this->rowsAudited,
                    $this->rowsTotal,
                    $e->getMessage()
                ));

                continue;
            }

            ++$this->rowsAudited;
            $this->log($this->progressUpdate($this->rowsAudited));
        }

        if (!empty($this->missingIds)) {
            $this->log("\nSource IDs not found in keymap data:");

            foreach ($this->missingIds as $sourceId => $rowNumber) {
                $this->log(sprintf('* %d (row %d)', $sourceId, $rowNumber));
            }
        }
    }

    public function loadCsvData($filename): League\Csv\ResultSet
    {
        $this->validateFileName($filename);

        $reader = $this->readCsvFile($filename);
        $stmt = new \League\Csv\Statement();
        $records = $this->getRecords($reader, $stmt);

        $this->rowsTotal = count($records);

        return $records;
    }

    public function processRow($data): void
    {
        // Determine column name to check
        $idColumnName = $this->getOption('idColumnName');

        // Throw error if not ID value is found
        if (empty($data[$idColumnName])) {
            throw new UnexpectedValueException(sprintf('ID column %s not found', $idColumnName));
        }

        // Attempt to fetch keymap entry corresponding to source ID
        $sourceId = trim($data[$idColumnName]);

        if (false === $this->ormClasses['keymap']::getTargetId(
            $this->getSourceName(),
            $sourceId,
            $this->getTargetName()
        )) {
            $this->missingIds[$sourceId] = $this->rowsAudited + 1;
        }
    }

    public function progressUpdate($count): string
    {
        $freq = $this->getOption('progressFrequency');

        if (1 == $freq) {
            $msg = 'Row [%u/%u] audited';

            $output = sprintf(
                $msg,
                $count,
                $this->rowsTotal
            );
        } elseif ($freq > 1 && 0 == $count % $freq) {
            $output = sprintf(
                'Audited %u of %u rows...',
                $count,
                $this->rowsTotal
            );
        }

        return $output;
    }

    public function getMissingIds(): array
    {
        return $this->missingIds;
    }

    //
    // Protected methods
    //

    protected function log($msg): void
    {
        if (!$this->getOption('quiet')) {
            echo $msg.PHP_EOL;
        }
    }

    protected function logError($msg): void
    {
        // Write to error log (but not STDERR)
        if (STDERR != $this->getErrorLogHandle()) {
            fwrite($this->getErrorLogHandle(), $msg.PHP_EOL);
        }
    }

    protected function getErrorLogHandle(): resource
    {
        if (null === $filename = $this->getOption('errorLog')) {
            return STDERR;
        }

        if (!isset($this->errorLogHandle)) {
            $this->errorLogHandle = fopen($filename, 'w');
        }

        return $this->errorLogHandle;
    }

    protected function readCsvFile($filename): object
    {
        $reader = \League\Csv\Reader::createFromPath($filename, 'r');

        if (!isset($this->options['header'])) {
            // Use first row of CSV file as header
            $reader->setHeaderOffset(0);
        }

        return $reader;
    }

    protected function getRecords($reader, $stmt): League\Csv\ResultSet
    {
        if (isset($this->options['header'])) {
            $records = $stmt->process($reader, $this->options['header']);
        } else {
            $records = $stmt->process($reader);
        }

        return $records;
    }
}
