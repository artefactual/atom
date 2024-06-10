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

class arSolrBoolQuery extends arSolrAbstractQuery
{
  /**
   * mustQuery.
   *
   * @var array
   */
  protected $mustQuery = [];

  /**
   * mustNotQuery.
   *
   * @var array
   */
  protected $mustNotQuery = [];

  /**
   * boolQuery.
   *
   * @var array
   */
  protected $boolQuery = [];

  /**
   * Assemble BoolQuery.
   *
   * @return arSolrBoolQuery object
   */
  public function generateBoolQuery()
  {
    $this->boolQuery = [
      "bool" => array_merge($this->mustQuery, $this->mustNotQuery)
    ];

    return $this->boolQuery;
  }

  /**
   * Add must for BoolQuery.
   *
   * @param array $field  field
   * @param string $must  must query
   * @param string $qp    query parser (default "dismax")
   *
   * @return array     must part of query
   */
  public function addMust($field, $must, $qp = 'dismax')
  {
    $this->mustQuery = [
      "must" => [
        [
          $qp => [
            "df" => $field,
            "query" => $must
          ]
        ]
      ]
    ];

    return $this->mustQuery;
  }

  /**
   * Add must not for BoolQuery.
   *
   * @param $lower    lower limit
   * @param $upper    upper limit
   * @param $mustNot  must not query
   *
   * @return array     must not part of query
   */
  public function addMustNot($mustNot, $lower = 0, $upper = 5)
  {
    $this->mustNotQuery = [
      "must_not" => [[
        "frange" => [
          "l" => $lower,
          "u" => $upper,
          "query" => $mustNot
        ]
      ]]
    ];

    return $this->mustNotQuery;
  }
}
