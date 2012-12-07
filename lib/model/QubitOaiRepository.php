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
 * Extend BaseSetting functionality.
 *
 * @package    AccesstoMemory
 * @subpackage model
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class QubitOaiRepository extends BaseOaiRepository
{
  /**
   * Get repository by URI
   * @param string $url the url of the repository to find
   * @return QubitQuery collection of OAI-PMH Repositories
   */
  public static function getByURI($URI)
  {
    $criteria = new Criteria;
    $criteria->add(QubitOaiRepository::URI, $URI, Criteria::LIKE);
    $criteria->addAscendingOrderByColumn(QubitOaiRepository::NAME);
    return self::get($criteria);
  }
}
