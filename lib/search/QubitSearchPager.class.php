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

class QubitSearchPager extends sfPager
{
  protected
    $nbResults = null,
    $resultSet;

  public function __construct(\Elastica\ResultSet $resultSet)
  {
    $this->resultSet = $resultSet;
  }

  /**
   * @see sfPager
   */
  public function init()
  {
    $this->setNbResults($this->resultSet->getTotalHits());

    if (0 == $this->getPage() || 0 == $this->getMaxPerPage())
    {
      $this->setLastPage(0);
    }
    else
    {
      $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));
    }
  }

  /**
   * @see sfPager
   */
  public function getResults()
  {
    // Note: to get results here beyond page 1, you'll need to call $resultSet->setFrom()
    // prior to this pager's creation.
    return $this->resultSet->getResults();
  }

  /**
   * @see sfPager
   */
  public function retrieveObject($offset)
  {
    return array_slice($this->getResults, $offset, 1);
  }

  /**
   * Returns true if the current query has any results
   *
   * @return boolean
   */
  public function hasResults()
  {
    $this->init();

    return 0 < $this->getNbResults();
  }
}
