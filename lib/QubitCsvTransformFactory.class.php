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

class QubitCsvTransformFactory
{
    public $cliOptions;
    public $machineName;
    public $addColumns;
    public $renameColumns;
    public $ignoreRows;
    public $ignoreRowCheckLogic;
    public $parentKeyLogic;
    public $rowParentKeyLookupLogic;
    public $setupLogic;
    public $transformLogic;
    public $preserveOrder;
    public $convertWindowsEncoding;

    public function __construct($options = [])
    {
        $allowedProperties = [
            'cliOptions',
            'machineName',
            'addColumns',
            'renameColumns',
            'ignoreRows',
            'ignoreRowCheckLogic',
            'parentKeyLogic',
            'rowParentKeyLookupLogic',
            'setupLogic',
            'transformLogic',
            'preserveOrder',
            'convertWindowsEncoding',
        ];

        QubitFlatfileImport::setPropertiesFromArray(
            $this,
            $options,
            $allowedProperties
        );

        if (!$this->machineName) {
            throw new sfException('The machineName property is required.');
        }
    }

    public function make()
    {
        $tempCsvFile = sys_get_temp_dir().'/'.$this->machineName.'_stage1.csv';

        return new QubitCsvTransform([
            'options' => $this->cliOptions,

            'status' => [
                'cliOptions' => $this->cliOptions,
                'parentKeys' => [],
                'ignoreRows' => $this->ignoreRows,
                'ignoreRowCheckLogic' => $this->ignoreRowCheckLogic,
                'noIdentifierCount' => 0,
                'tempFile' => $tempCsvFile,
                'outFh' => fopen($tempCsvFile, 'w'),
                'parentKeyLogic' => $this->parentKeyLogic,
                'rowParentKeyLookupLogic' => $this->rowParentKeyLookupLogic,
            ],

            'preserveOrder' => $this->preserveOrder,
            'convertWindowsEncoding' => true,

            'setupLogic' => $this->setupLogic,
            'transformLogic' => $this->transformLogic,

            'addColumns' => $this->addColumns,

            'renameColumns' => $this->renameColumns,

            'saveLogic' => function (&$self) {
                $self->writeHeadersOnFirstPass();

                if (isset($self->status['parentKeyLogic'])) {
                    $parentKey = trim($self->status['parentKeyLogic']($self));
                    if ($parentKey) {
                        $self->status['parentKeys'][$parentKey] = $self->columnValue('legacyId');
                    }
                }

                if (isset($self->transformLogic)) {
                    $self->executeClosurePropertyIfSet('transformLogic');
                }

                fputcsv($self->status['outFh'], $self->status['row']);
            },

            'completeLogic' => function (&$self) {
                echo "Step 1 complete.\n";

                $fhIn = fopen($self->status['tempFile'], 'r');

                if (!$fhIn) {
                    throw new sfException('Error reading '.$self->status['tempFile'].'.');
                }

                $stage2 = new QubitCsvTransform([
                    'skipOptionsAndEnvironmentCheck' => true,

                    'status' => [
                        'cliOptions' => $self->status['cliOptions'],
                        'finalOutputFile' => $self->status['finalOutputFile'],
                        'parentKeys' => $self->status['parentKeys'],
                        'badParents' => 0,
                        'ignoreRows' => $self->status['ignoreRows'],
                        'ignoreRowCheckLogic' => $self->status['ignoreRowCheckLogic'],
                        'tempFile' => $self->status['tempFile'],
                        'badLevelOfDescription' => 0,
                        'rowParentKeyLookupLogic' => $self->status['rowParentKeyLookupLogic'],
                        'ignoreBadLod' => $self->status['ignoreBadLod'],
                    ],

                    'preserveOrder' => $self->preserveOrder,
                    'convertWindowsEncoding' => $self->convertWindowsEncoding,

                    'errorLog' => $self->errorLog,

                    'saveLogic' => function (&$self) {
                        // Ignore row if ignore check is present and returns true
                        $ignore = isset($self->status['ignoreRowCheckLogic']) && $self->status['ignoreRowCheckLogic']($self);

                        // Ingore row if already ignoring or if present in list of rows to ignore
                        $ignore = ($ignore) ? true : in_array($self->status['rows'], $self->status['ignoreRows']);

                        if ($ignore) {
                            echo 'Ignoring row '.$self->status['rows']."...\n";

                            return;
                        }

                        if (isset($self->status['rowParentKeyLookupLogic'])) {
                            $keyOfRowParent = trim($self->status['rowParentKeyLookupLogic']($self));

                            // if this row has a parent key and a calculated parent key exists, set
                            // the "parentId" column
                            if ($keyOfRowParent && isset($self->status['parentKeys'][$keyOfRowParent])) {
                                $self->columnValue('parentId', $self->status['parentKeys'][$keyOfRowParent]);
                            } elseif ($keyOfRowParent) {
                                $self->columnValue('parentId', $keyOfRowParent);
                            } else {
                                // ...otherwise if the parent key didn't exist, note that it's bad
                                echo 'Bad parent found: '.$keyOfRowParent.' (row '.($self->getStatus('rows') + 1).")\n";
                                ++$self->status['badParents'];
                            }
                        }

                        $levelOfDescriptionAvailable = is_numeric(array_search('levelOfDescription', $self->columnNames));

                        if ($levelOfDescriptionAvailable) {
                            if (!empty($self->preserveOrder)) {
                                $sortorder = $self->getStatus('rows');
                            } else {
                                $sortorder = $self->levelOfDescriptionToSortorder($self->columnValue('levelOfDescription'));
                            }

                            if (is_numeric($sortorder)) {
                                //  print "Description sort order is ". $sortorder .".\n";
                                $self->addRowToMySQL($sortorder);
                            } elseif (isset($self->status['ignoreBadLod']) && $self->status['ignoreBadLod']) {
                                $sortorder = count($self->levelsOfDescription);
                                //  print "Description sort order is ". $sortorder .".\n";
                                $self->addRowToMySQL($sortorder);
                            } else {
                                ++$self->status['badLevelOfDescription'];
                                echo "Ignoring data with bad level of description: '".$self->columnValue('levelOfDescription')."'.\n";
                            }
                        } else {
                            $self->addRowToMySQL(0);
                        }
                    },

                    'completeLogic' => function (&$self) {
                        $self->writeMySQLRowsToCsvFilePath($self->status['finalOutputFile']);

                        echo "Step 2 complete.\n";
                        echo 'Bad parents found: '.$self->status['badParents'].".\n";
                        echo 'Bad level of description found: '.$self->status['badLevelOfDescription'].".\n";
                    },
                ]);

                $stage2->initializeMySQLtemp();
                $stage2->csv($fhIn);
            },
        ]);
    }
}
