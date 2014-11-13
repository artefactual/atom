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

class QubitPdoPager extends sfPager
{
  protected
    $nbResults = null,
    $rows = array(),
    $sql = null,
    $countSql = null;

  /**
   * @param string $sql        Main SQL query
   * @param array $sqlParams   Params for main SQL query
   * @param string $countSql   SQL query to count results (should SELECT only one column)
   * @param array $sqlParams   Params for SQL query to count results
   *
   * @return void
   */
  public function __construct($sql, $sqlParams, $countSql, $countSqlParams)
  {
    $this->sql = $sql;
    $this->sqlParams = $sqlParams;
    $this->countSql = $countSql;
    $this->countSqlParams = $countSqlParams;
  }

  /**
   * Add limiting to main SQL query
   *
   * @return string
   */
  public function limitMainSql()
  {
    $page = ($this->page < 1) ? 1 : $this->page;
    $offset = ($page - 1) * $this->getMaxPerPage();
    return sprintf('%s LIMIT %s, %s', $this->sql, $offset, $this->getMaxPerPage());
  }

  /**
   * @see sfPager
   */
  public function init()
  {
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
    $this->setNbResults(QubitPdo::fetchColumn($this->countSql, $this->countSqlParams));
    $this->rows = QubitPdo::fetchAll($this->limitMainSql(), $this->sqlParams);    

    $this->init();

    return $this->rows;
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
