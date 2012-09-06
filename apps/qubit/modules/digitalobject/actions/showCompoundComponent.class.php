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
 * Digital Object - display compound digital asset
 *
 * @package    qubit
 * @subpackage digital object
 * @author     David Juhasz <david@artefactual.com>
 * @version    SVN: $Id: showCompoundComponent.class.php 10288 2011-11-08 21:25:05Z mj $
 */
class DigitalObjectShowCompoundComponent extends sfComponent
{
  /**
   * Show a page turner for compound digital objects
   *
   * @param sfWebRequest $request
   */
  public function execute($request)
  {
    // Find all digital objects of child information objects
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::PARENT_ID, $this->resource->informationObject->id);
    $criteria->addJoin(QubitInformationObject::ID, QubitDigitalObject::INFORMATION_OBJECT_ID);

    // Show two results on page with pager
    $this->pager = new QubitPager('QubitDigitalObject');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage(2);
    $this->pager->setPage($request->page);

    $results = $this->pager->getResults();

    $this->leftObject = $results[0];

    $this->rightObject = null;
    if (1 < count($results))
    {
      $this->rightObject = $results[1];
    }
  }
}
