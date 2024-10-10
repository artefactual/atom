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
 * Importer for Physical Object CSV data.
 *
 * @author     David Juhasz <djuhasz@artefactual.com>
 */
class PhysicalObjectCsvImporter
{
    public static $columnMap = [
        'legacyId' => 'legacyId',
        'name' => 'name',
        'type' => 'typeId',
        'location' => 'location',
        'culture' => 'culture',
        'descriptionSlugs' => 'informationObjectIds',
    ];

    protected $context;
    protected $data;
    protected $dbcon;
    protected $errorLogHandle;
    protected $filename;
    protected $matchedExisting;
    protected $offset = 0;
    protected $ormClasses;
    protected $physicalObjectTypeTaxonomy;
    protected $reader;
    protected $rowsImported = 0;
    protected $rowsTotal = 0;
    protected $timers;
    protected $typeIdLookupTable;

    // Default options
    protected $options = [
        'debug' => false,
        'defaultCulture' => 'en',
        'errorLog' => null,
        'header' => null,
        'insertNew' => true,
        'multiValueDelimiter' => '|',
        'onMultiMatch' => 'skip',
        'overwriteWithEmpty' => false,
        'partialMatches' => false,
        'progressFrequency' => 1,
        'quiet' => false,
        'sourceName' => null,
        'updateExisting' => false,
        'updateSearchIndex' => false,
    ];

    //
    // Public methods
    //

    public function __construct(
        ?sfContext $context = null,
        $dbcon = null,
        $options = []
    ) {
        if (null === $context) {
            $context = new sfContext(ProjectConfiguration::getActive());
        }

        $this->setOrmClasses([
            'informationObject' => QubitInformationObject::class,
            'keymap' => QubitKeymap::class,
            'physicalObject' => QubitPhysicalObject::class,
            'relation' => QubitRelation::class,
        ]);

        $this->physicalObjectTypeTaxonomy = new QubitTaxonomy(
            QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID
        );

        $this->context = $context;
        $this->dbcon = $dbcon;

        $this->setOptions($options);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'context':
                return $this->{$name};

                break;

            case 'dbcon':
                return $this->getDbConnection();

                break;

            case 'typeIdLookupTable':
                return $this->getTypeIdLookupTable();

                break;

            default:
                throw new sfException("Unknown or inaccessible property \"{$name}\"");
        }
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'dbcon':
            case 'typeIdLookupTable':
                $this->{$name} = $value;

                break;

            default:
                throw new sfException("Couldn't set unknown property \"{$name}\"");
        }
    }

    public function setOrmClasses(array $classes)
    {
        $this->ormClasses = $classes;
    }

    public function setFilename($filename)
    {
        $this->filename = $this->validateFilename($filename);
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function validateFilename($filename)
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

    public function setOptions(?array $options = null)
    {
        if (empty($options)) {
            return;
        }

        foreach ($options as $name => $val) {
            $this->setOption($name, $val);
        }
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOption(string $name, $value)
    {
        switch ($name) {
            case 'header':
                $this->setHeader($value);

                break;

            case 'offset':
                $this->setOffset($value);

                break;

            case 'progressFrequency':
                $this->setProgressFrequency($value);

                break;

            // boolean options
            case 'debug':
            case 'insertNew':
            case 'overwriteWithEmpty':
            case 'partialMatches':
            case 'quiet':
            case 'updateExisting':
            case 'updateSearchIndex':
                $this->options[$name] = (bool) $value;

                break;

            default:
                $this->options[$name] = $value;
        }
    }

    public function getOption(string $name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }
        if ('sourceName' == $name) {
            return basename($this->filename);
        }

        return null;
    }

    public function setPhysicalObjectTypeTaxonomy(QubitTaxonomy $object)
    {
        $this->physicalObjectTypeTaxonomy = $object;
    }

    public function getPhysicalObjectTypeTaxonomy()
    {
        return $this->physicalObjectTypeTaxonomy;
    }

    public function setOffset(int $value)
    {
        $this->offset = $value;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setHeader(?string $str = null)
    {
        if (null === $str) {
            $this->options['header'] = null;

            return;
        }

        $columnNames = explode(',', trim($str));

        // Trim whitespace
        $columnNames = array_map('trim', $columnNames);

        // Remove empty values
        $columnNames = array_filter($columnNames, function ($val) {
            return !empty($val);
        });

        if (empty($columnNames)) {
            $msg = <<<'EOM'
Invalid header. Please provide a CSV delimited list of column names
e.g. "name,location,type,culture".
EOM;

            throw new sfException($msg);
        }

        // Throw error on unknown column names
        foreach ($columnNames as $name) {
            if (!array_key_exists($name, self::$columnMap)) {
                throw new sfException(sprintf(
                    'Column name "%s" in header is invalid',
                    $name
                ));
            }
        }

        $this->options['header'] = $columnNames;
    }

    public function getHeader()
    {
        if (isset($this->options['header'])) {
            return $this->options['header'];
        }
        if (null !== $this->reader) {
            return $this->reader->getHeader();
        }
    }

    public function countRowsImported()
    {
        return $this->rowsImported;
    }

    public function countRowsTotal()
    {
        return $this->rowsTotal;
    }

    public function doImport($filename = null)
    {
        $timer = $this->startTimer('total');

        if (null !== $filename) {
            $this->setFilename($filename);
        }

        $records = $this->loadCsvData($this->filename);

        foreach ($records as $record) {
            ++$this->offset;

            try {
                $data = $this->processRow($record);
                $this->savePhysicalobjects($data);
            } catch (UnexpectedValueException $e) {
                $this->logError(sprintf(
                    'Warning! skipped row [%u/%u]: %s',
                    $this->offset,
                    $this->rowsTotal,
                    $e->getMessage()
                ));

                continue;
            }

            ++$this->rowsImported;
            $this->log($this->progressUpdate($this->rowsImported, $data));
        }

        $timer->add();
    }

    public function loadCsvData($filename)
    {
        $timer = $this->startTimer('loadCsv');

        $this->validateFileName($filename);

        $this->reader = $this->readCsvFile($filename);
        $stmt = (new \League\Csv\Statement())->offset($this->offset);
        $records = $this->getRecords($stmt);

        $this->rowsTotal = count($records);

        $timer->add();

        return $records;
    }

    public function processRow($data)
    {
        $timer = $this->startTimer('processRow');

        if (0 == strlen($data['name']) && 0 == strlen($data['location'])) {
            throw new UnexpectedValueException('No name or location defined');
        }

        $culture = $this->getRecordCulture($data['culture']);

        foreach (self::$columnMap as $oldkey => $newkey) {
            $prow[$newkey] = $this->processColumn($oldkey, $data[$oldkey], $culture);
        }

        $timer->add();

        return $prow;
    }

    public function getRecordCulture($culture = null)
    {
        $culture = trim($culture);

        if (!empty($culture)) {
            return strtolower($culture);
        }

        if (!empty($this->options['defaultCulture'])) {
            return strtolower($this->options['defaultCulture']);
        }

        if (!empty(sfConfig::get('default_culture'))) {
            return strtolower(sfConfig::get('default_culture'));
        }

        throw new UnexpectedValueException('Couldn\'t determine row culture');
    }

    public function savePhysicalobjects($data)
    {
        $saveTimer = $this->startTimer('save');

        // Setting the propel::defaultCulture is necessary for non-English rows
        // to prevent creating an empty i18n row with culture 'en'
        sfPropel::setDefaultCulture($data['culture']);

        $timer = $this->startTimer('matchExisting');
        $matches = $this->matchExistingRecords($data);
        $timer->add();

        if (null === $matches) {
            $this->insertPhysicalObject($data);

            return;
        }

        foreach ($matches as $item) {
            $timer = $this->startTimer('updateExisting');
            $this->updatePhysicalObject($item, $data);
            $timer->add();
        }

        $saveTimer->add();
    }

    public function progressUpdate($count, $data)
    {
        $timer = $this->startTimer('progress');
        $freq = $this->getOption('progressFrequency');

        if (1 == $freq) {
            if (0 == $this->matchedExisting) {
                $msg = 'Row [%u/%u]: name "%s" imported (%01.2fs)';
            } else {
                $msg = 'Row [%u/%u]: Matched and updated name "%s" (%01.2fs)';
            }

            $output = sprintf(
                $msg,
                $this->offset,
                $this->rowsTotal,
                $data['name'],
                $this->getElapsedTime('total')
            );
        } elseif ($freq > 1 && 0 == $count % $freq) {
            $output = sprintf(
                'Imported %u of %u rows (%01.2fs)...',
                $count,
                $this->rowsTotal,
                $this->getElapsedTime('total')
            );
        }

        $timer->add();

        return $output;
    }

    public function reportTimes()
    {
        if (!$this->getOption('debug')) {
            return sprintf(
                'Total import time: %01.2fs',
                $this->getElapsedTime('total')
            ).PHP_EOL;
        }

        $msg = 'Elapsed times:'.PHP_EOL;

        $times = [
            [
                'Load CSV file:            %01.2fs',
                $this->getElapsedTime('loadCsv'),
            ],
            [
                'Process row:              %01.2fs',
                $this->getElapsedTime('processRow'),
            ],
            [
                'Save data:                %01.2fs',
                $this->getElapsedTime('save'),
            ],
            [
                '  Match existing:         %01.2fs',
                $this->getElapsedTime('matchExisting'),
            ],
            [
                '  Insert new rows:        %01.2fs',
                $this->getElapsedTime('insertNew'),
            ],
            [
                '  Update existing rows:   %01.2fs',
                $this->getElapsedTime('updateExisting'),
            ],
            [
                '    Save physical object: %01.2fs',
                $this->getElapsedTime('physobjSave'),
            ],
            [
                '    Save keymap:          %01.2fs',
                $this->getElapsedTime('keymapSave'),
            ],
            [
                '    Update IO relations:  %01.2fs',
                $this->getElapsedTime('updateInfObjRelations'),
            ],
            [
                'Progress reporting:       %01.2fs',
                $this->getElapsedTime('progress'),
            ],
        ];

        foreach ($times as $val) {
            $msg .= '  '.sprintf($val[0], (float) $val[1]).PHP_EOL;
        }

        $msg .= '---------------------------------'.PHP_EOL;
        $msg .= sprintf(
            'Total import time:          %01.2fs',
            $this->getElapsedTime('total')
        ).PHP_EOL;

        return $msg;
    }

    /**
     * Create keymap entry for object.
     *
     * @param string $sourceName Name of source data
     * @param int    $sourceId   ID from source data
     * @param object $object     Object to create entry for
     * @param mixed  $objectId
     * @param mixed  $csvdata
     */
    public function createKeymapEntry($objectId, $csvdata)
    {
        $timer = $this->startTimer('keymapSave');

        $query = <<<'EOQ'
INSERT INTO keymap (`source_name`, `source_id`, `target_name`, `target_id`)
VALUES (:sourceName, :sourceId, :targetName, :targetId);
EOQ;

        $sth = $this->dbcon->prepare($query);
        $sth->execute([
            ':sourceName' => $this->getOption('sourceName'),
            ':sourceId' => $csvdata['legacyId'],
            ':targetName' => 'physical_object',
            ':targetId' => $objectId,
        ]);

        $timer->add();
    }

    public function matchExistingRecords($data)
    {
        $this->matchedExisting = 0;
        $options = ['culture' => $data['culture']];

        if (!$this->getOption('updateExisting')) {
            return null;
        }

        if ($this->getOption('partialMatches')) {
            $options = $options + ['partialMatch' => 'begin'];
        }

        $matches = $this->ormClasses['physicalObject']::getByName(
            $data['name'],
            $options
        );

        if (0 == count($matches)) {
            return null;
        }
        if (1 == count($matches)) {
            $this->matchedExisting = 1;

            return [$matches->current()];
        }

        return $this->handleMultipleMatches($data['name'], $matches);
    }

    public function handleMultipleMatches($name, $matches)
    {
        $this->matchedExisting = count($matches);

        if ('skip' == $this->getOption('onMultiMatch')) {
            throw new UnexpectedValueException(sprintf(
                'name "%s" matched %u existing records',
                $name,
                $this->matchedExisting
            ));
        }

        if ('first' == $this->getOption('onMultiMatch')) {
            $matches = [$matches->current()];
        }

        return $matches;
    }

    //
    // Protected methods
    //

    protected function insertPhysicalObject($csvdata)
    {
        $timer = $this->startTimer('insertNew');

        if (!$this->getOption('insertNew')) {
            throw new UnexpectedValueException(sprintf(
                'Couldn\'t match name "%s"',
                $csvdata['name']
            ));
        }

        // Create a new db object, if no match is found
        $physobj = new $this->ormClasses['physicalObject']();

        $physobj->name = $csvdata['name'];
        $physobj->typeId = $csvdata['typeId'];
        $physobj->location = $csvdata['location'];
        $physobj->indexOnSave = $this->getOption('updateSearchIndex');
        $physobj->save($this->dbcon);

        $this->createKeymapEntry($physobj->id, $csvdata);

        $physobj->addInfobjRelations($csvdata['informationObjectIds']);

        $timer->add();
    }

    protected function updatePhysicalObject($physobj, $csvdata)
    {
        $updates = [];

        if ($this->shouldUpdateDb($csvdata['typeId'])) {
            $updates['typeId'] = $csvdata['typeId'];
        }

        if ($this->shouldUpdateDb($csvdata['location'])) {
            $updates['location'] = $csvdata['location'];
        }

        // Only do update if $updates array is populated
        if (!empty($updates)) {
            $timer = $this->startTimer('physobjSave');
            $updated = $physobj->quickUpdate($updates, $this->getDbConnection());
            $timer->add();

            if ($updated) {
                $this->createKeymapEntry($physobj->id, $csvdata);
            }
        }

        if ($this->shouldUpdateDb($csvdata['informationObjectIds'])) {
            $this->updateInfoObjRelations($physobj, $csvdata['informationObjectIds']);
        }
    }

    protected function updateInfoObjRelations($physobj, $informationObjectIds)
    {
        $timer->startTimer('updateInfObjRelations');

        // Update the search index of related information objects
        $physobj->indexOnSave = $this->getOption('updateSearchIndex');

        if (isset($updates['informationObjectIds'])) {
            $physobj->updateInfobjRelations($informationObjectIds);
        }

        $timer->add();
    }

    protected function log($msg)
    {
        if (!$this->getOption('quiet')) {
            echo $msg.PHP_EOL;
        }
    }

    protected function logError($msg)
    {
        // Write to error log (but not STDERR) even in quiet mode
        if (!$this->getOption('quiet') || STDERR != $this->getErrorLogHandle()) {
            fwrite($this->getErrorLogHandle(), $msg.PHP_EOL);
        }
    }

    protected function setProgressFrequency(int $freq)
    {
        // Note: $progressFrequency == 0 turns off logging
        $this->options['progressFrequency'] = ($freq > 0) ? $freq : 0;
    }

    protected function getDbConnection()
    {
        if (null === $this->dbcon) {
            $this->dbcon = Propel::getConnection();
        }

        return $this->dbcon;
    }

    protected function getErrorLogHandle()
    {
        if (null === $filename = $this->getOption('errorLog')) {
            return STDERR;
        }

        if (!isset($this->errorLogHandle)) {
            $this->errorLogHandle = fopen($filename, 'w');
        }

        return $this->errorLogHandle;
    }

    protected function readCsvFile($filename)
    {
        $reader = \League\Csv\Reader::createFromPath($filename, 'r');

        if (!isset($this->options['header'])) {
            // Use first row of CSV file as header
            $reader->setHeaderOffset(0);
        }

        return $reader;
    }

    protected function getRecords($stmt)
    {
        if (isset($this->options['header'])) {
            $records = $stmt->process($this->reader, $this->options['header']);
        } else {
            $records = $stmt->process($this->reader);
        }

        return $records;
    }

    protected function processColumn($key, $val, $culture)
    {
        switch ($key) {
            case 'culture':
                $val = $culture;

                break;

            case 'type':
                $val = $this->lookupTypeId($val, $culture);

                break;

            case 'descriptionSlugs':
                $val = $this->processDescriptionSlugs($val);

                break;

            default:
                $val = trim($val);
        }

        // I'm not using !empty() for this conditional because I want to return an
        // empty array when $val = array()
        if ('' !== $val) {
            return $val;
        }
    }

    protected function processDescriptionSlugs(?string $str = null)
    {
        $ids = [];

        if (null === $str) {
            return $ids;
        }

        foreach ($this->processMultiValueColumn($str) as $val) {
            $class = $this->ormClasses['informationObject'];
            $infobj = $class::getBySlug($val);

            if (null === $infobj) {
                $this->logError(
                    sprintf(
                        'Notice on row [%u/%u]: Ignored unknown description slug "%s"',
                        $this->offset,
                        $this->rowsTotal,
                        $val
                    )
                );

                continue;
            }

            $ids[] = $infobj->id;
        }

        return $ids;
    }

    protected function processMultiValueColumn(string $str)
    {
        if ('' === trim($str)) {
            return [];
        }

        $values = explode($this->getOption('multiValueDelimiter'), $str);
        $values = array_map('trim', $values);

        // Remove empty strings from array
        return array_filter($values, function ($val) {
            return null !== $val && '' !== $val;
        });
    }

    protected function lookupTypeId($name, $culture)
    {
        // Allow typeId to be null
        if ('' === trim($name)) {
            return;
        }

        $lookupTable = $this->getTypeIdLookupTable();
        $name = trim(strtolower($name));
        $culture = trim(strtolower($culture));

        if (null === $typeId = $lookupTable[$culture][$name]) {
            $msg = <<<EOL
Couldn't find physical object type "{$name}" for culture "{$culture}"
EOL;

            throw new UnexpectedValueException($msg);
        }

        return $typeId;
    }

    protected function getTypeIdLookupTable()
    {
        if (null === $this->typeIdLookupTable) {
            $this->typeIdLookupTable = $this
                ->getPhysicalObjectTypeTaxonomy()
                ->getTermNameToIdLookupTable($this->getDbConnection());

            if (null === $this->typeIdLookupTable) {
                throw new sfException(
                    'Couldn\'t load Physical object type terms from database'
                );
            }
        }

        return $this->typeIdLookupTable;
    }

    /**
     * Check if $value should update the current db data.
     *
     * @param mixed $value
     */
    protected function shouldUpdateDb($value)
    {
        // If $value is empty, we shouldn't overwrite the existing DB data, *unless*
        // overwriteWithEmpty option is true
        if (empty($value) && !$this->getOption('overwriteWithEmpty')) {
            return false;
        }

        return true;
    }

    protected function startTimer($name)
    {
        if (!isset($this->timers[$name])) {
            $this->timers[$name] = new QubitTimer();
        } else {
            $this->timers[$name]->start();
        }

        return $this->timers[$name];
    }

    protected function getElapsedTime($name)
    {
        if (!isset($this->timers[$name])) {
            return 0;
        }

        return $this->timers[$name]->elapsed();
    }
}
