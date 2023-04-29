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

class QubitNote extends BaseNote
{
    // Flag for updating search index on save or delete
    public $indexOnSave = true;

    public function __get($name)
    {
        $args = func_get_args();

        $options = [];
        if (1 < count($args)) {
            $options = $args[1];
        }

        if ('type' === $name && true === $options['sourceCulture']) {
            $sql = 'SELECT term_i18n.name FROM note
                JOIN term_i18n ON term_i18n.id = note.type_id
                WHERE term_i18n.id=?
                AND note.id=? AND term_i18n.culture=?';

            return QubitPdo::fetchColumn($sql, [$this->typeId, $this->id, $this->sourceCulture]);
        }

        return call_user_func_array([$this, 'BaseNote::__get'], $args);
    }

    public function __toString()
    {
        if (null === $content = $this->getContent()) {
            $content = $this->getContent(['sourceCulture' => true]);
        }

        return (string) $content;
    }

    public function save($connection = null)
    {
        // TODO: $cleanObject = $this->object->clean;
        $cleanObjectId = $this->__get('objectId', ['clean' => true]);

        parent::save($connection);

        if ($this->indexOnSave) {
            if ($this->objectId != $cleanObjectId && null !== QubitInformationObject::getById($cleanObjectId)) {
                QubitSearch::getInstance()->update(QubitInformationObject::getById($cleanObjectId));
            }

            if ($this->object instanceof QubitInformationObject) {
                QubitSearch::getInstance()->update($this->object);
            }
        }

        return $this;
    }

    public function delete($connection = null)
    {
        parent::delete($connection);

        if ($this->getObject() instanceof QubitInformationObject) {
            QubitSearch::getInstance()->update($this->getObject());
        }
    }
}
