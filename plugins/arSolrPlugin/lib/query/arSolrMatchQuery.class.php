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

class arSolrMatchQuery extends arSolrTermQuery
{
    /**
     * Params.
     *
     * @var array
     */
    protected $params = [];

    public function setFieldQuery($field, $value)
    {
        $this->setTerm($field, $value);
    }

    public function generateQueryParams()
    {
        $matchField = $this->getTermField();
        if (!isset($matchField)) {
            throw new Exception('Match field is not set.');
        }

        $matchValue = $this->getTermValue();
        if (!isset($matchValue)) {
            throw new Exception('Match value is not set.');
        }

        $type = $this->getType();
        if (!isset($type)) {
            throw new Exception("Match 'type' is not set.");
        }

        $this->query = [
            'query' => [
                'edismax' => [
                    'query' => "{$type}.{$matchField}:{$matchValue}~",
                ],
            ],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];
    }
}
