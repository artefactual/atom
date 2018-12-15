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
 * Normalize physical objects
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class physicalObjectNormalizeTask extends arBaseTask
{
  protected $namespace        = 'physicalobject';
  protected $name             = 'normalize';
  protected $briefDescription = 'Normalize physical object data';

  protected $detailedDescription = <<<EOF
Normalize physical object data
EOF;

  private $toDelete = array();
  private $relationsUpdated = 0;

  /**
   * @see sfBaseTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('name-only', 'n', sfCommandOption::PARAMETER_NONE, 'Normalize using physical object name only', null),
      new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, "Verbose (shows details of what's marked for deletion", null),
      new sfCommandOption('force', 'f', sfCommandOption::PARAMETER_NONE, 'Normalize without confirmation', null),
      new sfCommandOption('dry-run', 'd', sfCommandOption::PARAMETER_NONE, 'Dry run (no database changes)', null),
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    // Offer to abort if not using --force or --dry-run options
    if (!$options['force'] && !$options['dry-run'])
    {
      $confirmation = $this->askConfirmation("Are you sure you'd like to normalize physical object data?");

      if (!$confirmation)
      {
        $this->log('Aborted.');
        exit();
      }
    }

    // Disable search index
    QubitSearch::disable();

    // Remind user they are in dry run mode
    if ($options['dry-run'])
    {
      $this->log('*** DRY RUN (no changes will be made to the database) ***');
    }

    // Display initial counts of physical objects and corresponding relations
    $physicalObjectsCountBefore = $this->getPhysicalObjectCount();
    $relationsCountBefore = $this->getPhysicalObjectRelationCount();

    $this->log();
    $this->displayPhysicalObjectAndRelationCounts('before', $physicalObjectsCountBefore, $relationsCountBefore);

    // Check for duplicate physical objects and normalize relations
    $this->log();
    $this->log('Detecting duplicates and updating relations to duplicates...');

    if ($options['name-only'])
    {
      $this->log('(Using physical object name only as the basis for duplicate detection.)');

      $this->checkAllPhysicalObjectsByNameOnlyAndNormalizeRelations($options['dry-run']);
    }
    else
    {
      $this->log('(Using physical object name, location, and type as the basis for duplicate detection.)');

      $this->checkPhysicalObjectsWithLocationsAndNormalizeRelations($options['dry-run']);
      $this->checkPhysicalObjectsWithoutLocationsAndNormalizeRelations($options['dry-run']);
    }

    $this->log(sprintf(' - %d relations updated', $this->relationsUpdated));

    // Delete duplicate physical objects if not conducting a dry run
    $this->log();
    $this->log('Deleting duplicates...');

    foreach ($this->toDelete as $id)
    {
      // Delete duplicates
      $po = QubitPhysicalObject::getById($id);

      // Show details of each individual physical object being deleted, if in verbose mode
      if ($options['verbose'])
      {
        $description = sprintf(" - Name: '%s'", $po->getName(array('cultureFallback' => true)));

        if (!empty($location = $po->getLocation(array('cultureFallback' => true))))
        {
          $description .= sprintf(", Location: '%s'", $location);
        }

        $description .= sprintf(", Type: '%s'", $po->getType(array('cultureFallback' => true)));

        $this->log($description);
      }

      if (!$options['dry-run'])
      {
        $po->delete();
      }
    }

    $this->log(sprintf(' - %d duplicates deleted', count($this->toDelete)));

    // Display post-normalization counts of physical objects and corresponding relations
    if (!$options['dry-run'])
    {
      $physicalObjectsCountAfter = $this->getPhysicalObjectCount();
      $relationsCountAfter = $this->getPhysicalObjectRelationCount();
    }
    else
    {
      // Simulate results during dry run
      $physicalObjectsCountAfter = $physicalObjectsCountBefore - count($this->toDelete);
      $relationsCountAfter = $relationsCountBefore;
    }

    $this->log();
    $this->displayPhysicalObjectAndRelationCounts('after', $physicalObjectsCountAfter, $relationsCountAfter);

    // Make sure that new counts make sense
    $this->log();

    if ($relationsCountBefore == $relationsCountAfter
      && (count($this->toDelete) + $physicalObjectsCountAfter) == $physicalObjectsCountBefore)
    {
      $this->log('Normalization completed successfully.');
    }
    else
    {
      $this->log('Eror: final physical object count is unexpected.');
    }

    // Enable search index
    QubitSearch::enable();
  }

  private function getPhysicalObjectCount()
  {
    $sql = 'SELECT count(*) FROM physical_object';
    return QubitPdo::fetchColumn($sql);
  }

  private function getPhysicalObjectRelationCount()
  {
    $sql = 'SELECT count(*) FROM relation WHERE type_id=?';
    return QubitPdo::fetchColumn($sql, array(QubitTerm::HAS_PHYSICAL_OBJECT_ID));
  }

  private function displayPhysicalObjectAndRelationCounts($stage, $physicalObjectsCount, $relationsCount)
  {
    $this->log(sprintf('Data %s clean-up:', $stage));
    $this->log(sprintf(' - %d physical objects', $physicalObjectsCount));
    $this->log(sprintf(' - %d physical object relations', $relationsCount));
  }

  private function criteriaForPhysicalObjectsBySourceCulture()
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitPhysicalObject::ID, QubitPhysicalObjectI18n::ID);
    $criteria->addJoin(QubitPhysicalObject::SOURCE_CULTURE, QubitPhysicalObjectI18n::CULTURE);

    return $criteria;
  }

  private function checkAllPhysicalObjectsByNameOnlyAndNormalizeRelations($dryRun)
  {
    // Get physical objects without locations 
    $criteria = $this->criteriaForPhysicalObjectsBySourceCulture();
    $criteria->add(QubitPhysicalObjectI18n::NAME, null, Criteria::ISNOTNULL);

    foreach (QubitPhysicalObject::get($criteria) as $physicalObject)
    {
      if (in_array($physicalObject->id, $this->toDelete))
      {
        // Ignore physical objects already marked for deletion
        continue;
      }

      $name = $physicalObject->getName(array('sourceCulture' => true));

      // Find duplicates
      $criteria = $this->criteriaForPhysicalObjectsBySourceCulture();
      $criteria->add(QubitPhysicalObjectI18n::NAME, $name);

      $this->findAndMarkDuplicates($criteria, $physicalObject->id, $dryRun);
    }
  }

  private function checkPhysicalObjectsWithLocationsAndNormalizeRelations($dryRun = false)
  {
    // Get physical objects with location
    $criteria = $this->criteriaForPhysicalObjectsBySourceCulture();
    $criteria->add(QubitPhysicalObjectI18n::NAME, null, Criteria::ISNOTNULL);
    $criteria->add(QubitPhysicalObjectI18n::LOCATION, null, Criteria::ISNOTNULL);

    foreach (QubitPhysicalObject::get($criteria) as $physicalObject)
    {
      if (in_array($physicalObject->id, $this->toDelete))
      {
        // Ignore physical objects already marked for deletion
        continue;
      }

      $name = $physicalObject->getName(array('sourceCulture' => true));
      $location = $physicalObject->getLocation(array('sourceCulture' => true));

      // Find duplicates
      $criteria = $this->criteriaForPhysicalObjectsBySourceCulture();
      $criteria->add(QubitPhysicalObject::TYPE_ID, $physicalObject->typeId);
      $criteria->add(QubitPhysicalObjectI18n::NAME, $name);
      $c1 = $criteria->getNewCriterion(QubitPhysicalObjectI18n::LOCATION, $location);
      $c2 = $criteria->getNewCriterion(QubitPhysicalObjectI18n::LOCATION, null, Criteria::ISNULL);
      $c1->addOr($c2);
      $criteria->add($c1);

      $this->findAndMarkDuplicates($criteria, $physicalObject->id, $dryRun);
    }
  }

  private function checkPhysicalObjectsWithoutLocationsAndNormalizeRelations($dryRun)
  {
    // Get physical objects without locations 
    $criteria = $this->criteriaForPhysicalObjectsBySourceCulture();
    $criteria->add(QubitPhysicalObjectI18n::NAME, null, Criteria::ISNOTNULL);
    $criteria->add(QubitPhysicalObjectI18n::LOCATION, null, Criteria::ISNULL);

    foreach (QubitPhysicalObject::get($criteria) as $physicalObject)
    {
      if (in_array($physicalObject->id, $this->toDelete))
      {
        // Ignore physical objects already marked for deletion
        continue;
      }

      $name = $physicalObject->getName(array('sourceCulture' => true));

      // Find duplicates
      $criteria = $this->criteriaForPhysicalObjectsBySourceCulture();
      $criteria->add(QubitPhysicalObjectI18n::NAME, $name);
      $criteria->add(QubitPhysicalObjectI18n::LOCATION, null, Criteria::ISNULL);

      $this->findAndMarkDuplicates($criteria, $physicalObject->id, $dryRun);
    }
  }

  private function findAndMarkDuplicates($criteria, $physicalObjectId, $dryRun)
  {
    foreach (QubitPhysicalObject::get($criteria) as $duplicate)
    {
      if ($duplicate->id == $physicalObjectId)
      {
        // Ignore current physical object
        continue;
      }

      if (in_array($duplicate->id, $this->toDelete))
      {
        // Ignore physical objects already marked for deletion
        continue;
      }

      // Get relations to physical objects
      $relations = QubitRelation::getRelationsBySubjectId($duplicate->id, array('typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID));

      foreach ($relations as $relation)
      {
        if (!$dryRun) 
        {
          // Update relation to use current physical object
          $relation->indexOnSave = false;
          $relation->subjectId = $physicalObjectId;
          $relation->save();
        }

        $this->relationsUpdated++;
      }

      // Mark duplicate for deletion
      $this->toDelete[] = $duplicate->id;
    }
  }
}
