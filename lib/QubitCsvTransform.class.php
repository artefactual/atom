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

class QubitCsvTransform extends QubitFlatfileImport
{
    /**
     * Name of MySQL table to create as a place to temporarily store row data.
     */
    public const WORKING_AREA_TABLE = 'import_rows';

    public $setupLogic;
    public $transformLogic;
    public $rowsPerFile = 1000;
    public $preserveOrder = false;
    public $levelsOfDescription;
    public $sortOrderCallback;
    public $convertWindowsEncoding = false;

    public function __construct($options = [])
    {
        // Call parent class constructor
        parent::__construct($options);

        if (isset($options['setupLogic'])) {
            $this->setupLogic = $options['setupLogic'];
        }

        if (isset($options['transformLogic'])) {
            $this->transformLogic = $options['transformLogic'];
        }

        $this->status['headersWritten'] = false;

        // Set levels of description
        $this->levelsOfDescription = [];

        if (!empty($options['levelsOfDescription'])) {
            // Use abritrary list of levels of description
            foreach ($options['levelsOfDescription'] as $name) {
                $this->levelsOfDescription[] = strtolower($name);
            }
        } else {
            // Load levels of description from database
            $criteria = new Criteria();
            $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
            $criteria->add(QubitTermI18n::CULTURE, 'en');
            $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
            $criteria->addAscendingOrderByColumn('lft');

            foreach (QubitTerm::get($criteria) as $term) {
                $this->levelsOfDescription[] = strtolower($term->name);
            }
        }
    }

    private function __destruct()
    {
        $this->dropMySQLtemp();
    }

    public function writeHeadersOnFirstPass()
    {
        // Execute setup logic, if any
        if (isset($this->setupLogic)) {
            $this->executeClosurePropertyIfSet('setupLogic');
        }

        if (!$this->status['headersWritten']) {
            fputcsv($this->status['outFh'], $this->columnNames);
            $this->status['headersWritten'] = true;
        }
    }

    public function initializeMySQLtemp()
    {
        // Create transformation work area if it doesn't yet exist
        $sql = 'CREATE TABLE IF NOT EXISTS '.self::WORKING_AREA_TABLE.' (
            id INT NOT NULL AUTO_INCREMENT,
            sortorder INT,
            data LONGTEXT,
            PRIMARY KEY (id))';

        QubitPdo::modify($sql);

        // Delete contents of work area
        $sql = 'DELETE FROM '.self::WORKING_AREA_TABLE;

        QubitPdo::prepareAndExecute($sql);
    }

    public static function dropMySQLtemp()
    {
        $sql = 'DROP TABLE '.self::WORKING_AREA_TABLE;

        QubitPdo::modify($sql);
    }

    public function addRowToMySQL($sortorder)
    {
        $row = $this->status['row'];

        // Normalize each row column's values
        if ($this->convertWindowsEncoding) {
            foreach ($row as $index => $value) {
                $row[$index] = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
            }
        }

        // Add serialized row data to the work area
        $sql = 'INSERT INTO '.self::WORKING_AREA_TABLE.' (sortorder, data) VALUES (?, ?)';

        QubitPdo::prepareAndExecute($sql, [$sortorder, serialize($row)]);
    }

    public static function numberedFilePathVariation($filename, $number)
    {
        $parts = pathinfo($filename);

        return sprintf(
            '%s/%s_%04d.%s',
            $parts['dirname'],
            $parts['filename'],
            $number,
            $parts['extension']
        );
    }

    public function writeMySQLRowsToCsvFilePath($filepath)
    {
        $chunk = 0;
        $startFile = $this->numberedFilePathVariation($filepath, $chunk);
        $fhOut = fopen($startFile, 'w');

        if (!$fhOut) {
            throw new sfException('Error writing to '.$startFile.'.');
        }

        echo 'Writing to '.$startFile."...\n";

        fputcsv($fhOut, $this->columnNames); // write headers

        // Cycle through sorted work area and write CSV file
        $sql = 'SELECT data FROM '.self::WORKING_AREA_TABLE.' ORDER BY sortorder';

        $statement = QubitPdo::prepareAndExecute($sql);

        $currentRow = 1;

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            // If starting a new CSV file, write CSV headers first
            if (($currentRow % $this->rowsPerFile) == 0) {
                ++$chunk;
                $chunkFilePath = $this->numberedFilePathVariation($filepath, $chunk);
                $fhOut = fopen($chunkFilePath, 'w');

                echo 'Writing to '.$chunkFilePath."...\n";

                fputcsv($fhOut, $this->columnNames);
            }

            // Write CSV row data
            $data = unserialize($row['data']);

            fputcsv($fhOut, $data);

            ++$currentRow;
        }
    }

    public function levelOfDescriptionToSortorder($level)
    {
        return array_search(strtolower($level), $this->levelsOfDescription);
    }
}
