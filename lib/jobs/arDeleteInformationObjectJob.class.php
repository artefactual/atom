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
 * A worker to delete archival descriptions asynchronously.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arDeleteInformationObjectJob extends arBaseJob
{
  public function run($parameters)
  {
    $this->addRequiredParameters(array('information_object_id'));

    parent::run($parameters);

    try
    {
      $ioId = $parameters['information_object_id'];
      
      if (($io = QubitInformationObject::getById($ioId)) === null)
      {
        $this->error("Invalid information object id: $ioId");
        return;
      }

      $this->deleteInformationObject($io);

      $this->job->setStatusCompleted();
      $this->job->save();
    }
    catch (Exception $e)
    {
      $this->error('Unhandled exception - ' . $e);
    }
  }

  private function deleteInformationObject($io)
  {
    $parent = $io->parent;

    foreach ($io->descendants->andSelf()->orderBy('rgt') as $item)
    {
      // Delete related digitalObjects
      foreach ($item->digitalObjects as $digitalObject)
      {
        $digitalObject->informationObjectId = null;
        $digitalObject->delete();
      }

      $item->delete();
    }
  }
}