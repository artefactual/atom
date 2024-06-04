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
 * arSolrQueryIds.
 */
class arSolrQueryIds extends arSolrQuery
{
    /**
     * Creates filter object.
     *
     * @param array $ids List of ids
     */
    public function __construct(array $ids = [])
    {
        $this->setIds($ids);
    }

    /**
     * Adds one more filter to the and filter.
     *
     * @param string $id Adds id to filter
     *
     * @return $this
     */
    public function addId(string $id): self
    {
        $this->params['values'][] = $id;

        return $this;
    }

    /**
     * Sets the ids to filter.
     *
     * @param array|string $ids List of ids
     *
     * @return $this
     */
    public function setIds($ids): self
    {
        if (\is_array($ids)) {
            $this->params['values'] = $ids;
        } else {
            $this->params['values'] = [$ids];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return ['ids' => $this->params];
    }
}
