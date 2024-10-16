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
 * Delete unlinked physical objects.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class physicalObjectDeleteUnlinkedTask extends arBaseTask
{
    protected $namespace = 'physicalobject';
    protected $name = 'delete-unlinked';
    protected $briefDescription = "Delete physical objects that aren't linked to descriptions";

    protected $detailedDescription = <<<'EOF'
Delete physical objects that aren't linked to descriptions
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

        // Offer to abort if not using --force or --dry-run options
        if (!$options['force'] && !$options['dry-run']) {
            $confirmation = $this->askConfirmation("Are you sure you'd like to delete all unlinked physical objects?");

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

        // Display initial count of physical objects
        $physicalObjectsCountBefore = $this->getPhysicalObjectCount();

        $this->displayPhysicalObjectCount('before', $physicalObjectsCountBefore, $relationsCountBefore);

        // Check for unlinked physical objects
        $this->log("\nDetecting unlinked physical objects...");

        $toDelete = $this->checkPhysicalObjects($options['dry-run']);

        $this->log(sprintf(' - %d physical objects marked for deletion', count($toDelete)));

        // Delete unlinked physical objects if not conducting a dry run
        $this->log(null);
        $this->log('Deleting unlinked physical objects...');

        foreach ($toDelete as $id) {
            // Delete duplicates
            $po = QubitPhysicalObject::getById($id);

            // Show details of each individual physical object being deleted, if in verbose mode
            if ($options['verbose']) {
                $description = sprintf(" - Name: '%s'", $po->getName(['cultureFallback' => true]));

                if (!empty($location = $po->getLocation(['cultureFallback' => true]))) {
                    $description .= sprintf(", Location: '%s'", $location);
                }

                $description .= sprintf(", Type: '%s'", $po->getType(['cultureFallback' => true]));

                $this->log($description);
            }

            // Delete, if not conducting a dry run
            if (!$options['dry-run']) {
                $po->delete();
            }
        }

        $this->log(sprintf(' - %d physical objects deleted', count($toDelete)));

        // Display post-deletion count of physical objects
        if (!$options['dry-run']) {
            $physicalObjectsCountAfter = $this->getPhysicalObjectCount();
        } else {
            // Simulate results during dry run
            $physicalObjectsCountAfter = $physicalObjectsCountBefore - count($toDelete);
        }

        $this->log(null);
        $this->displayPhysicalObjectCount('after', $physicalObjectsCountAfter);

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
            new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, "Verbose (shows details of what's marked for deletion", null),
            new sfCommandOption('force', 'f', sfCommandOption::PARAMETER_NONE, 'Delete without confirmation', null),
            new sfCommandOption('dry-run', 'd', sfCommandOption::PARAMETER_NONE, 'Dry run (no database changes)', null),
        ]);
    }

    private function getPhysicalObjectCount()
    {
        $sql = 'SELECT count(*) FROM physical_object';

        return QubitPdo::fetchColumn($sql);
    }

    private function displayPhysicalObjectCount($stage, $physicalObjectsCount)
    {
        $this->log(sprintf('Data %s clean-up:', $stage));
        $this->log(sprintf(' - %d physical objects', $physicalObjectsCount));
    }

    private function checkPhysicalObjects()
    {
        $toDelete = [];

        $sql = 'SELECT id FROM physical_object';

        foreach (QubitPdo::fetchAll($sql) as $physicalObject) {
            // Get relations to physical object
            $relations = QubitRelation::getRelationsBySubjectId($physicalObject->id, ['typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID]);

            $informationObjectFound = false;

            if (count($relations)) {
                foreach ($relations as $relation) {
                    if (null !== QubitInformationObject::getById($relation->objectId)) {
                        $informationObjectFound = true;

                        break;
                    }
                }
            }

            // Mark physical object for deletion
            if (!$informationObjectFound) {
                $toDelete[] = $physicalObject->id;
            }
        }

        return $toDelete;
    }
}
