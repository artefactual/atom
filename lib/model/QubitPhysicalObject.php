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

class QubitPhysicalObject extends BasePhysicalObject
{
    public $disableNestedSetUpdating = false;
    public $indexOnSave = false;

    /**
     * Call this function when casting object instance as type string.
     *
     * @return string Physical Object Name
     */
    public function __toString()
    {
        $string = $this->name;
        if (!isset($string)) {
            $string = $this->getName(['sourceCulture' => true]);
        }

        return (string) $string;
    }

    public function insert($connection = null)
    {
        if (!isset($this->slug)) {
            $this->slug = QubitSlug::slugify($this->__get('name', ['sourceCulture' => true]));
        }

        return parent::insert($connection);
    }

    public function getLabel()
    {
        $label = '';

        if ($this->type) {
            $label .= $this->type.': ';
        }

        $label .= $this->__toString();

        if (0 == strlen($location = $this->getLocation())) {
            $location = $this->getLocation(['sourceCulture' => true]);
        }

        if (0 < strlen($location)) {
            $label .= ' - '.$location;
        }

        return $label;
    }

    /**
     * Overwrite BasePhysicalObject::delete() method to add cascading delete
     * logic.
     *
     * @param mixed $connection a database connection object
     */
    public function delete($connection = null)
    {
        $this->deleteInformationObjectRelations();

        parent::delete($connection);
    }

    /**
     * Delete relation records linking this physical object to information objects.
     */
    public function deleteInformationObjectRelations()
    {
        $informationObjectRelations = QubitRelation::getRelationsBySubjectId(
            $this->id,
            ['typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID]
        );

        foreach ($informationObjectRelations as $relation) {
            $relation->delete();
        }
    }

    /**
     * Get related information object via QubitRelation relationship.
     *
     * @param array $options list of options to pass to QubitQuery
     *
     * @return QubitQuery collection of Information Objects
     */
    public function getInformationObjects($options = [])
    {
        $criteria = new Criteria();
        $criteria->addJoin(QubitPhysicalObject::ID, QubitRelation::SUBJECT_ID);
        $criteria->addJoin(QubitRelation::OBJECT_ID, QubitInformationObject::ID);
        $criteria->add(QubitPhysicalObject::ID, $this->id);

        return QubitInformationObject::get($criteria);
    }

    /**
     * Get physical objects by name and location.
     *
     * @param name  The name of the physical object
     * @param location  The location of the physical object
     * @param typeId  The type id of the physical object (Box, etc.)
     * @param mixed $name
     * @param mixed $location
     * @param mixed $typeId
     *
     * @return QubitQuery collection of Physical Objects
     */
    public static function getPhysicalObjectsByNameAndLocation($name, $location, $typeId)
    {
        $criteria = new Criteria();
        $criteria->addJoin(QubitPhysicalObject::ID, QubitPhysicalObjectI18n::ID);

        if ($typeId) {
            $criteria->add(QubitPhysicalObject::TYPE_ID, $typeId);
        }

        if ($location) {
            $criteria->add(QubitPhysicalObjectI18n::LOCATION, $location);
        }

        $criteria->add(QubitPhysicalObjectI18n::NAME, $name);

        return QubitPhysicalObject::get($criteria);
    }

    /**
     * Get physical objects by name.
     *
     * @param name    The name of the physical object
     * @param options Array of optional paramaters
     * @param mixed $name
     * @param mixed $options
     *
     * @return QubitQuery A collection of matching physicalobject objects
     */
    public static function getByName($name, $options = [])
    {
        $criteria = new Criteria();

        $criteria->addJoin(QubitPhysicalObject::ID, QubitPhysicalObjectI18n::ID);

        if (isset($options['partialMatch']) && 'begin' == $options['partialMatch']) {
            $criteria->add(
                QubitPhysicalObjectI18n::NAME,
                $name.'%',
                Criteria::LIKE
            );
        } else {
            $criteria->add(QubitPhysicalObjectI18n::NAME, $name);
        }

        if (isset($options['culture'])) {
            $criteria->add(QubitPhysicalObjectI18n::CULTURE, $options['culture']);
        }

        return QubitPhysicalObject::get($criteria);
    }

    /**
     * Get whether or not a physical object matching name/location/type
     * exists in the current collection already.
     *
     * @param name  The name of the physical object
     * @param location  The location of the physical object
     * @param typeId  The type id of the physical object (Box, etc.)
     * @param collectionId  The collection id to check inside for the physical object
     * @param mixed $name
     * @param mixed $location
     * @param mixed $typeId
     * @param mixed $collectionId
     *
     * @return A physical object if found matching, otherwise null
     */
    public static function checkPhysicalObjectExistsInCollection($name, $location, $typeId, $collectionId)
    {
        if (!isset($collectionId)) {
            return;
        }

        // Get collection lft and rgt values from the database as they are not
        // always being updated in the class cache in multi-level imports
        $sql = 'SELECT lft, rgt FROM information_object WHERE id = :id;';
        $collection = QubitPdo::fetchOne($sql, [':id' => $collectionId]);
        if (!isset($collection)) {
            return;
        }

        $objs = QubitPhysicalObject::getPhysicalObjectsByNameAndLocation($name, $location, $typeId);
        foreach ($objs as $physObj) {
            $sql = 'SELECT rel.id FROM relation rel
                INNER JOIN information_object io ON rel.object_id = io.id
                WHERE rel.subject_id = :id AND rel.type_id = :typeId
                AND io.lft >= :lft AND io.lft <= :rgt;';

            $params = [
                ':id' => $physObj->id,
                ':typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID,
                ':lft' => $collection->lft,
                ':rgt' => $collection->rgt,
            ];

            if (QubitPdo::fetchOne($sql, $params)) {
                return $physObj;
            }
        }
    }

    /**
     * Add new information object relations.
     *
     * @param array newInfobjIds array of new information object ids to link
     */
    public function addInfobjRelations(array $newInfobjIds)
    {
        if (empty($this->id)) {
            throw new sfException('Invalid QubitPhysicalObject id');
        }

        foreach ($newInfobjIds as $infobjId) {
            $relation = new QubitRelation();
            $relation->subjectId = $this->id;
            $relation->objectId = $infobjId;
            $relation->typeId = QubitTerm::HAS_PHYSICAL_OBJECT_ID;

            $relation->indexOnSave = $this->indexOnSave;

            $relation->save();
        }
    }

    /**
     * Update.
     */
    public function updateInfobjRelations(array $relatedInfobjIds)
    {
        $existingRelations = [];

        if (empty($this->id)) {
            throw new sfException('Invalid QubitPhysicalObject id');
        }

        // Find existing relations
        if (
            null !== $relations = $this->getRelationsAsArray(
                QubitTerm::HAS_PHYSICAL_OBJECT_ID
            )
        ) {
            foreach ($relations as $row) {
                $existingRelations[$row['id']] = $row['object_id'];
            }
        }

        // Save any new relations
        if (
            false != $newInfobjIds = array_diff(
                $relatedInfobjIds,
                $existingRelations
            )
        ) {
            $this->addInfobjRelations($newInfobjIds);
        }

        // Delete any obsolete relations
        $obsoleteInfobjIds = array_diff($existingRelations, $relatedInfobjIds);

        foreach ($obsoleteInfobjIds as $infobjId) {
            $relationId = array_search($infobjId, $existingRelations);

            if (
                false === $relationId
                || null === $relation = QubitRelation::getById($relationId)
            ) {
                continue;
            }

            $relation->indexOnSave = $this->indexOnSave;
            $relation->delete();
        }
    }

    public function getRelationsAsArray(?int $typeId = null)
    {
        if (empty($this->id)) {
            throw new sfException('Invalid QubitPhysicalObject id');
        }

        $sql = 'SELECT
            id,
            subject_id,
            object_id,
            type_id,
            start_date,
            end_date,
            source_culture
            FROM relation WHERE subject_id = :id';

        if (isset($typeId)) {
            $sql .= ' AND type_id = :typeId';
        }

        $results = QubitPdo::prepareAndExecute($sql, [
            ':id' => $this->id,
            ':typeId' => $typeId,
        ]);

        return $results->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * An quick update of an existing physical_object database row.
     *
     * If passed data wouldn't alter the current db data, return false without
     * writing to the database
     *
     * Currently only updates the following columns:
     * - object.updated_at
     * - physical_object.type_id
     * - physical_object_i18n.location
     *
     * @param array     $data       a keyed array (column => value)
     * @param PropelPDO $connection a connection object
     */
    public function quickUpdate(array $data, $connection = null)
    {
        $doUpdate = false;
        $newvals = [];

        $updateCols = [
            'typeId',
            'location',
        ];

        if (!isset($connection)) {
            $connection = Propel::getConnection();
        }

        foreach ($updateCols as $name) {
            if (!empty($data[$name]) && $this->{$name} != $data[$name]) {
                $doUpdate = true;
                $newvals[$name] = $data[$name];
            } else {
                $newvals[$name] = $this->{$name};
            }
        }

        if (!$doUpdate) {
            // The incoming data matches the data already in the database, so avoid
            // unnecessary db queries

            return false;
        }

        $connection->beginTransaction();

        // Update object.updated_at column with current timestamp
        $this->updateUpdatedAt($connection);

        // Update physical_object.type_id
        $sth = $connection->prepare(
            'UPDATE '.self::TABLE_NAME
            .' SET '.self::TYPE_ID
            .' = :typeId WHERE `id` = :id'
        );

        $result = $sth->execute([
            ':typeId' => $newvals['typeId'],
            ':id' => $this->id,
        ]);

        if (!$result) {
            return $result;
        }

        // Update physical_object_i18n.location
        $sth = $connection->prepare(
            'UPDATE '.QubitPhysicalObjectI18n::TABLE_NAME
            .' SET '.QubitPhysicalObjectI18n::LOCATION
            .' = :location WHERE `id` = :id'
        );

        $result = $sth->execute([
            ':location' => $newvals['location'],
            ':id' => $this->id,
        ]);

        $connection->commit();

        return $result;
    }
}
