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
 * Import csv deaccession data.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class csvDeaccessionImportTask extends csvImportBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'deaccession-import';
    protected $briefDescription = 'Import CSV deaccession data';
    protected $detailedDescription = <<<'EOF'
Import CSV deaccession data
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

        if (false === $fh = fopen($arguments['filename'], 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        $databaseManager = new sfDatabaseManager($this->configuration);
        $conn = $databaseManager->getDatabase('propel')->getConnection();

        // Load taxonomies into variables to avoid use of magic numbers
        $termData = QubitFlatfileImport::loadTermsFromTaxonomies([
            QubitTaxonomy::DEACCESSION_SCOPE_ID => 'scopeTypes',     // part or whole
        ]);

        // Define import
        $import = new QubitFlatfileImport([
            // Pass context
            'context' => sfContext::createInstance($this->configuration),

            // How many rows should import until we display an import status update?
            'rowsUntilProgressDisplay' => $options['rows-until-update'],

            // Where to log errors to
            'errorLog' => $options['error-log'],

            // The status array is a place to put data that should be accessible
            // from closure logic using the getStatus method
            'status' => [
                'scopeTypes' => $termData['scopeTypes'],
                'options' => $options,
            ],

            'standardColumns' => [
                'date',
                'description',
                'extent',
                'reason',
            ],

            'columnMap' => [
                'deaccessionNumber' => 'identifier',
            ],

            // These values get stored to the rowStatusVars array
            'variableColumns' => [
                'scope',
                'accessionNumber',
            ],

            // Import logic to load deaccession
            'rowInitLogic' => function (&$self) {
                $accessionIdentifier = $self->rowStatusVars['accessionNumber'];

                // Look up Qubit ID of pre-created accession
                $accessionQueryStatement = $self->sqlQuery(
                    'SELECT id FROM accession WHERE identifier=?',
                    $params = [$accessionIdentifier]
                );

                $result = $accessionQueryStatement->fetch(PDO::FETCH_OBJ);

                if ($result) {
                    $self->object = new QubitDeaccession();
                    $self->object->accessionId = $result->id;
                } else {
                    $error = 'Skipping. Match not found for accession number: '.$accessionIdentifier;
                    echo $self->logError($error);
                }
            },

            'preSaveLogic' => function (&$self) {
                $ignoreDuplicates = ($self->status['options']['ignore-duplicates']) ? true : false;

                $createDeaccession = $ignoreDuplicates;

                // Check database if ignore-duplicates is NOT set
                if (!$ignoreDuplicates) {
                    // Try and find existing Deaccession
                    $deaccessionQueryStatement = $self->sqlQuery(
                        'SELECT deaccession.id FROM deaccession'
                        .' JOIN deaccession_i18n ON deaccession_i18n.id = deaccession.id'
                        .' WHERE deaccession.identifier=?'
                        .' AND deaccession.date=?'
                        .' AND deaccession.scope_id=?'
                        .' AND deaccession_i18n.description=?'
                        .' AND deaccession_i18n.extent=?'
                        .' AND deaccession_i18n.reason=?'
                        .' AND deaccession.source_culture=?',
                        $params = [
                            $self->object->identifier,
                            $self->object->date,
                            $self->object->scopeId,
                            $self->object->description,
                            $self->object->extent,
                            $self->object->reason,
                            $self->object->culture,
                        ]
                    );
                    $deaccessionResult = $deaccessionQueryStatement->fetch(PDO::FETCH_OBJ);

                    if (!$deaccessionResult) {
                        $createDeaccession = true;
                    }
                }

                if (!$createDeaccession) {
                    $self->object = null;
                    $error = 'Skipping duplicate deaccession: '.$self->rowStatusVars['accessionNumber'];
                    echo $self->logError($error);
                }
            },

            // Import logic to save accession
            'saveLogic' => function (&$self) {
                if ('QubitDeaccession' == get_class($self->object) && isset($self->object) && is_object($self->object)) {
                    $self->object->save();
                }
            },
        ]);

        $import->addColumnHandler('scope', function ($self, $data) {
            $this->setObjectPropertyToTermIdLookedUpFromTermNameArray(
                $self,
                'scopeId',
                'scope type',
                $data,
                $self->status['scopeTypes'][$self->columnValue('culture')]
            );
        });

        $import->csv($fh, $skipRows);
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        parent::configure();
        $this->addOptions([
            new sfCommandOption(
                'ignore-duplicates',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Load all records from csv, including duplicates.'
            ),
        ]);
    }
}
