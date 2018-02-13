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
 *
 *
 * @package    symfony
 * @subpackage jobs
 */

class arUpdateDescendantsJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('tldId');

  public function runJob($parameters)
  {
    if (null === $this->topLevelDesc = QubitInformationObject::getById($parameters['tldId']))
    {
      $this->error($this->i18n->__('Invalid description id: %1', array('%1' => $parameters['tldId'])));
      return false;
    }

    $this->info($this->i18n->__("Updating child descriptions' inherited fields from ancestors (id: %1)", array('%1' => $this->topLevelDesc->id)));
    $searchIo = new arElasticSearchInformationObject;
    $searchIo->recursivelyUpdateInformationObjects($parameters['tldId'], count($this->topLevelDesc->descendants),
      'updateInheritedFields');


    $this->info($this->i18n->__('Update(s) completed.'));
    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }
}
