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

class arSolrIdsQuery extends arSolrTermsQuery
{
    /**
     * Constructor.
     *
     * @param mixed      $searchQuery
     * @param null|mixed $term
     */
    public function __construct($ids = null)
    {
        $this->setIds($ids);
    }

    public function setIds($ids) {
        $this->termField = 'id';
        $this->termValues = $ids;
    }

    public function getIds() {
        return $this->termValues;
    }

    protected function generateQueryParams()
    {
        $termField = $this->getTermField();
        $ids = $this->getIds();
        if (!isset($ids) || count($ids) == 0) {
            throw new Exception('Ids are not set.');
        }

        $type = $this->getType();
        if (!isset($type)) {
            throw new Exception("Field 'type' is not set.");
        }

        $queryString = implode(" OR ", $ids);
        $this->query = [
            'query' => [
                'edismax' => [
                    'query' => "{$type}.{$termField}:({$queryString})",
                ],
            ],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];
    }
}
