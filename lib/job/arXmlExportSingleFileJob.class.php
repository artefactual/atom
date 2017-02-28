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
 * A worker to export a description as XML.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arXmlExportSingleFileJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected
    $extraRequiredParameters = array('objectId', 'format');

  protected $params = array();

  public function runJob($parameters)
  {
    $this->params = $parameters;

    if (!is_numeric($this->params['objectId']))
    {
      $this->error($this->i18n->__('Object ID must be numberic.'));

      return false;
    }

    $this->info($this->i18n->__('Starting %1 export of information object %2.', array('%1' => strtoupper($this->params['format']), '%2' => $this->params['objectId'])));
    $this->exportResource();

    // Mark job as complete
    $this->info($this->i18n->__('Export complete.'));
    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }

  /**
   * Export XML representation of information object as file
   *
   * @param object  information object to be export
   *
   * @return null
   */
  protected function exportResource()
  {
    $resource = QubitInformationObject::getById($this->params['objectId']);

    if (null === $resource)
    {
      throw new sfException($this->i18n->__('Information object %1% does not eist', array('%1%' => $this->params['objectId'])));
    }

    try
    {
      // Print warnings/notices here too, as they are often important.
      $errLevel = error_reporting(E_ALL);

      $cache = new QubitInformationObjectXmlCache;
      $cache->export($resource);

      error_reporting($errLevel);
    }
    catch (Exception $e)
    {
      throw new sfException($this->i18n->__('Invalid XML generated for information object %1%.', array('%1%' => $this->params['objectId'])));
    }
  }
}
