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
 * Data integrity repair tool.
 */
class dataIntegrityRepairTask extends arBaseTask
{
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('filename', sfCommandArgument::OPTIONAL, 'A filepath (ending in .csv) for the generated CSV report file', 'affected-records.csv'),
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('mode', null, sfCommandOption::PARAMETER_OPTIONAL, 'The chosed mode for how to proceed with fix: report(default), fix, delete', 'report'),
        ]);

        $this->namespace = 'tools';
        $this->name = 'data-integrity-repair';
        $this->briefDescription = 'Attempt data integrity repair';
        $this->detailedDescription = <<<'EOF'
Attempt to repair data integrity. It does the following:
- Adds missing object rows for all resources extending QubitObject
- Regenerates slugs to use them in CSV report
- Adds missing parent ids to terms
- Checks descriptions with missing data and provides options for attempting to generate a list, fix them, or delete them
- Re-builds the nested sets

To use the data integrity repair tool:
    php symfony tools:data-integrity-repair file/path/to/report.csv
  Any results will be written to the csv at the supplied file path

The data integrity repair tool has 3 modes. By default it only generate reports, but it can also attempt to fix or delete affected records:
    php symfony tools:data-integrity-repair file/path/to/report.csv --mode=delete
  or
    php symfony tools:data-integrity-repair file/path/to/report.csv --mode=fix
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);
        $this->performDataIntegrityChecks($arguments['filename'], $options);
    }

    private function performDataIntegrityChecks($filename, $options = [])
    {
        QubitSearch::disable();
        $this->logSection('data-integrity-repair', "Adding missing object rows (except for descriptions):\n");
        // List of classes with a related object row
        $classes = [
            'QubitRepository',
            'QubitRightsHolder',
            'QubitUser',
            'QubitDonor',
            'QubitActor',
            'QubitAip',
            'QubitJob',
            'QubitDigitalObject',
            'QubitEvent',
            'QubitFunctionObject',
            'QubitObjectTermRelation',
            'QubitPhysicalObject',
            'QubitPremisObject',
            'QubitRelation',
            'QubitRights',
            'QubitRightsHolder',
            'QubitStaticPage',
            'QubitTaxonomy',
            'QubitTerm',
            'QubitAccession',
            'QubitDeaccession',
        ];

        foreach ($classes as $class) {
            $fixed = 0;

            // Find resources without object row
            $sql = 'SELECT tb.id
                FROM '.$class::TABLE_NAME.' tb
                LEFT JOIN object o ON tb.id=o.id
                WHERE o.id IS NULL;';
            $noObjectIds = QubitPdo::fetchAll(
                $sql, [], ['fetchMode' => PDO::FETCH_COLUMN]
            );

            foreach ($noObjectIds as $id) {
                $this->insertObjectRow($id, $class);
                ++$fixed;
            }

            $this->logSection('data-integrity-repair', sprintf("  - %s: %d\n", $class, $fixed));
        }

        $this->logSection('data-integrity-repair', "Regenerating slugs ...\n");

        $task = new propelGenerateSlugsTask($this->dispatcher, $this->formatter);
        $task->setConfiguration($this->configuration);
        $task->run();

        // Set root term as parent for terms without one
        $sql = 'UPDATE term SET parent_id=110 WHERE parent_id IS NULL AND id<>110;';
        $updated = QubitPdo::modify($sql);
        $this->logSection('data-integrity-repair', sprintf("Updating terms without parent id: %d\n", $updated));

        $this->logSection('data-integrity-repair', "Checking descriptions integrity:\n");

        $sql = 'SELECT COUNT(io.id)
            FROM information_object io
            LEFT JOIN object o ON io.id=o.id
            WHERE io.id<>1
            AND o.id IS NULL;';
        $this->logSection('data-integrity-repair', sprintf("  - Descriptions without object row: %d\n", QubitPdo::fetchColumn($sql)));

        $sql = 'SELECT COUNT(id)
            FROM information_object
            WHERE id<>1
            AND parent_id IS NULL;';
        $this->logSection('data-integrity-repair', sprintf("  - Descriptions without parent id: %d\n", QubitPdo::fetchColumn($sql)));

        $sql = 'SELECT COUNT(io.id)
            FROM information_object io
            LEFT JOIN information_object p ON io.parent_id=p.id
            WHERE io.id<>1
            AND p.id IS NULL;';
        $this->logSection('data-integrity-repair', sprintf("  - Descriptions without parent: %d\n", QubitPdo::fetchColumn($sql)));

        $sql = 'SELECT COUNT(io.id)
            FROM information_object io
            LEFT JOIN status st ON io.id=st.object_id AND st.type_id=158
            WHERE io.id<>1
            AND st.status_id IS NULL;';
        $this->logSection('data-integrity-repair', sprintf("  - Descriptions without publication status: %d\n", QubitPdo::fetchColumn($sql)));

        $sql = 'SELECT io.id, o.id as object_id, io.parent_id, p.id as parent, st.id as status, st.status_id
            FROM information_object io
            LEFT JOIN object o ON io.id=o.id
            LEFT JOIN information_object p ON io.parent_id=p.id
            LEFT JOIN status st ON io.id=st.object_id AND st.type_id=158
            WHERE io.id<>1
            AND (o.id IS NULL OR io.parent_id IS NULL
            OR p.id IS NULL
            OR st.id IS NULL
            OR st.status_id IS NULL);';
        $affectedIos = QubitPdo::fetchAll($sql, [], ['fetchMode' => PDO::FETCH_ASSOC]);
        $this->logSection('data-integrity-repair', sprintf("  - Affected descriptions: %d\n", count($affectedIos)));

        if (0 == count($affectedIos)) {
            $this->logSection('data-integrity-repair', "All descriptions seem to be okay.\n");
        } else {
            $affectedIosAndDescendantIds = [];
            $affectedIosById = [];
            foreach (array_reverse($affectedIos) as $io) {
                $this->populateAffectedIosAndDescendantIds($io['id'], $affectedIosAndDescendantIds);
                $affectedIosById[$io['id']] = $io;
            }
            $this->logSection('data-integrity-repair', sprintf("  - Affected descriptions (including descendants): %d\n", count($affectedIosAndDescendantIds)));

            $this->report($filename, $affectedIosById, $affectedIosAndDescendantIds);

            switch ($options['mode']) {
                case 'fix':
                    $this->fix($affectedIosById);

                    break;

                case 'delete':
                    $this->deleteDescriptions($affectedIosById, $affectedIosAndDescendantIds);

                    break;
            }
        }

        $this->logSection('data-integrity-repair', "Rebuilding nested set ...\n");

        $task = new propelBuildNestedSetTask($this->dispatcher, $this->formatter);
        $task->setConfiguration($this->configuration);
        $task->run();

        $this->logSection('data-integrity-repair', "The ES index has not been updated! Run the search:populate task to do so.\n");
    }

    private function insertObjectRow($id, $class)
    {
        $sql = 'INSERT INTO object
            (id, class_name, created_at, updated_at, serial_number)
            VALUES
            (:id, :class, now(), now(), 0);';
        QubitPdo::modify($sql, [':id' => $id, ':class' => $class]);
    }

    private function populateAffectedIosAndDescendantIds($id, &$affectedIosAndDescendantIds)
    {
        // Skip already added IOs
        if (in_array($id, $affectedIosAndDescendantIds)) {
            return;
        }

        // Find children
        $sql = 'SELECT id FROM information_object WHERE parent_id=:id;';
        $children = QubitPdo::fetchAll($sql, [':id' => $id], ['fetchMode' => PDO::FETCH_COLUMN]);

        // Add descendants first
        foreach ($childrenIds as $childId) {
            $this->populateAffectedIosAndDescendantIds($childId, $affectedIosAndDescendantIds);
        }

        $affectedIosAndDescendantIds[] = $id;
    }

    private function report($filename, $affectedIosById, $affectedIosAndDescendantIds)
    {
        $csvFile = fopen($filename, 'w');
        fputcsv($csvFile, ['id', 'parent_id', 'slug', 'issue(s)']);

        // Reverse IOs to show ancestors first on the report
        foreach (array_reverse($affectedIosAndDescendantIds) as $id) {
            // Get current IO data
            $sql = 'SELECT io.id, io.parent_id, slug
                FROM information_object io
                LEFT JOIN slug ON io.id=slug.object_id
                WHERE io.id=:id;';
            $stmt = QubitPdo::prepareAndExecute($sql, [':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_NUM);

            // Check issues
            $issues = [];
            if (isset($affectedIosById[$id])) {
                if (!isset($affectedIosById[$id]['object_id'])) {
                    $issues[] = 'missing object row';
                }
                if (!isset($affectedIosById[$id]['parent'])) {
                    $issues[] = 'parent does not exist';
                }
                if (!isset($affectedIosById[$id]['parent_id'])) {
                    $issues[] = 'parent not set';
                }
                if (!isset($affectedIosById[$id]['status_id']) || !isset($affectedIosById[$id]['status'])) {
                    $issues[] = 'missing publication status';
                }
            } else {
                $issues[] = 'descendant';
            }

            $result[] = implode(' | ', $issues);
            fputcsv($csvFile, $result);
        }

        fclose($csvFile);
        $this->logSection('data-integrity-repair', sprintf("CSV generated: '%s'.\n", $filename));
    }

    private function fix($affectedIosById)
    {
        $count = 0;
        $this->logSection('data-integrity-repair', "Fixing descriptions ...\n");

        foreach ($affectedIosById as $id => $io) {
            // Fix missing object row
            if (!isset($io['object_id'])) {
                $this->insertObjectRow($id, 'QubitInformationObject');
            }

            // Set root IO as parent
            if (!isset($io['parent']) || !isset($io['parent_id'])) {
                $sql = 'UPDATE information_object SET parent_id=1 WHERE id=:id;';
                QubitPdo::modify($sql, [':id' => $id]);
            }

            // Add publication status row
            if (!isset($io['status'])) {
                $sql = "INSERT INTO status
                    (object_id, type_id, status_id, serial_number)
                    VALUES (:id, '158', '159', '0');";
                QubitPdo::modify($sql, [':id' => $id]);
            }
            // Set publication status to draft
            elseif (!isset($io['status_id'])) {
                $sql = 'UPDATE status SET status_id=159 WHERE type_id=158 AND object_id=:id;';
                QubitPdo::modify($sql, [':id' => $id]);
            }

            ++$count;
            if (0 == $count % 100) {
                $this->logSection('data-integrity-repair', sprintf("%d descriptions fixed ...\n", $count));
            }
        }

        $this->logSection('data-integrity-repair', sprintf("%d descriptions fixed.\n", count($affectedIosById)));
    }

    private function deleteDescriptions($affectedIosById, $affectedIosAndDescendantIds)
    {
        $count = 0;
        $this->logSection('data-integrity-repair', "Deleting descriptions ...\n");

        // Description trees are already flattened and reversed to avoid foreign key issues
        foreach ($affectedIosAndDescendantIds as $id) {
            // Fix object row if needed
            if (isset($affectedIosById[$id]) && !isset($affectedIosById[$id]['object_id'])) {
                $this->insertObjectRow($id, 'QubitInformationObject');
            }

            // Delete IO without updating nested set
            $io = QubitInformationObject::getById($id);
            $io->disableNestedSetUpdating = true;
            $io->delete();

            // Avoid high memory usage
            Qubit::clearClassCaches();

            ++$count;
            if (0 == $count % 100) {
                $this->logSection('data-integrity-repair', sprintf("%d descriptions deleted ...\n", $count));
            }
        }

        $this->logSection('data-integrity-repair', sprintf("%d descriptions deleted.\n", count($affectedIosAndDescendantIds)));
    }
}
