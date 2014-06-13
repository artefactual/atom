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
 * A bare bones worker to test Gearman / AtoM jobs
 *
 * @package    symfony
 * @subpackage jobs
 */

class arInheritRightsJob extends arBaseJob
{
  public function run($parameters)
  {
    // This will be an array of required parameter names
    $this->addRequiredParameters(array(
      'information_object_id',
      'overwrite_or_combine',  // values: overwrite, combine
      'all_or_digital_only'  // values: all, digital_only
    ));

    // parent::run() will check parameters and throw an exception if any are missing
    parent::run($parameters);

    $this->info("Got a test job! id: {$this->job->id}\n");

    try
    {
      $ioId = $parameters['information_object_id'];

      if (($io = QubitInformationObject::getById($ioId)) === null)
      {
        $this->error("Invalid information object id: $ioId");
        return false;
      }

      foreach($io->descendants as $descendant)
      {
        // if digital only and descendant isn't a digital object, skip
        if ('digital_only' === $parameters['all_or_digital_only'] && null === $descendant->getDigitalObject())
        {
          echo "skipping descendant {$descendant->getId()}\n";
          continue;
        }

        // delete existing rights if overwriting rights
        if ('overwrite' === $parameters['overwrite_or_combine']) {
          // the object property of the relation($item) is the right
          foreach ($descendant->getRights() as $item) {
            $item->object->delete();
          }
        }

        // lastly, copy all rights from $io to $descendants
        foreach ($io->getRights() as $parentRelation) {
          // duplicate the right

          // duplicate the related granted_rights

          // create a relation record associating the new right to the descendant
        }
      }

      // finally apply rights of $io to all descendant objects

      $this->job->setStatusCompleted();
      $this->job->save();
    }
    catch (Exception $e)
    {
      $this->error('Unhandled exception - ' . $e);
      return false;
    }

    return true;
  }
}
