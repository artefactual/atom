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
 * Base task to process i18n fields.
 */
abstract class i18nTransformBaseTask extends arBaseTask
{
    private static $tables = [
        'information_object_i18n' => [
            'title',
            'alternate_title',
            'edition',
            'extent_and_medium',
            'archival_history',
            'acquisition',
            'scope_and_content',
            'appraisal',
            'accruals',
            'arrangement',
            'access_conditions',
            'reproduction_conditions',
            'physical_characteristics',
            'finding_aids',
            'location_of_originals',
            'location_of_copies',
            'related_units_of_description',
            'institution_responsible_identifier',
            'rules',
            'sources',
            'revision_history',
        ],
        'actor_i18n' => [
            'authorized_form_of_name',
            'dates_of_existence',
            'history',
            'places',
            'legal_status',
            'functions',
            'mandates',
            'internal_structures',
            'general_context',
            'institution_responsible_identifier',
            'rules',
            'sources',
            'revision_history',
        ],
        'note_i18n' => [
            'content',
        ],
        'repository_i18n' => [
            'geocultural_context',
            'collecting_policies',
            'buildings',
            'holdings',
            'finding_aids',
            'opening_times',
            'access_conditions',
            'disabled_access',
            'research_services',
            'reproduction_services',
            'public_facilities',
            'desc_institution_identifier',
            'desc_rules',
            'desc_sources',
            'desc_revision_history',
        ],
        'rights_i18n' => [
            'rights_note',
            'copyright_note',
            'identifier_value',
            'identifier_type',
            'identifier_role',
            'license_terms',
            'license_note',
            'statute_jurisdiction',
            'statute_note',
        ],
    ];

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $rowCount = 0;
        $changedCount = 0;
        $columnsChangedCount = 0;

        $rootIds = implode(', ', [
            QubitInformationObject::ROOT_ID,
            QubitActor::ROOT_ID,
            QubitRepository::ROOT_ID,
        ]);

        foreach (self::$tables as $tableName => $columns) {
            // Fetch all i18n rows
            $query = 'SELECT * FROM '.$tableName.' WHERE id NOT IN ('.$rootIds.')';
            $statement = QubitPdo::prepareAndExecute($query);

            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                // Process row in subclasses
                $columnsChanged = $this->processRow($row, $tableName, $columns);

                // Update total column values changed
                if ($columnsChanged) {
                    ++$changedCount;
                    $columnsChangedCount += $columnsChanged;
                }

                // Report progress
                $message = 'Processed '.$tableName.' row '.$row['id'].' ('.$row['culture'].')';

                if ($columnsChanged) {
                    $message .= ' ('.$columnsChanged.' changes)';
                }

                $this->logSection('i18n', $message);
                ++$rowCount;
            }
        }

        // Report summary of processing
        $message = 'Processed '.$rowCount.' rows.';

        if ($changedCount) {
            $message .= ' Changed '.$changedCount.' rows';
            $message .= ' ('.$columnsChangedCount.' field values changed).';
        }

        $this->logSection('i18n', $message);
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
        ]);
    }

    /**
     * Process i18n rows and update columns.
     *
     * @param stdClass $row       row of i18n data
     * @param string   $tableName name of table
     * @param array    $columns   available columns
     *
     * @return int number of columns changed
     */
    abstract protected function processRow($row, $tableName, $columns);

    /**
     * Update i18n table row with modified values.
     *
     * @param string $table        i18n table name
     * @param int    $id           ID of row in an i18n table
     * @param string $culture      culture code of a row in an i18n table
     * @param array  $columnValues column/value data to process
     */
    protected function updateRow($table, $id, $culture, $columnValues)
    {
        $values = [];

        $query = 'UPDATE '.$table.' SET ';

        foreach ($columnValues as $column => $value) {
            $query .= (count($values)) ? ', ' : '';
            $query .= $column.'=?';

            $values[] = $value;
        }

        $query .= " WHERE id='".$id."' AND culture='".$culture."'";

        if (count($values)) {
            QubitPdo::prepareAndExecute($query, $values);
        }
    }
}
