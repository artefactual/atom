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
 * Import csv data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
abstract class csvImportBaseTask extends arBaseTask
{
    /**
     * If updating, delete existing digital object if updating, a path or UI has
     * been specified, and not keeping digial objects.
     *
     * @param QubitFlatfileImport $self a reference to our flat file importer
     */
    public function deleteDigitalObjectIfUpdatingAndNotKeeping($self)
    {
        // If keep-digital-objects is set and --update="match-and-update" is set,
        // skip this logic to delete digital objects.
        if (
            (!empty($self->rowStatusVars['digitalObjectPath']) || !empty($self->rowStatusVars['digitalObjectURI']))
            && !$self->keepDigitalObjects
        ) {
            // Retrieve any digital objects that exist for this information object
            $do = $self->object->getDigitalObject();

            if (null !== $do) {
                $deleteDigitalObject = true;

                if ($self->isUpdating()) {
                    // if   - there is a checksum in the import file
                    //      - the checksum is non-blank
                    //      - the checksum in the csv file matches what is in the database
                    // then - do not re-load the digital object from the import file on UPDATE (leave existing recs as is)
                    // else - reload the digital object in the import file (i.e. delete existing record below)
                    if (
                        !empty($self->rowStatusVars['digitalObjectChecksum'])
                        && $self->rowStatusVars['digitalObjectChecksum'] === $do->getChecksum()
                    ) {
                        // if the checksum matches what is stored with digital object, do not import this digital object.
                        $deleteDigitalObject = false;
                    }
                }

                if ($deleteDigitalObject) {
                    $this->log('Deleting existing digital object.');
                    $do->delete();
                }
            }
        }
    }

    /**
     * If getDigitalObject() is null, and a digital object URI or path
     * is specified, then attempt to import single digital object.
     *
     * If both a URI and path are provided, the former is preferred.
     *
     * @param QubitFlatfileImport $self a reference to our flat file importer
     */
    public function importDigitalObject($self)
    {
        if (null === $self->object->getDigitalObject()) {
            if ($uri = $self->rowStatusVars['digitalObjectURI']) {
                $this->addDigitalObjectFromURI($self, $uri);
            } elseif ($path = $self->rowStatusVars['digitalObjectPath']) {
                $this->addDigitalObjectFromPath($self, $path);
            }
        }
    }

    /**
     * Create new digital object from a URI and link it to a resource.
     *
     * @param QubitFlatfileImport $self A reference to our flat file importer ($self->object refers to
     *                                  the resource we're currently importing)
     * @param string              $uri  Asset URI
     */
    public function addDigitalObjectFromURI($self, $uri)
    {
        $do = new QubitDigitalObject();
        $do->object = $self->object;
        $do->indexOnSave = false;

        if ($self->status['options']['skip-derivatives']) {
            // Don't download remote resource or create derivatives
            $do->createDerivatives = false;
        } else {
            // Try downloading external object up to three times (2 retries)
            $options = ['downloadRetries' => 2];
        }

        // Catch digital object import errors to avoid killing whole import
        try {
            $do->importFromURI($uri, $options);
            $do->save();
        } catch (Exception $e) {
            // Log error
            $this->log($e->getMessage(), sfLogger::ERR);
        }
    }

    /**
     * Create new digital object from a path and link it to a resource.
     *
     * @param QubitFlatfileImport $self a reference to our flat file importer ($self->object refers to
     *                                  the resource we're currently importing)
     * @param string              $path Asset file path
     */
    public function addDigitalObjectFromPath($self, $path)
    {
        if (!is_readable($path)) {
            $this->log("Cannot read digital object path. Skipping creation of digital object ({$path})");

            return;
        }

        $do = new QubitDigitalObject();
        $do->usageId = QubitTerm::MASTER_ID;
        $do->object = $self->object;
        $do->indexOnSave = false;

        // Don't create derivatives (reference, thumb)
        if ($self->status['options']['skip-derivatives']) {
            $do->createDerivatives = false;
        }

        $do->assets[] = new QubitAsset($path);

        try {
            $do->save();
        } catch (Exception $e) {
            $this->log($e->getMessage(), sfLogger::ERR);
        }
    }

    public static function importAlternateFormsOfName($self)
    {
        $typeIds = [
            'parallel' => QubitTerm::PARALLEL_FORM_OF_NAME_ID,
            'standardized' => QubitTerm::STANDARDIZED_FORM_OF_NAME_ID,
            'other' => QubitTerm::OTHER_FORM_OF_NAME_ID,
        ];

        foreach ($typeIds as $typeName => $typeId) {
            $columnName = $typeName.'FormsOfName';

            if (!empty($self->arrayColumns[$columnName])) {
                $aliases = $self->rowStatusVars[$columnName];

                foreach ($aliases as $alias) {
                    // Add other name
                    $otherName = new QubitOtherName();
                    $otherName->objectId = $self->object->id;
                    $otherName->name = $alias;
                    $otherName->typeId = $typeId;
                    $otherName->culture = $self->columnValue('culture');
                    $otherName->save();
                }
            }
        }
    }

    /**
     * Import physical objects.
     *
     * @param mixed $self
     */
    public static function importPhysicalObjects($self)
    {
        // Add physical objects
        if (isset($self->rowStatusVars['physicalObjectName'])
        && $self->rowStatusVars['physicalObjectName']) {
            $names = explode('|', $self->rowStatusVars['physicalObjectName']);
            $locations = explode('|', $self->rowStatusVars['physicalObjectLocation']);
            $types = (isset($self->rowStatusVars['physicalObjectType']))
                ? explode('|', $self->rowStatusVars['physicalObjectType'])
                : [];

            foreach ($names as $index => $name) {
                // If location column populated
                if ($self->rowStatusVars['physicalObjectLocation']) {
                    // If current index applicable
                    if (isset($locations[$index])) {
                        $location = $locations[$index];
                    } else {
                        $location = $locations[0];
                    }
                } else {
                    $location = '';
                }

                // If object type column populated
                if ($self->rowStatusVars['physicalObjectType']) {
                    // If current index applicable
                    if (isset($types[$index])) {
                        $type = $types[$index];
                    } else {
                        $type = $types[0];
                    }
                } else {
                    $type = 'Box';
                }

                $physicalObjectTypeId = self::arraySearchCaseInsensitive($type, $self->status['physicalObjectTypes'][$self->columnValue('culture')]);

                // Create new physical object type if not found
                if (false === $physicalObjectTypeId) {
                    echo "\nTerm {$type} not found in physical object type taxonomy, creating it...\n";

                    $newTerm = QubitTerm::createTerm(QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID, $type, $self->columnValue('culture'));
                    $self->status['physicalObjectTypes'] = self::refreshTaxonomyTerms(QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID);

                    $physicalObjectTypeId = $newTerm->id;
                }

                $container = $self->createOrFetchPhysicalObject($name, $location, $physicalObjectTypeId);

                // Associate container with information object
                $self->createRelation($container->id, $self->object->id, QubitTerm::HAS_PHYSICAL_OBJECT_ID);
            }
        }
    }

    /**
     * Import events.
     *
     * @param mixed $import
     */
    public static function importEvents(&$import)
    {
        $events = [];

        // Event columns grouped by version
        foreach (
            [
                '2.1' => [
                    'actorName' => 'creators',
                    'actorHistory' => 'creatorHistories',
                    'date' => 'creatorDates',
                    'startDate' => 'creatorDatesStart',
                    'endDate' => 'creatorDatesEnd',
                    'description' => 'creatorDateNotes',
                    'type' => '-',
                    'place' => '-',
                ],
                '2.2' => [
                    'actorName' => 'creators',
                    'actorHistory' => 'creatorHistories',
                    'date' => 'creationDates',
                    'startDate' => 'creationDatesStart',
                    'endDate' => 'creationDatesEnd',
                    'description' => 'creationDateNotes',
                    'type' => 'creationDatesType',
                    'place' => '-',
                ],
                '2.3' => [
                    'actorName' => 'eventActors',
                    'actorHistory' => 'eventActorHistories',
                    'date' => 'eventDates',
                    'startDate' => 'eventStartDates',
                    'endDate' => 'eventEndDates',
                    'description' => 'eventDescriptions',
                    'type' => 'eventTypes',
                    'place' => 'eventPlaces',
                ],
            ] as $version => $propertyColumns
        ) {
            // Get event data if one of the columns is populated in the current index
            $index = 0;
            while (
                isset($import->rowStatusVars[$propertyColumns['actorName']][$index])
                || isset($import->rowStatusVars[$propertyColumns['actorHistory']][$index])
                || isset($import->rowStatusVars[$propertyColumns['date']][$index])
                || isset($import->rowStatusVars[$propertyColumns['startDate']][$index])
                || isset($import->rowStatusVars[$propertyColumns['endDate']][$index])
                || isset($import->rowStatusVars[$propertyColumns['description']][$index])
                || isset($import->rowStatusVars[$propertyColumns['type']][$index])
                || isset($import->rowStatusVars[$propertyColumns['place']][$index])
            ) {
                // Two columns are used in 2.1 and 2.2: 'creators' and 'creatorHistories'.
                // To avoid adding duplicate events, if we are checking the 2.1 version
                // and only those columns are populated, the events are not created and
                // those columns will try to be related with the other 2.2 date columns.
                // This could create duplicates in CSV files mixing 2.1 and 2.2 date columns,
                // to avoid that, all values are removed after they are added to event data.
                if (
                    '2.1' == $version
                    && !isset($import->rowStatusVars[$propertyColumns['date']][$index])
                    && !isset($import->rowStatusVars[$propertyColumns['startDate']][$index])
                    && !isset($import->rowStatusVars[$propertyColumns['endDate']][$index])
                    && !isset($import->rowStatusVars[$propertyColumns['description']][$index])
                ) {
                    ++$index;

                    continue;
                }

                $eventData = [];
                foreach ($propertyColumns as $property => $column) {
                    // Ignore 'NULL' values
                    if (
                        isset($import->rowStatusVars[$column][$index])
                        && 'NULL' != $import->rowStatusVars[$column][$index]
                    ) {
                        $eventData[$property] = $import->rowStatusVars[$column][$index];

                        // Remove values to avoid duplicates in CSV files mixing version columns.
                        // Use 'NULL' to let them be set (but ignored) for the next version cicle.
                        $import->rowStatusVars[$column][$index] = 'NULL';
                    }
                }

                if (count($eventData)) {
                    $events[] = $eventData;
                }

                ++$index;
            }
        }

        // Create events
        foreach ($events as $eventData) {
            if (!isset($eventData['type'])) {
                // Creation is the default event type. Cast variable as string to avoid
                // a type mismatch when testing if it's a duplicate event in
                // QubitFlatfileImport::hasDuplicateEvent()
                $eventTypeId = (string) QubitTerm::CREATION_ID;
            } else {
                // Get or add term if event type is set
                $typeTerm = $import->createOrFetchTerm(QubitTaxonomy::EVENT_TYPE_ID, $eventData['type'], $import->columnValue('culture'));
                $eventTypeId = $typeTerm->id;

                unset($eventData['type']);
            }

            // If in update mode, check if the import event data matches an existing
            // event
            if (
                $import->matchAndUpdate
                && null !== $event = self::matchExistingEvent($import->object->id, $eventTypeId, $eventData['actorName'])
            ) {
                $eventData['eventId'] = $event->id;
            }

            // Add row culture to fetch place term in event creation/update
            $eventData['culture'] = $import->columnValue('culture');

            $import->createOrUpdateEvent($eventTypeId, $eventData);
        }
    }

    public static function matchExistingEvent($objectId, $typeId, $actorName)
    {
        // Check for a matching event to update
        $criteria = new Criteria();
        $criteria->add(QubitEvent::TYPE_ID, $typeId);
        $criteria->add(QubitEvent::OBJECT_ID, $objectId);

        // Search for a related event linked to the provided actor name
        if (!isset($actorName)) {
            // If no actor name is provided, check for a related event with no actor
            $criteria->add(QubitEvent::ACTOR_ID, null, Criteria::ISNULL);
        } else {
            if (null !== $actor = QubitActor::getByAuthorizedFormOfName($actorName)) {
                // If we found a matching actor, then check for an event related to
                // that actor
                $criteria->add(QubitEvent::ACTOR_ID, $actor->id);
            } else {
                // The provided actor name doesn't exist, so create a new event
                return;
            }
        }

        // Return the matching event, if one is found
        if (null !== $event = QubitEvent::getOne($criteria)) {
            return $event;
        }
    }

    /**
     * Search array for a value, ignoring case, and return the first
     * corresponding key.
     *
     * @param string $search string to search for
     * @param array  $array  array to search through
     *
     * @return bool|int key for found search item or FALSE if not found
     */
    public static function arraySearchCaseInsensitive($search, $array)
    {
        return array_search(strtolower($search), array_map('strtolower', $array));
    }

    /**
     * Add alternative identifiers to an information object.
     *
     * @param QubitInformationObject $io          information object
     * @param array                  $altIds      array of alternative identfier IDs
     * @param array                  $altIdLabels array of alternative identifier labels
     */
    public static function setAlternativeIdentifiers($io, $altIds, $altIdLabels)
    {
        if (count($altIdLabels) !== count($altIds)) {
            throw new sfException('Number of alternative ids does not match number of alt id labels');
        }

        for ($i = 0; $i < count($altIds); ++$i) {
            $io->addProperty($altIdLabels[$i], $altIds[$i], ['scope' => 'alternativeIdentifiers']);
        }
    }

    /**
     * Reload a taxonomy's terms from the database. We'll need to do this
     * whenever we create new terms on the fly when importing the file,
     * so subsequent rows can use the newly created terms.
     *
     * @param int $taxonomyId ID of taxonomy
     *
     * @return array array containing taxonomy terms
     */
    public static function refreshTaxonomyTerms($taxonomyId)
    {
        $result = QubitFlatfileImport::loadTermsFromTaxonomies([$taxonomyId => 'terms']);

        return $result['terms'];
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
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('rows-until-update', null, sfCommandOption::PARAMETER_OPTIONAL, 'Output total rows imported every n rows.'),
            new sfCommandOption('skip-rows', null, sfCommandOption::PARAMETER_OPTIONAL, 'Skip n rows before importing.'),
            new sfCommandOption('error-log', null, sfCommandOption::PARAMETER_OPTIONAL, 'File to log errors to.'),
        ]);
    }

    /**
     * Validate import-related options, throwing exceptions or warning when
     * appropriate.
     *
     * @param array $options options
     */
    protected function validateOptions($options)
    {
        $numericOptions = ['rows-until-update', 'skip-rows'];

        foreach ($numericOptions as $option) {
            if ($options[$option] && !is_numeric($options[$option])) {
                throw new sfException($option.' must be an integer');
            }
        }

        if ($options['error-log'] && !is_dir(dirname($options['error-log']))) {
            throw new sfException('Path to error log is invalid.');
        }

        if ($this->acceptsOption('source-name') && !$options['source-name']) {
            echo "WARNING: If you're importing multiple CSV files as part of the same import it's advisable to use the source-name CLI option to specify a source name (otherwise the filename will be used as a source name).\n";
        }

        if ($options['limit'] && !$options['update']) {
            throw new sfException('The --limit option requires the --update option to be present.');
        }

        if ($options['keep-digital-objects'] && 'match-and-update' != trim($options['update'])) {
            throw new sfException('The --keep-digital-objects option can only be used when --update=\'match-and-update\' option is present.');
        }

        $this->validateUpdateOptions($options);
    }

    /**
     * Validate --update option values, throw an exception if invalid value specified.
     *
     * @param array $options CLI options passed in during import
     */
    protected function validateUpdateOptions($options)
    {
        if (!$options['update']) {
            return;
        }

        $validParams = ['match-and-update', 'delete-and-replace'];

        if (!in_array(trim($options['update']), $validParams)) {
            $msg = sprintf('Parameter "%s" is not valid for --update option. ', $options['update']);
            $msg .= sprintf('Valid options are: %s', implode(', ', $validParams));

            throw new sfException($msg);
        }
    }

    /**
     * Checks to see if a particular option is supported.
     *
     * @param string $name option name
     *
     * @return bool
     */
    protected function acceptsOption($name)
    {
        foreach ($this->getOptions() as $option) {
            if ($name == $option->getName()) {
                return true;
            }
        }

        return false;
    }
}
