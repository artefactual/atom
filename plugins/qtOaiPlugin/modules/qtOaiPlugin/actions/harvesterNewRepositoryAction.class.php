<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Generate the OAI-PMH response
 *
 * @package    qubit
 * @subpackage oai
 * @version    svn: $Id: harvesterNewRepositoryAction.class.php 10288 2011-11-08 21:25:05Z mj $
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class qtOaiPluginHarvesterNewRepositoryAction extends sfAction
{
   /*
   * Executes action
   *
   * @param sfRequest $request A request object
   */
  public function execute($request)
  {
    if ($this->request->getAttribute('preExistingRepository'))
    {
      $this->preExistingRepository = $this->request->getAttribute('preExistingRepository');
    }
    if ($this->request->getAttribute('parsingErrors'))
    {
      $this->parsingErrors = $this->request->getAttribute('parsingErrors');
    } else
    {
      // Add context message so that the user knows what repository was added
    }
  }
}
