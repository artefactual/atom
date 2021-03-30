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
 * Represent the time, place and/or agent of events in an artifact's history.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitEvent extends BaseEvent
{
    // Flag for updating search index on save or delete
    public $indexOnSave = true;

    /**
     * Additional save functionality, e.g. update search index.
     *
     * @param mixed $connection provide a database connection
     *
     * @return QubitInformationObject self-reference
     */
    public function save($connection = null)
    {
        // TODO $cleanObject = $this->object->clean;
        $cleanObjectId = $this->__get('objectId', ['clean' => true]);

        parent::save($connection);

        if ($this->indexOnSave) {
            // Update IO descendants in creation events
            $options = [];
            if (QubitTerm::CREATION_ID == $this->typeId) {
                $options['updateDescendants'] = true;
            }

            if ($this->objectId != $cleanObjectId && null !== QubitObject::getById($cleanObjectId)) {
                QubitSearch::getInstance()->update(QubitObject::getById($cleanObjectId), $options);
            }

            if (isset($this->object)) {
                QubitSearch::getInstance()->update($this->object, $options);
            }
        }

        return $this;
    }

    public function delete($connection = null)
    {
        // Get related object
        $object = $this->getObject();

        // Delete event
        parent::delete($connection);

        // Update object
        if (isset($object) && $this->indexOnSave) {
            // Update IO descendants in creation events
            $options = [];
            if (QubitTerm::CREATION_ID == $this->typeId) {
                $options['updateDescendants'] = true;
            }

            QubitSearch::getInstance()->update($object, $options);
        }
    }

    public function getPlace(array $options = [])
    {
        $criteria = new Criteria();
        $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->id);
        $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::PLACE_ID);
        $relation = QubitObjectTermRelation::get($criteria);

        if (count($relation) > 0) {
            return $relation[0]->getTerm();
        }

        return null;
    }

    protected function insert($connection = null)
    {
        $this->slug = QubitSlug::slugify($this->slug);

        return parent::insert($connection);
    }
}
