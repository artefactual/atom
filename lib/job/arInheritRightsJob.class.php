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
 * A job to make an archival description's children inherit its
 * granted rights. Afterward, the job will initiate another job to
 * calculate new ACL rules based on the granted rights.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arInheritRightsJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array(
    'overwrite_or_combine', // Values: overwrite, combine
    'all_or_digital_only',  // Values: all, digital_only
    'objectId'
  );

  public function runJob($parameters)
  {
    $io = QubitInformationObject::getById($parameters['objectId']);

    // Check that object exists and that it is not the root
    if (!isset($io) || !isset($io->parent))
    {
      $this->error($this->i18n->__('Could not find an information object with id: %1', array('%1' => $parameters['objectId'])));

      return false;
    }

    // Info object IDs to recalculate rights based on PREMIS
    $idsToUpdate = array();

    foreach ($io->descendants as $descendant)
    {
      // If digital only and descendant isn't a digital object, skip
      if ('digital_only' === $parameters['all_or_digital_only'] && null === $descendant->getDigitalObject())
      {
        $this->info($this->i18n->__('Skipping descendant %1', array('%1' => $descendant->getId())));

        continue;
      }

      $idsToUpdate[] = $descendant->id;

      // Delete existing rights if overwriting rights
      if ('overwrite' === $parameters['overwrite_or_combine'])
      {
        // The object property of the relation($item) is the right
        foreach ($descendant->getRights() as $item)
        {
          $item->object->delete();
        }
      }

      // Lastly, copy all rights from $io to $descendants
      foreach ($io->getRights() as $parentRelation)
      {
        $rights = $parentRelation->object;

        // Duplicate the right
        $newRights = $rights->copy();

        // Create a relation record associating the new right to the descendant
        $newRelation = new QubitRelation;
        $newRelation->objectId  = $newRights->getId();
        $newRelation->typeId    = QubitTerm::RIGHT_ID;
        $newRelation->subjectId = $descendant->getId();

        $newRelation->save();
      }
    }

    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }
}
