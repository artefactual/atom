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
 * Normalize physical objects.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class physicalObjectNormalizeTask extends arBaseTask
{
    protected $namespace = 'physicalobject';
    protected $name = 'normalize';
    protected $briefDescription = 'Normalize physical object data';

    protected $detailedDescription = <<<'EOF'
Normalize physical object data
EOF;

    private $toDelete = [];
    private $relationsUpdated = 0;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        // Offer to abort if not using --force or --dry-run options
        if (!$options['force'] && !$options['dry-run']) {
            $confirmation = $this->askConfirmation("Are you sure you'd like to normalize physical object data?");

            if (!$confirmation) {
                $this->log('Aborted.');

                exit;
            }
        }

        // Disable search index
        QubitSearch::disable();

        // Remind user they are in dry run mode
        if ($options['dry-run']) {
            $this->log('*** DRY RUN (no changes will be made to the database) ***');
        }

        // Display initial counts of physical objects and corresponding relations
        $physicalObjectsCountBefore = $this->getPhysicalObjectCount();
        $relationsCountBefore = $this->getPhysicalObjectRelationCount();

        $this->displayPhysicalObjectAndRelationCounts('before', $physicalObjectsCountBefore, $relationsCountBefore);

        // Check for duplicate physical objects and normalize relations
        $this->log('Detecting duplicates and updating relations to duplicates...');

        if ($options['name-only']) {
            $this->log('(Using physical object name only as the basis for duplicate detection.)');

            $this->checkAllPhysicalObjectsByNameOnlyAndNormalizeRelations($options['dry-run']);
        } else {
            $this->log('(Using physical object name, location, and type as the basis for duplicate detection.)');

            $this->checkPhysicalObjectsWithLocationsAndNormalizeRelations($options['dry-run']);
            $this->checkPhysicalObjectsWithoutLocationsAndNormalizeRelations($options['dry-run']);
        }

        $this->log(sprintf(' - %d relations updated', $this->relationsUpdated));

        // Delete duplicate physical objects if not conducting a dry run
        $this->log('Deleting duplicates...');

        foreach ($this->toDelete as $id) {
            // Delete duplicates
            $po = QubitPhysicalObject::getById($id);

            // Show details of each individual physical object being deleted, if in verbose mode
            if ($options['verbose']) {
                $this->log(sprintf(' - %s', $this->describePhysicalObject($po)));
            }

            if (!$options['dry-run']) {
                $po->delete();
            }
        }

        $this->log(sprintf(' - %d duplicates deleted', count($this->toDelete)));

        // Display post-normalization counts of physical objects and corresponding relations
        if (!$options['dry-run']) {
            $physicalObjectsCountAfter = $this->getPhysicalObjectCount();
            $relationsCountAfter = $this->getPhysicalObjectRelationCount();
        } else {
            // Simulate results during dry run
            $physicalObjectsCountAfter = $physicalObjectsCountBefore - count($this->toDelete);
            $relationsCountAfter = $relationsCountBefore;
        }

        $this->displayPhysicalObjectAndRelationCounts('after', $physicalObjectsCountAfter, $relationsCountAfter);

        // Make sure that new counts make sense
        if (
            $relationsCountBefore == $relationsCountAfter
            && (count($this->toDelete) + $physicalObjectsCountAfter) == $physicalObjectsCountBefore
        ) {
            $this->log('Normalization completed successfully.');
        } else {
            $this->log('Eror: final physical object count is unexpected.');
        }

        // Enable search index
        QubitSearch::enable();
    }

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('name-only', 'n', sfCommandOption::PARAMETER_NONE, 'Normalize using physical object name only', null),
            new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, "Verbose (shows details of what's marked for deletion", null),
            new sfCommandOption('force', 'f', sfCommandOption::PARAMETER_NONE, 'Normalize without confirmation', null),
            new sfCommandOption('dry-run', 'd', sfCommandOption::PARAMETER_NONE, 'Dry run (no database changes)', null),
        ]);
    }

    private function getPhysicalObjectCount()
    {
        $sql = 'SELECT count(*) FROM physical_object';

        return QubitPdo::fetchColumn($sql);
    }

    private function getPhysicalObjectRelationCount()
    {
        $sql = 'SELECT count(*) FROM relation WHERE type_id=?';

        return QubitPdo::fetchColumn($sql, [QubitTerm::HAS_PHYSICAL_OBJECT_ID]);
    }

    private function displayPhysicalObjectAndRelationCounts($stage, $physicalObjectsCount, $relationsCount)
    {
        $this->log(sprintf('Data %s clean-up:', $stage));
        $this->log(sprintf(' - %d physical objects', $physicalObjectsCount));
        $this->log(sprintf(' - %d physical object relations', $relationsCount));
    }

    private function sqlForPhysicalObjectsBySourceCulture()
    {
        return 'SELECT p.id, p.type_id, pi.name, pi.location
            FROM physical_object p
            INNER JOIN physical_object_i18n pi
            ON p.id=pi.id AND p.source_culture=pi.culture';
    }

    private function checkAllPhysicalObjectsByNameOnlyAndNormalizeRelations($dryRun)
    {
        $sql = $this->sqlForPhysicalObjectsBySourceCulture();
        $sql .= ' WHERE pi.name IS NOT NULL';

        foreach (QubitPdo::fetchAll($sql) as $physicalObject) {
            if (in_array($physicalObject->id, $this->toDelete)) {
                // Ignore physical objects already marked for deletion
                continue;
            }

            // Find duplicates
            $sql = $this->sqlForPhysicalObjectsBySourceCulture();
            $sql .= ' WHERE pi.name=:name';

            $params = [':name' => $physicalObject->name];

            $this->findAndMarkDuplicates($sql, $params, $physicalObject->id, $dryRun);
        }
    }

    private function checkPhysicalObjectsWithLocationsAndNormalizeRelations($dryRun = false)
    {
        // Get physical objects with location
        $sql = $this->sqlForPhysicalObjectsBySourceCulture();
        $sql .= ' WHERE pi.name IS NOT NULL AND pi.location IS NOT NULL';

        foreach (QubitPdo::fetchAll($sql) as $physicalObject) {
            if (in_array($physicalObject->id, $this->toDelete)) {
                // Ignore physical objects already marked for deletion
                continue;
            }

            // Find duplicates
            $sql = $this->sqlForPhysicalObjectsBySourceCulture();
            $sql .= ' WHERE p.type_id=:type_id AND pi.name=:name AND pi.location=:location';

            $params = [
                ':type_id' => $physicalObject->type_id,
                ':name' => $physicalObject->name,
                ':location' => $physicalObject->location,
            ];

            $this->findAndMarkDuplicates($sql, $params, $physicalObject->id, $dryRun);
        }
    }

    private function checkPhysicalObjectsWithoutLocationsAndNormalizeRelations($dryRun)
    {
        // Get physical objects without locations
        $sql = $this->sqlForPhysicalObjectsBySourceCulture();
        $sql .= ' WHERE pi.name IS NOT NULL AND pi.location IS NULL';

        foreach (QubitPdo::fetchAll($sql) as $physicalObject) {
            if (in_array($physicalObject->id, $this->toDelete)) {
                // Ignore physical objects already marked for deletion
                continue;
            }

            // Find duplicates
            $sql = $this->sqlForPhysicalObjectsBySourceCulture();
            $sql .= ' WHERE pi.name=:name AND pi.location IS NULL';

            $params = [':name' => $physicalObject->name];

            $this->findAndMarkDuplicates($sql, $params, $physicalObject->id, $dryRun);
        }
    }

    private function findAndMarkDuplicates($sql, $params, $physicalObjectId, $dryRun)
    {
        foreach (QubitPdo::fetchAll($sql, $params) as $duplicate) {
            if ($duplicate->id == $physicalObjectId) {
                // Ignore current physical object
                continue;
            }

            if (in_array($duplicate->id, $this->toDelete)) {
                // Ignore physical objects already marked for deletion
                continue;
            }

            // Get relations to physical objects
            $relations = QubitRelation::getRelationsBySubjectId($duplicate->id, ['typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID]);

            foreach ($relations as $relation) {
                if (!$dryRun) {
                    // Update relation to use current physical object
                    $relation->indexOnSave = false;
                    $relation->subjectId = $physicalObjectId;
                    $relation->save();
                }

                ++$this->relationsUpdated;
            }

            // Mark duplicate for deletion
            $this->toDelete[] = $duplicate->id;
        }
    }

    private function describePhysicalObject($physicalObject)
    {
        $po = QubitPhysicalObject::getById($physicalObject->id);

        $description = sprintf("Name: '%s'", $po->getName(['cultureFallback' => true]));

        if (!empty($location = $po->getLocation(['cultureFallback' => true]))) {
            $description .= sprintf(", Location: '%s'", $location);
        }

        $description .= sprintf(", Type: '%s'", $po->getType(['cultureFallback' => true]));

        return $description;
    }
}
