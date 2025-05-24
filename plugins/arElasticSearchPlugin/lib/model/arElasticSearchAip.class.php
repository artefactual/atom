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

class arElasticSearchAip extends arElasticSearchModelBase
{
    public function load()
    {
        $sql = 'SELECT id';
        $sql .= ' FROM '.QubitAip::TABLE_NAME;

        $aips = QubitPdo::fetchAll($sql);

        $this->count = count($aips);

        return $aips;
    }

    public function populate()
    {
        $errors = [];

        // Loop through results, and add to search index
        foreach ($this->load() as $key => $item) {
            try {
                $node = new arElasticSearchAipPdo($item->id);
                $data = $node->serialize();

                QubitSearch::getInstance()->addDocument($data, 'QubitAip');

                $this->logEntry($data['filename'], $key + 1);
            } catch (sfException $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }

    public static function update($object)
    {
        $node = new arElasticSearchAipPdo($object->id);

        QubitSearch::getInstance()->addDocument($node->serialize(), 'QubitAip');

        return true;
    }
}
