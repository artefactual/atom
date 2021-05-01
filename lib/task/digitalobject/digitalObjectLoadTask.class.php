<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Load a csv list of digital objects.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class digitalObjectLoadTask extends arBaseTask
{
    protected static $count = 0;

    private $curObjNum = 0;
    private $totalObjCount = 0;
    private $skippedCount = 0;
    private $disableNestedSetUpdating = false;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $databaseManager = new sfDatabaseManager($this->configuration);
        $options['conn'] = $databaseManager->getDatabase('propel')->getConnection();

        sfConfig::set('app_upload_dir', self::getUploadDir($options));

        if (false === $fh = fopen($arguments['filename'], 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        if (isset($options['limit']) && !is_numeric($options['limit'])) {
            throw new sfException('Limit must be a number');
        }

        if ($options['replace'] && $options['attach-only']) {
            throw new sfException('Cannot use option "--attach-only" with "--replace".');
        }

        if ($options['index']) {
            QubitSearch::enable();
        } else {
            QubitSearch::disable();
        }

        $this->disableNestedSetUpdating = ($options['skip-nested-set-build']) ? true : false;

        $operation = $options['replace'] ? 'Replace' : 'Load';
        $this->logSection('digital-object', sprintf('%s digital objects from %s...', $operation, $arguments['filename']));

        // Get header (first) row
        $header = fgetcsv($fh, 1000);

        if ((!in_array('information_object_id', $header) && !in_array('identifier', $header) && !in_array('slug', $header)) || !in_array('filename', $header)) {
            throw new sfException('Import file must contain an \'information_object_id\', an \'identifier\' or a \'slug\' column, and a \'filename\' column');
        }

        $fileKey = array_search('filename', $header);

        // If information_object_id column is available, use it for id
        if (false !== $idKey = array_search('information_object_id', $header)) {
            $idType = 'id';
        }
        // If no id, then lookup by identifier
        elseif (false !== $idKey = array_search('identifier', $header)) {
            $idType = 'identifier';
        }
        // Lookup by slug
        elseif (false !== $idKey = array_search('slug', $header)) {
            $idType = 'slug';
        }

        // Build hash on information_object.id, with array value if information
        // object has multiple digital objects attached
        while ($item = fgetcsv($fh, 1000)) {
            $id = $item[$idKey];
            $filename = $item[$fileKey];

            if (0 == strlen($id)) {
                $this->log("Row {$totalObjCount}: missing {$idType}");

                continue;
            }

            if (0 == strlen($filename)) {
                $this->log("Row {$totalObjCount}: missing filename");

                continue;
            }

            if (!isset($digitalObjects[$id])) {
                $digitalObjects[$id] = $filename;
            } elseif (!is_array($digitalObjects[$id])) {
                $digitalObjects[$id] = [$digitalObjects[$id], $filename];
            } else {
                $digitalObjects[$id][] = $filename;
            }

            ++$this->totalObjCount;
        }

        $this->curObjNum = 0;

        // Set up prepared query based on identifier type
        $sql = 'SELECT io.id, do.id FROM '.QubitInformationObject::TABLE_NAME.' io ';
        if ('slug' == $idType) {
            $sql .= 'JOIN '.QubitSlug::TABLE_NAME.' slug ON slug.object_id = io.id ';
        }
        $sql .= 'LEFT JOIN '.QubitDigitalObject::TABLE_NAME.' do ON io.id = do.object_id';

        if ('id' == $idType) {
            $sql .= ' WHERE io.id = ?';
        } elseif ('identifier' == $idType) {
            $sql .= ' WHERE io.identifier = ?';
        } else {
            $sql .= ' WHERE slug.slug = ?';
        }

        $ioQuery = QubitPdo::prepare($sql);
        $importedCount = 0;

        // Loop through $digitalObject hash and add digital objects to db
        foreach ($digitalObjects as $key => $item) {
            // Stop importing if we've reached the limit
            if (isset($options['limit']) && ($importedCount >= $options['limit'])) {
                break;
            }

            $ioQuery->execute([$key]);
            $results = $ioQuery->fetch();
            if (!$results) {
                $this->log("Couldn't find information object with {$idType}: {$key}");

                continue;
            }

            if ($options['replace']) {
                $digitalObjectName = !is_array($item) ? $item : end($item);

                if (null !== $results[1]) {
                    if (file_exists($path = self::getPath($digitalObjectName, $options))) {
                        // get digital object and delete it.
                        if (null !== $do = QubitDigitalObject::getById($results[1])) {
                            $do->delete();
                            ++$this->deletedCount;
                        }
                    } else {
                        $this->log(sprintf("Couldn't read file '{$digitalObjectName}'"));
                        ++$this->skippedCount;

                        continue;
                    }
                }
                self::addDigitalObject($results[0], $digitalObjectName, $options);
            }
            // If attach-only is set, the task will attach the new DO via a new
            // information obj regardless of whether there is one vs more in the
            // import CSV.
            elseif (!is_array($item) && !$options['attach-only']) {
                // Skip if this information object already has a digital object attached
                if (null !== $results[1]) {
                    $this->log(sprintf("Information object {$idType}: %s already has a digital object. Skipping.", $key));
                    ++$this->skippedCount;

                    continue;
                }

                if (!file_exists($path = self::getPath($item, $options))) {
                    $this->log(sprintf("Couldn't read file '{$item}'"));
                    ++$this->skippedCount;

                    continue;
                }

                self::addDigitalObject($results[0], $item, $options);
            } else {
                if (!is_array($item)) {
                    if (!file_exists($path = self::getPath($item, $options))) {
                        $this->log(sprintf("Couldn't read file '{$item}'"));
                        ++$this->skippedCount;

                        continue;
                    }

                    self::attachDigitalObject($item, $results[0], $options);
                } else {
                    // If more than one digital object linked to this information object
                    for ($i = 0; $i < count($item); ++$i) {
                        if (!file_exists($path = self::getPath($item[$i], $options))) {
                            $this->log(sprintf("Couldn't read file '{$item[$i]}'"));
                            ++$this->skippedCount;

                            continue;
                        }

                        self::attachDigitalObject($item[$i], $results[0], $options);
                    }
                }
            }

            ++$importedCount;
            Qubit::clearClassCaches();
        }

        $this->logSection('digital-object', 'Successfully Loaded '.self::$count.' digital objects.');

        // Warn user to manually update search index
        if (!$options['index']) {
            $this->logSection('digital-object', 'Please update the search index manually to reflect any changes');
        }
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('filename', sfCommandArgument::REQUIRED, 'The input file (csv format).'),
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('link-source', 's', sfCommandOption::PARAMETER_NONE, 'Link source', null),
            new sfCommandOption('path', 'p', sfCommandOption::PARAMETER_OPTIONAL, 'Path prefix for digital objects', null),
            new sfCommandOption('limit', 'l', sfCommandOption::PARAMETER_OPTIONAL, 'Limit number of digital objects imported to n', null),
            new sfCommandOption('attach-only', 'a', sfCommandOption::PARAMETER_NONE, 'Always attach digital objects to a new child description', null),
            new sfCommandOption('replace', 'r', sfCommandOption::PARAMETER_NONE, 'Delete and replace digital objects', null),
            new sfCommandOption('index', 'i', sfCommandOption::PARAMETER_NONE, 'Update search index (defaults to false)', null),
            new sfCommandOption('skip-nested-set-build', null, sfCommandOption::PARAMETER_NONE, "Don't build the nested set upon import completion.", null),
        ]);

        $this->namespace = 'digitalobject';
        $this->name = 'load';
        $this->briefDescription = 'Load a csv list of digital objects';

        $this->detailedDescription = <<<'EOF'
Load a csv list of digital objects
EOF;
    }

    protected function attachDigitalObject($item, $informationObjectId, $options = [])
    {
        // Create new information objects, to maintain one-to-one
        // relationship with digital objects
        $informationObject = new QubitInformationObject();
        $informationObject->parent = QubitInformationObject::getById($informationObjectId);
        $informationObject->title = basename($item);
        $informationObject->disableNestedSetUpdating = $this->disableNestedSetUpdating;
        $informationObject->save($options['conn']);

        self::addDigitalObject($informationObject->id, $item, $options);
    }

    protected function getPath($path, $options = [])
    {
        if (isset($options['path'])) {
            $path = $options['path'].$path;
        }

        return $path;
    }

    protected function addDigitalObject($objectId, $path, $options = [])
    {
        ++$this->curObjNum;

        $path = self::getPath($path, $options);

        $filename = basename($path);

        if (!file_exists($path)) {
            $this->log("Couldn't read file '{$path}'");

            return;
        }

        $remainingImportCount = $this->totalObjCount - $this->skippedCount - $importedCount;
        $operation = $options['replace'] ? 'Replacing with' : 'Loading';
        $message = sprintf("%s '%s' (%d of %d remaining", $operation, $filename, $this->curObjNum, $remainingImportCount);

        if (isset($options['limit'])) {
            $message .= sprintf(': limited to %d imports', $options['limit']);
        }
        $message .= ')';

        $this->log(sprintf('(%s) %s', strftime('%h %d, %r'), $message));

        // Create digital object
        $do = new QubitDigitalObject();
        $do->objectId = $objectId;

        if ($options['link-source']) {
            if (false === $do->importFromFile($path)) {
                return;
            }
        } else {
            $do->usageId = QubitTerm::MASTER_ID;
            $do->assets[] = new QubitAsset($path);
        }

        $do->save($options['conn']);

        ++self::$count;
    }

    protected function getUploadDir($options = [])
    {
        $uploadDir = 'uploads'; // Default value

        $sql = 'SELECT i18n.value
            FROM setting stg JOIN setting_i18n i18n ON stg.id = i18n.id
            WHERE stg.source_culture = i18n.culture
            AND stg.name = \'upload_dir\';';

        if ($sth = $options['conn']->query($sql)) {
            list($uploadDir) = $sth->fetch();
        }

        return $uploadDir;
    }
}
