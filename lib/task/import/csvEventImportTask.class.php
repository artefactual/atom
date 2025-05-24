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
 * Import csv event record data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvEventRecordImportTask extends csvImportBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'event-import';
    protected $briefDescription = 'Import csv event record data';
    protected $detailedDescription = <<<'EOF'
Import CSV event record data
EOF;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $this->validateOptions($options);

        $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

        $sourceName = ($options['source-name'])
            ? $options['source-name']
            : false;

        if (false === $fh = fopen($arguments['filename'], 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        $databaseManager = new sfDatabaseManager($this->configuration);
        $conn = $databaseManager->getDatabase('propel')->getConnection();

        // Load taxonomies into variables to avoid use of magic numbers
        $termData = QubitFlatfileImport::loadTermsFromTaxonomies([
            QubitTaxonomy::EVENT_TYPE_ID => 'eventTypes',
        ]);

        $subjectTable = 'actor_i18n';
        $subjectKeyColumn = 'authorized_form_of_name';
        $subjectValueColumn = 'eventActorName';
        $subjectIdColumn = 'id';

        $objectTable = 'keymap';
        $objectKeyColumn = 'source_id';
        $objectValueColumn = 'legacyId';
        $objectIdColumn = 'target_id';

        $relationTypeColumn = 'eventType';

        $import = new QubitFlatfileImport([
            // Pass context
            'context' => sfContext::createInstance($this->configuration),

            'status' => [
                'sourceName' => $sourceName,
                'eventTypes' => $termData['eventTypes'],
                'subjectTable' => $subjectTable,
                'subjectKeyColumn' => $subjectKeyColumn,
                'subjectValueColumn' => $subjectValueColumn,
                'subjectIdColumn' => $subjectIdColumn,
                'objectTable' => $objectTable,
                'objectKeyColumn' => $objectKeyColumn,
                'objectValueColumn' => $objectValueColumn,
                'objectIdColumn' => $objectIdColumn,
                'relationTypeColumn' => $relationTypeColumn,
                'dataCached' => false,
                'subjectKeys' => [],
                'objectKeys' => [],
                'goodSubjects' => 0,
                'badSubjects' => 0,
                'goodObjects' => 0,
                'badObjects' => 0,
            ],

            'errorLog' => $options['error-log'],

            'saveLogic' => function (&$self) {
                if (!$self->status['dataCached']) {
                    // Cache key -> id associations
                    $self->status['subjectKeys'] = getNameIdArrayFromTable(
                        $self,
                        $self->status['subjectTable'],
                        $self->status['subjectKeyColumn'],
                        $self->status['subjectIdColumn']
                    );

                    $whereClause = ($self->status['sourceName'] && 'keymap' == $self->status['objectTable'])
                        ? 'source_name = "'.$self->status['sourceName'].'"'
                        : false;

                    $self->status['objectKeys'] = getNameIdArrayFromTable(
                        $self,
                        $self->status['objectTable'],
                        $self->status['objectKeyColumn'],
                        $self->status['objectIdColumn'],
                        $whereClause
                    );

                    $self->status['dataCached'] = true;
                }

                // Attempt to use pre-cached actor ID
                $subjectKey = trim($self->columnValue($self->status['subjectValueColumn']));
                $subjectId = false;
                if ($subjectKey) {
                    if (isset($self->status['subjectKeys'][$subjectKey])) {
                        $subjectId = $self->status['subjectKeys'][$subjectKey];
                    }
                }

                // If actor ID not found, create
                if (!$subjectId) {
                    // create actor
                    $actor = $self->createOrFetchActor($subjectKey);
                    $subjectId = $actor->id;
                }

                if ($subjectId) {
                    ++$self->status['goodSubjects'];

                    $objectKey = trim($self->columnValue($self->status['objectValueColumn']));
                    $objectId = false;
                    if ($objectKey) {
                        if (isset($self->status['objectKeys'][$objectKey])) {
                            $objectId = $self->status['objectKeys'][$objectKey];
                        }
                    }

                    if ($objectId) {
                        ++$self->status['goodObjects'];

                        $type = $self->columnValue($self->status['relationTypeColumn']);
                        echo 'Relate '.$subjectId.' to '.$objectId.' as '.$type.".\n";

                        $typeId = array_search($type, $self->status['eventTypes'][$self->columnValue('culture')]);

                        if (!$typeId) {
                            echo "Term does not exist... adding.\n";
                            $term = QubitTerm::createTerm(
                                QubitTaxonomy::EVENT_TYPE_ID,
                                $type,
                                $self->columnValue('culture')
                            );
                            $typeId = $term->id;
                            $self->status['eventTypes'][$typeId] = $type;
                        }

                        $event = new QubitEvent();
                        $event->objectId = $objectId;
                        $event->typeId = $typeId;
                        $event->actorId = $subjectId;
                        $event->save();
                    } else {
                        ++$self->status['badObjects'];
                        echo 'ERROR: object '.$objectKey." not found.\n";
                    }
                } else {
                    ++$self->status['badSubjects'];
                    echo 'ERROR: subject '.$subjectKey." not found.\n";
                }
            },

            'completeLogic' => function (&$self) {
                echo "Import complete.\n";
                echo 'Good subjects: '.$self->status['goodSubjects']."\n";
                echo 'Bad subjects:  '.$self->status['badSubjects']."\n";
                echo 'Good objects:  '.$self->status['goodObjects']."\n";
                echo 'Bad objects:   '.$self->status['badObjects']."\n";
            },
        ]);

        $import->csv($fh, $skipRows);
    }

    /**
     * @see csvImportBaseTask
     */
    protected function configure()
    {
        parent::configure();

        $this->addOptions([
            new sfCommandOption('event-types', null, sfCommandOption::PARAMETER_OPTIONAL, 'Event type terms to create, if they do not yet exist, before import.'),
            new sfCommandOption('source-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'Source name to use when inserting keymap entries.'),
        ]);
    }
}

function getNameIdArrayFromTable(&$self, $tableName, $keyColumn, $idColumn, $whereClause = false)
{
    $names = [];

    $query = 'SELECT '.$keyColumn.', '.$idColumn.' FROM '.$tableName;

    $query .= ($whereClause) ? ' WHERE '.$whereClause : '';

    $statement = $self->sqlQuery($query);

    if (!$statement) {
        echo 'DB error';

        exit;
    }

    while ($subject = $statement->fetch(PDO::FETCH_OBJ)) {
        $names[$subject->{$keyColumn}] = $subject->{$idColumn};
    }

    return $names;
}
