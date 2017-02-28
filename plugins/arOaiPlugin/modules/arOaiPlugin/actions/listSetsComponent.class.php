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
 * Generate  listSets response of the OAI-PMH protocol for the Access to Memory (AtoM)
 *
 * @package    AccesstoMemory
 * @subpackage oai
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class arOaiPluginlistSetsComponent extends arOaiPluginComponent
{
  /**
   * Executes action
   *
   * @param sfRequest $request A request object
   */
  public function execute($request)
  {
    $this->setUpdateParametersFromRequest($request);
    $this->getPagedOaiSets($request);
    $this->setRequestAttributes($request);
  }

  private function getPagedOaiSets($request)
  {
    $options = array('filterDrafts' => true);
    if (isset($this->cursor))
    {
      $options['offset'] = $this->cursor;
    }
    $options['limit'] = QubitSetting::getByName('resumption_token_limit')->__toString();

    $results = QubitOai::getOaiSets($options);
    $this->oaiSets = $results['data'];
    $this->remaining = $results['remaining'];
    $resumptionCursor = $this->cursor + $options['limit'];
    $this->resumptionToken  = base64_encode(json_encode(array('cursor' => $resumptionCursor)));
  }
}
