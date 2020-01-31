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
 * Updates information object documents in the Elasticsearch index
 *
 * @package    symfony
 * @subpackage jobs
 */

class arUpdateEsIoDocumentsJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('ioIds', 'updateIos', 'updateDescendants');

  public function runJob($parameters)
  {
    if (empty($parameters['ioIds']) || (!$parameters['updateIos'] && !$parameters['updateDescendants']))
    {
      $this->error($this->i18n->__('Called arUpdateEsIoDocumentsJob without specifying what needs to be updated.'));

      return false;
    }

    if ($parameters['updateIos'] && $parameters['updateDescendants'])
    {
      $message = $this->i18n->__('Updating %1 description(s) and their descendants.', array('%1' => count($parameters['ioIds'])));
    }
    elseif ($parameters['updateIos'])
    {
      $message = $this->i18n->__('Updating %1 description(s).', array('%1' => count($parameters['ioIds'])));
    }
    else
    {
      $message = $this->i18n->__('Updating descendants of %1 description(s).', array('%1' => count($parameters['ioIds'])));
    }

    $this->job->addNoteText($message);
    $this->info($message);

    $count = 0;
    foreach ($parameters['ioIds'] as $id)
    {
      if (null === $object = QubitInformationObject::getById($id))
      {
        $this->info($this->i18n->__('Invalid archival description id: %1', array('%1' => $id)));

        continue;
      }
      
      // Don't count invalid description ids
      $count++;

      if ($parameters['updateIos'] && $parameters['updateDescendants'])
      {
        arElasticSearchInformationObject::update($object, array('updateDescendants' => true));
        $message = $this->i18n->__('Updated %1 description(s) and their descendants.', array('%1' => $count));
      }
      elseif ($parameters['updateIos'])
      {
        arElasticSearchInformationObject::update($object);
        $message = $this->i18n->__('Updated %1 description(s).', array('%1' => $count));
      }
      else
      {
        arElasticSearchInformationObject::updateDescendants($object);
        $message = $this->i18n->__('Updating descendant of %1 description(s).', array('%1' => $count));
      }

      // Minimize memory use in case we're dealing with a large number of information objects
      Qubit::clearClassCaches();

      // Status update every 100 descriptions
      if (0 == $count % 100)
      {
        $this->info($message);
      }
    }

    // Final status update, if total count is not a multiple of 100
    if (0 != $count % 100)
    {
      $this->info($message);
    }

    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }
}
