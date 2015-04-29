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
 * AbstractSitemapObjectSet is meant to be extended by entity-specific classes.
 * Examples: SitemapInformationObjectSet and SitemapStaticPageSet.
 */
abstract class AbstractSitemapObjectSet implements Countable, Iterator
{
  /**
   * Database recordset
   */
  protected $rec;

  /**
   * Number of current record
   */
  private $cursor = -1;

  /**
   * Current object in the collection
   */
  private $item = null;

  /**
   * Propel PDO object
   */
  public $conn;

  /**
   * Sitemap configuration (sitemap.yml)
   */
  public $config;

  /**
   * Force extending class to define init() to prepare the PDO statement
   */
  abstract public function init();

  public function __construct()
  {
    $this->conn = Propel::getConnection();
  }

  public function setConfig(&$config)
  {
    $this->config = $config;
  }

  public function count()
  {
    return $this->rec->rowCount();
  }

  public function rewind()
  {
    if ($this->cursor >= 0)
    {
      $conn = Propel::getConnection();
      $this->init($conn);
    }

    $this->next();
  }

  public function next()
  {
    $this->cursor++;
    $this->item = $this->rec->fetch(PDO::FETCH_ORI_NEXT);
  }

  public function current()
  {
    return $this->item;
  }

  public function key()
  {
    return $this->cursor;
  }

  public function valid()
  {
    return $this->cursor < $this->count();
  }
}
