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
 * Updates the publication status to the descendants of an information object
 *
 * @package    symfony
 * @subpackage jobs
 */

class arUpdatePublicationStatusJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('objectId', 'publicationStatusId');

  public function runJob($parameters)
  {
    if (null === $resource = QubitInformationObject::getById($parameters['objectId']))
    {
      $this->error($this->i18n->__('Invalid description id: %1', array('%1' => $parameters['objectId'])));

      return false;
    }

    if (null === $publicationStatus = QubitTerm::getById($parameters['publicationStatusId']))
    {
      $this->error($this->i18n->__('Invalid publication status id: %1', array('%1' => $parameters['publicationStatusId'])));

      return false;
    }

    $message = $this->i18n->__('Updating publication status for the descendants of "%1" to "%2".', array('%1' => $resource->title, '%2' => $publicationStatus->name));
    $this->job->addNoteText($message);
    $this->info($message);

    $descriptionsUpdated = 0;
    foreach ($resource->descendants as $descendant)
    {
      $descendant->setPublicationStatus($publicationStatus->id);
      $descendant->save();

      $descriptionsUpdated++;
    }

    $message = $this->i18n->__('%1 descriptions updated.', array('%1' => $descriptionsUpdated));
    $this->job->addNoteText($message);
    $this->info($message);

    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }
}
