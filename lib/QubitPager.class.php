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
 * Qubit specifc extension to the sfPropelPager
 *
 * @package AccesstoMemory
 * @author  David Juhasz <david@artefactual.com>
 * @author  Mike Cantelon <mike@artefactual.com>
 */
class QubitPager extends sfPropelPager
{
  protected

    // Override sfPager::$nbResults = 0
    $nbResults = null;

  /**
   * BasePeer::doCount() returns PDOStatement
   */
  public function doCount(Criteria $criteria)
  {
    call_user_func(array($this->class, 'addSelectColumns'), $criteria);

    return BasePeer::doCount($criteria)->fetchColumn(0);
  }

  public function doSelect(Criteria $criteria)
  {
    return call_user_func(array($this->class, 'get'), $criteria);
  }

  /**
   * @see sfPropelPager
   */
  public function getClassPeer()
  {
    return $this;
  }

  /**
   * Override ::getNbResults() to call ->init() first
   *
   * @see sfPager
   */
  public function getNbResults()
  {
    if (!isset($this->nbResults))
    {
      $this->init();
    }

    return parent::getNbResults();
  }

  /**
   * Override ::getResults() to call ->init() first
   *
   * @see sfPager
   */
  public function getResults()
  {
    $this->init();

    return parent::getResults();
  }

  /**
   * Similar to getResults but gets raw row data, not objects
   *
   * Columns need to be selected using the criteria
   *
   * Example: $criteria->addSelectColumn(QubitInformationObject::ID);
   * 
   */
  public function getRows(Criteria $criteria)
  {
    $this->init();

    $class = $this->class;

    $options = array();
    $options['connection'] = Propel::getConnection($class::DATABASE_NAME);
    $options['rows'] = true;

    return QubitQuery::createFromCriteria($criteria, $this->class, $options);
  }
}
