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

class arElasticSearchFunctionObject extends arElasticSearchModelBase
{
    public function load()
    {
        $sql = 'SELECT func.id';
        $sql .= ' FROM '.QubitFunctionObject::TABLE_NAME.' func';
        $sql .= ' JOIN '.QubitObject::TABLE_NAME.' object ON func.id = object.id';
        $sql .= ' WHERE object.class_name = ?';

        $actors = QubitPdo::fetchAll($sql, ['QubitFunctionObject']);

        $this->count = count($actors);

        return $actors;
    }

    public function populate()
    {
        $errors = [];

        // Loop through results, and add to search index
        foreach ($this->load() as $key => $item) {
            try {
                $node = new arElasticSearchFunctionObjectPdo($item->id);
                $data = $node->serialize();

                QubitSearch::getInstance()->addDocument($data, 'QubitFunctionObject');

                $this->logEntry($data['i18n'][$data['sourceCulture']]['authorizedFormOfName'], $key + 1);
            } catch (sfException $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }

    public static function update($object)
    {
        $node = new arElasticSearchFunctionObjectPdo($object->id);

        QubitSearch::getInstance()->addDocument($node->serialize(), 'QubitFunctionObject');

        return true;
    }
}
