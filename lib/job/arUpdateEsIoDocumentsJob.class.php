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

    foreach ($parameters['ioIds'] as $id)
    {
      if (null === $object = QubitInformationObject::getById($id))
      {
        $this->info($this->i18n->__('Invalid archival description id: %1', array('%1' => $id)));

        continue;
      }

      $title = $object->getTitle(array('cultureFallback' => true));

      if ($parameters['updateIos'] && $parameters['updateDescendants'])
      {
        arElasticSearchInformationObject::update($object, array('updateDescendants' => true));
        $message = $this->i18n->__('Updating "%1" and descendants.', array('%1' => $title));
      }
      elseif ($parameters['updateIos'])
      {
        arElasticSearchInformationObject::update($object);
        $message = $this->i18n->__('Updating "%1".', array('%1' => $title));
      }
      else
      {
        arElasticSearchInformationObject::updateDescendants($object);
        $message = $this->i18n->__('Updating descendants of "%1".', array('%1' => $title));
      }

      $this->info($message);
    }

    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }
}
