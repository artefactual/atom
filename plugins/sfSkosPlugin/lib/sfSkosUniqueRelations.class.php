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
 * sfSkosUniqueRelations stores pairs of integers without duplicates so they
 * can be later persisted in QubitRelation only once per pair.
 */
class sfSkosUniqueRelations implements Iterator, Countable
{
  private
    $relations = array(),
    $visited = array();

  public function __construct() {
    $this->position = 0;
  }

  protected function visited($sum)
  {
    return false !== array_key_exists($sum, $this->visited);
  }

  /**
   * Use cantor pairing function: https://en.wikipedia.org/wiki/Pairing_function.
   * The vector is sorted before so the same sum is obtained regardless the order.
   */
  protected function hash($x, $y)
  {
    $vec = array($x, $y);
    sort($vec);
    list($x, $y) = $vec;
    
    return (($x + $y) * ($x + $y + 1)) / 2 + $y;
  }

  public function insert($x, $y)
  {
    $sum = $this->hash($x, $y);
    if ($this->visited($sum))
    {
      return false;
    }

    // Mark pair as visited
    $this->visited[$sum] = true;

    $this->relations[] = array($x, $y);

    return true;
  }

  public function getAll()
  {
    return $this->relations;
  }

  public function exists($x, $y)
  {
    $sum = $this->hash($x, $y);

    return $this->visited($sum);
  }

  // Iterable

  public function rewind()
  {
    $this->position = 0;
  }

  public function current()
  {
    return $this->relations[$this->position];
  }

  public function key()
  {
    return $this->position;
  }

  public function next()
  {
    ++$this->position;
  }

  public function valid()
  {
    return isset($this->relations[$this->position]);
  }

  // Countable

  public function count()
  {
    return count($this->relations);
  }
}
