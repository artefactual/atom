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
 * Import CSV term data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvTermImportTask extends csvImportBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'term-import';
    protected $briefDescription = 'Import csv term data';
    protected $detailedDescription = <<<'EOF'
Import CSV term data
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

        $this->validateOptions($options);

        $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

        $sourceName = ($options['source-name'])
            ? $options['source-name']
            : basename($arguments['filename']);

        if (false === $fh = fopen($arguments['filename'], 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        $databaseManager = new sfDatabaseManager($this->configuration);
        $conn = $databaseManager->getDatabase('propel')->getConnection();

        $import = new QubitFlatfileImport([
            'status' => [
                'context' => $this->context,
                'task' => $this,
                'cliOptions' => $options,
                'sourceName' => $sourceName,
                'legacyIdToInternalId' => [],
                'termRelations' => [],
            ],

            // How many rows should import until we display an import status update?
            'rowsUntilProgressDisplay' => $options['rows-until-update'],

            // Where to log errors to
            'errorLog' => $options['error-log'],

            'saveLogic' => function (&$self) {
                $legacyId = ($self->columnExists('legacyId')) ? $self->columnValue('legacyId') : false;
                $taxonomyId = $self->columnValue('taxonomyId');
                $culture = $self->columnValue('culture');

                // Set parent ID
                if ($self->columnExists('parentName')) {
                    // Look up parent ID using name, taxonomy ID, and culture
                    $criteria = new Criteria();
                    $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);
                    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                    $criteria->add(QubitTermI18n::CULTURE, $culture);
                    $criteria->add(QubitTermI18n::NAME, $self->columnValue('parentName'));

                    $term = QubitTerm::getOne($criteria);

                    if (null !== $term) {
                        $parentId = $term->id;
                    } else {
                        $parentId = QubitTerm::ROOT_ID;
                    }
                } elseif ($self->columnExists('parentId')) {
                    $parentId = $self->columnValue('parentId');
                }

                // Set culture
                $self->status['context']->getUser()->setCulture($culture);

                // Attempt to load pre-existing term and take note of taxonomy
                $term = null;

                if (!empty($legacyId) && isset($self->status['legacyIdToInternalId'][$legacyId])) {
                    // This row is a translation rather than a new term
                    $term = QubitTerm::getById($self->status['legacyIdToInternalId'][$legacyId]);
                } else {
                    // Check to see if a term with the same name already exists in the DB
                    $criteria = new Criteria();
                    $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);
                    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                    $criteria->add(QubitTermI18n::CULTURE, $culture);
                    $criteria->add(QubitTermI18n::NAME, $self->columnValue('name'));

                    $term = QubitTerm::getOne($criteria);
                }

                // Create term if an existing term hasn't been found
                if (null === $term) {
                    $term = new QubitTerm();
                    $term->taxonomyId = $self->columnValue('taxonomyId');

                    if (!empty($parentId)) {
                        if (
                            $mapEntry = $self->fetchKeymapEntryBySourceAndTargetName(
                                $parentId,
                                $self->getStatus('sourceName'),
                                'term'
                            )
                        ) {
                            // Use parent ID reference in keymap entry
                            $term->parentId = $mapEntry->target_id;
                        } elseif (null !== QubitTerm::getById($parentId)) {
                            // Assume parent ID is the ID of an existing term
                            $term->parentId = $parentId;
                        } else {
                            $message = 'Parent ID not found in keymap data or existing data. Setting to root.';
                            $self->getStatus('task')->log($self->logError($message));
                        }
                    }
                }

                // Set term name and code and save
                $term->setName($self->columnValue('name'), ['culture' => $culture]);

                if ($self->columnExists('code')) {
                    $term->code = $self->columnValue('code');
                }

                $term->save();

                // Store new internal ID in case there are subsequent translations to import
                if (!empty($legacyId) && !isset($self->status['legacyIdToInternalId'][$legacyId])) {
                    $self->status['legacyIdToInternalId'][$legacyId] = $term->id;
                }

                // Add notes, if any
                $noteTypes = [
                    'scopeNote' => QubitTerm::SCOPE_NOTE_ID,
                    'sourceNote' => QubitTerm::SOURCE_NOTE_ID,
                    'displayNote' => QubitTerm::DISPLAY_NOTE_ID,
                ];

                foreach ($noteTypes as $column => $typeId) {
                    if ($self->columnExists('')) {
                        $noteDataText = trim($self->columnValue($column));

                        if (!empty($noteDataText)) {
                            $noteData = explode('|', $noteDataText);

                            foreach ($noteData as $noteText) {
                                $note = new QubitNote();
                                $note->objectId = $term->id;
                                $note->typeId = $typeId;
                                $note->content = $noteText;

                                $note->save();
                            }
                        }
                    }
                }

                // Add other names
                if ($self->columnExists('otherFormsOfName')) {
                    $otherNameDataText = trim($self->columnValue('otherFormsOfName'));

                    if (!empty($otherNameDataText)) {
                        $otherNameData = explode('|', $otherNameDataText);

                        foreach ($otherNameData as $name) {
                            $otherName = new QubitOtherName();
                            $otherName->objectId = $term->id;
                            $otherName->typeId = QubitTerm::ALTERNATIVE_LABEL_ID;
                            $otherName->name = $name;

                            $otherName->save();
                        }
                    }
                }

                // Add related terms
                $self->status['termRelations'][$term->id] = [];

                if ($self->columnExists('relatedTerms')) {
                    $relatedTermsDataText = trim($self->columnValue('relatedTerms'));

                    if (!empty($relatedTermsDataText)) {
                        $relatedTermsData = explode('|', $relatedTermsDataText);

                        foreach ($relatedTermsData as $related) {
                            $relatedData = [
                                'name' => $related,
                                'taxonomyId' => $taxonomyId,
                                'culture' => $culture,
                            ];

                            $self->status['termRelations'][$term->id][] = $relatedData;
                        }
                    }
                }

                // Add keymap entry
                if ($self->columnExists('legacyId')) {
                    $self->createKeymapEntry($self->getStatus('sourceName'), $self->columnValue('legacyId'), $term);
                }
            },

            'completeLogic' => function (&$self) {
                // Add term relations
                foreach ($self->status['termRelations'] as $termId => $relatedTermsData) {
                    foreach ($relatedTermsData as $relatedData) {
                        // Search for term in the DB
                        $criteria = new Criteria();
                        $criteria->add(QubitTerm::TAXONOMY_ID, $relatedData['taxonomyId']);
                        $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                        $criteria->add(QubitTermI18n::CULTURE, $relatedData['culture']);
                        $criteria->add(QubitTermI18n::NAME, $relatedData['name']);

                        if (null !== $relatedTerm = QubitTerm::getOne($criteria)) {
                            // Create relation
                            $relation = new QubitRelation();
                            $relation->objectId = $termId;
                            $relation->subjectId = $relatedTerm->id;
                            $relation->typeId = QubitTerm::TERM_RELATION_ASSOCIATIVE_ID;
                            $relation->save();
                        }
                    }
                }
            },
        ]);

        // Allow search indexing to be enabled via a CLI option
        $import->searchIndexingDisabled = ($options['index']) ? false : true;

        $import->csv($fh, $skipRows);
    }

    /**
     * @see csvImportBaseTask
     */
    protected function configure()
    {
        parent::configure();

        $this->addOptions([
            new sfCommandOption(
                'source-name',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Source name to use when inserting keymap entries.'
            ),
            new sfCommandOption(
                'index',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Index for search during import.'
            ),
        ]);
    }
}
