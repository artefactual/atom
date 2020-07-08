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
 * Represent the time, place and/or agent of events in an artifact's history
 *
 * @package    AccesstoMemory
 * @subpackage model
 */
class QubitAip extends BaseAip
{
  // Flag for updating search index on save
  public
    $indexOnSave = true;

  /**
   * Additional save functionality (e.g. update search index)
   *
   * @param mixed $connection a database connection object
   * @return QubitAip self-reference
   */
  public function save($connection = null)
  {
    parent::save($connection);

    if ($this->indexOnSave)
    {
      QubitSearch::getInstance()->update($this);
    }

    return $this;
  }

  /**
   * Get AIP by UUID
   *
   * @param string $uuid AIP UUID
   *
   * @return QubitQuery resultset object
   */
  public static function getByUuid($uuid)
  {
    $c = new Criteria;
    $c->add(self::UUID, $uuid);

    return self::getOne($c);
  }
}
