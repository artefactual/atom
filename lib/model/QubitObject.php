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

class QubitObject extends BaseObject implements Zend_Acl_Resource_Interface
{
    public function __isset($name)
    {
        $args = func_get_args();

        switch ($name) {
            case 'slug':
                if (!array_key_exists('slug', $this->values)) {
                    $connection = Propel::getConnection(QubitObject::DATABASE_NAME);

                    $statement = $connection->prepare('
                        SELECT '.QubitSlug::SLUG.'
                        FROM '.QubitSlug::TABLE_NAME.'
                        WHERE ? = '.QubitSlug::OBJECT_ID
                    );
                    $statement->execute([$this->id]);

                    if (false !== $row = $statement->fetch()) {
                        $this->values['slug'] = $row[0];
                    }
                }

                return isset($this->values['slug']);

            default:
                return call_user_func_array('BaseObject::__isset', $args);
        }
    }

    public function __get($name)
    {
        $args = func_get_args();

        switch ($name) {
            case 'slug':
                if (!array_key_exists('slug', $this->values)) {
                    $connection = Propel::getConnection(QubitObject::DATABASE_NAME);

                    $statement = $connection->prepare('
                        SELECT '.QubitSlug::SLUG.'
                        FROM '.QubitSlug::TABLE_NAME.'
                        WHERE ? = '.QubitSlug::OBJECT_ID
                    );
                    $statement->execute([$this->id]);
                    $row = $statement->fetch();
                    $this->values['slug'] = $row[0];
                }

                return $this->values['slug'];

            default:
                return call_user_func_array('BaseObject::__get', $args);
        }
    }

    public function __set($name, $value)
    {
        $args = func_get_args();

        switch ($name) {
            case 'slug':
                $this->values['slug'] = $value;

                return $this;

            default:
                return call_user_func_array('BaseObject::__set', $args);
        }
    }

    public function save($connection = null)
    {
        parent::save($connection);

        // Save updated objectTermRelations
        foreach ($this->objectTermRelationsRelatedByobjectId as $relation) {
            $relation->indexOnSave = false;
            $relation->object = $this;
            $relation->save();
        }

        // Save updated notes
        foreach ($this->notes as $note) {
            $note->indexOnSave = false;
            $note->object = $this;
            $note->save();
        }

        // Save updated properties
        foreach ($this->propertys as $property) {
            $property->indexOnSave = false;
            $property->object = $this;
            $property->save();
        }

        // Save updated object relations
        foreach ($this->relationsRelatedByobjectId->transient as $relation) {
            $relation->indexOnSave = false;
            $relation->object = $this;
            $relation->save();
        }

        // Save updated subject relations
        foreach ($this->relationsRelatedBysubjectId->transient as $relation) {
            $relation->indexOnSave = false;
            $relation->subject = $this;
            $relation->save();
        }

        // Save updated other namnes
        foreach ($this->otherNames as $otherName) {
            $otherName->object = $this;
            $otherName->save();
        }

        return $this;
    }

    public function insertSlug($connection = null)
    {
        if (!isset($connection)) {
            $connection = Propel::getConnection();
        }

        if (isset($this->slug)) {
            $statement = $connection->prepare('
                INSERT INTO '.QubitSlug::TABLE_NAME.' ('.QubitSlug::OBJECT_ID
                .', '.QubitSlug::SLUG.') VALUES (?, ?)'
            );

            // Unless it is set, get random, digit and letter slug
            if (1 > strlen($this->slug)) {
                $statement->execute([$this->id, QubitSlug::getUnique($connection)]);

                return $this;
            }

            // Truncate to 235 characters to prevent issue of long title collision
            // causing an infinite loop when computing a unique slug
            $this->slug = substr($this->slug, 0, 235);

            // Compute unique slug adding contiguous numeric suffix
            $suffix = 2;
            $triedQuery = false;
            $stem = $this->slug;

            do {
                try {
                    if ($this->checkIfSlugIsReserved()) {
                        throw new RuntimeException('Reserved slug');
                    }

                    $statement->execute([$this->id, $this->slug]);
                    unset($suffix);
                }
                // Collision? Try next suffix
                catch (Exception $e) {
                    // If exception is unexpected re-throw it
                    if (!($e instanceof RuntimeException || $e instanceof PDOException)) {
                        throw $e;
                    }

                    if (!$triedQuery) {
                        $triedQuery = true;

                        // Try getting value of last suffix for this slug in database to
                        // avoid long loops trying to find next suffix
                        $query = 'SELECT slug FROM slug WHERE slug LIKE \''.$stem.'-%\' ORDER BY id DESC LIMIT 1;';
                        $stmt2 = $connection->query($query);

                        if ($lastSlugInSet = $stmt2->fetchColumn()) {
                            if (preg_match('/-(\d+)$/', $lastSlugInSet, $matches)) {
                                $suffix = intval($matches[1]) + 1;
                            }
                        }
                    } else {
                        // Simple increment in case SQL query doesn't work for some reason
                        ++$suffix;
                    }

                    $this->slug = "{$stem}-{$suffix}";
                }
            } while (isset($suffix));
        }

        return $this;
    }

    /**
     * Checks to see if a path links to an action.
     *
     * @param string $url URL or path
     *
     * @return bool True if path links to an action
     */
    public static function actionExistsForUrl($url)
    {
        $context = sfContext::getInstance();
        $route = $context->getRouting()->findRoute($url);
        $routeParams = $route['parameters'];

        return $context->getController()->actionExists($routeParams['module'], $routeParams['action']);
    }

    public function delete($connection = null)
    {
        if (!isset($connection)) {
            $connection = Propel::getConnection();
        }

        // Delete slug
        $statement = $connection->prepare(
            'DELETE FROM '.QubitSlug::TABLE_NAME.
            ' WHERE '.QubitSlug::OBJECT_ID.' = ?'
        );
        $statement->execute([$this->id]);

        // Delete other names
        if (0 < count($this->otherNames)) {
            foreach ($this->otherNames as $otherName) {
                $otherName->delete();
            }
        }

        parent::delete($connection);
    }

    public static function getBySlug($slug)
    {
        $criteria = new Criteria();
        $criteria->add(QubitSlug::SLUG, $slug);
        $criteria->addJoin(QubitSlug::OBJECT_ID, QubitObject::ID);

        return QubitObject::get($criteria)->__get(0);
    }

    /**
     * Required by Zend_Acl_Resource_Interface interface.
     */
    public function getResourceId()
    {
        return $this->id;
    }

    public function updateUpdatedAt($connection = null)
    {
        if (!isset($connection)) {
            $connection = Propel::getConnection();
        }

        if (!isset($this->id)) {
            throw new sfException('QubitObject->id must be set');
        }

        $sth = $connection->prepare(
            'UPDATE '.self::TABLE_NAME.
            ' SET '.self::UPDATED_AT.' = NOW() WHERE id = :id'
        );

        return $sth->execute([':id' => $this->id]);
    }

    // Status

    public function setStatus($options = [])
    {
        $status = $this->getStatus(['typeId' => $options['typeId']]);
        // only create a new status object if type is not already set
        if (null === $status) {
            $status = new QubitStatus();
            $status->setTypeId($options['typeId']);
        }
        $status->setStatusId($options['statusId']);
        $this->statuss[] = $status;

        return $this;
    }

    public function getStatus($options = [])
    {
        $criteria = new Criteria();
        $criteria->add(QubitStatus::OBJECT_ID, $this->id);
        $criteria->add(QubitStatus::TYPE_ID, $options['typeId']);

        return QubitStatus::getOne($criteria);
    }

    /**
     * Check to see if note is saved in memory (field was updated).
     *
     * Used by ISAD, DACS, and RAD templates when duplicating a description,
     * specifcally for updating the 'Language and script notes' field.
     *
     * @return array $notes
     */
    public function getMemoryNotesByType(array $options = [])
    {
        // Check memory for notes
        if (!count($this->notes) < 0) {
            return $this->getNotesByType($options);
        }

        $noteTypeId = $options['noteTypeId'];
        $notes = new ArrayObject();

        foreach ($this->notes as $note) {
            if ($note->typeId == $noteTypeId) {
                $notes->append($note);
            }
        }

        return $notes;
    }

    public function getNotesByType(array $options = [])
    {
        $criteria = new Criteria();
        $criteria->addJoin(QubitNote::TYPE_ID, QubitTerm::ID);
        $criteria->add(QubitNote::OBJECT_ID, $this->id);
        if (isset($options['noteTypeId'])) {
            $criteria->add(QubitNote::TYPE_ID, $options['noteTypeId']);
        }
        if (isset($options['exclude'])) {
            // Turn exclude string into an array
            $excludes = (is_array($options['exclude'])) ? $options['exclude'] : [$options['exclude']];

            foreach ($excludes as $exclude) {
                $criteria->addAnd(QubitNote::TYPE_ID, $exclude, Criteria::NOT_EQUAL);
            }
        }

        return QubitNote::get($criteria);
    }

    public function getNotesByTaxonomy(array $options = [])
    {
        $criteria = new Criteria();
        $criteria->addJoin(QubitNote::TYPE_ID, QubitTerm::ID);
        $criteria->add(QubitNote::OBJECT_ID, $this->id);
        if (isset($options['taxonomyId'])) {
            $criteria->add(QubitTerm::TAXONOMY_ID, $options['taxonomyId']);
        }

        return QubitNote::get($criteria);
    }

    /**
     * Get the digital object related to this resource. The resource to
     * digitalObject relationship is "one to zero or one".
     *
     * @return mixed QubitDigitalObject or null
     */
    public function getDigitalObject()
    {
        $digitalObjects = $this->digitalObjectsRelatedByobjectId;

        if (count($digitalObjects) > 0) {
            return $digitalObjects[0];
        }

        return null;
    }

    /**
     * Get the digital object's public URL.
     *
     * @return string digital object URL or null
     */
    public function getDigitalObjectPublicUrl()
    {
        // Set digital object URL
        $do = $this->digitalObjectsRelatedByobjectId[0];
        if (!isset($do)) {
            return;
        }

        if (!$do->masterAccessibleViaUrl()) {
            return;
        }

        $path = $do->getFullPath();

        // If path is external, it's absolute so return it
        if (QubitTerm::EXTERNAL_URI_ID == $do->usageId) {
            return $path;
        }

        if (
            !QubitAcl::check($this, 'readMaster') && null !== $do->reference
            && QubitAcl::check($this, 'readReference')
        ) {
            $path = $do->reference->getFullPath();
        }

        return rtrim(QubitSetting::getByName('siteBaseUrl'), '/').'/'.ltrim($path, '/');
    }

    /**
     * Return the URL for the digital object master linked to this object, if the
     * current user has "read master" authorization.
     *
     * @return null|string The URL of the digital object master, or null
     */
    public function getDigitalObjectUrl()
    {
        $digitalObject = $this->getDigitalObject();

        // If there are no digital objects linked to this actor, return null
        if (null === $digitalObject) {
            return null;
        }

        // If the linked digital object isn't accessible via URL, return null
        if (!$digitalObject->masterAccessibleViaUrl()) {
            return null;
        }

        // If the current user isn't authorized to read the master, return null
        if (!QubitAcl::check($this, 'readMaster')) {
            return null;
        }

        if (QubitTerm::EXTERNAL_URI_ID == $digitalObject->usageId) {
            // Return external digital object URL
            return $digitalObject->path;
        }

        $request = sfContext::getInstance()->getRequest();

        // Return the URL for the master digital object on the local filesystem
        return $request->getUriPrefix()
            .$request->getRelativeUrlRoot()
            .$digitalObject->getFullPath();
    }

    /**
     * Get the digital object's checksum value.
     *
     * @return string digital object checksum or null
     */
    public function getDigitalObjectChecksum()
    {
        if (null !== $do = $this->getDigitalObject()) {
            return $do->getChecksum();
        }
    }

    /**
     * Check if this object is linked to a text (PDF) digital object.
     *
     * @return bool true if related digital object has mediaType of "text"
     */
    public function hasTextDigitalObject()
    {
        $digitalObject = $this->getDigitalObject();

        if (null === $digitalObject) {
            return false;
        }

        return QubitTerm::TEXT_ID == $digitalObject->mediaTypeId;
    }

    // Other names

    public function getOtherNames($options = [])
    {
        $criteria = new Criteria();
        $criteria->add(QubitOtherName::OBJECT_ID, $this->id);

        if (isset($options['typeId'])) {
            $criteria->add(QubitOtherName::TYPE_ID, $options['typeId']);
        }

        return QubitOtherName::get($criteria);
    }

    // Rights

    public function getRights($options = [])
    {
        return QubitRelation::getRelationsBySubjectId($this->id, ['typeId' => QubitTerm::RIGHT_ID]);
    }

    // Properties

    /**
     * Get first matching related property by name (optionally scope).
     * Return an empty QubitProperty object if a matching one doesn't exist.
     *
     * @param string $name
     * @param array  $options
     *
     * @return QubitProperty
     */
    public function getPropertyByName($name, $options = [])
    {
        if (null === $property = QubitProperty::getOneByObjectIdAndName($this->id, $name, $options)) {
            $property = new QubitProperty();
        }

        return $property;
    }

    // Physical Objects

    /**
     * Add a relation from this object to a physical object. Check to make
     * sure the relationship is unique.
     *
     * @param QubitPhysicalObject $physicalObject Subject of relationship
     *
     * @return QubitObject this object
     */
    public function addPhysicalObject($physicalObject)
    {
        // Verify that $physicalObject is really a Physical Object and
        // Don't add an identical object -> physical object relationship
        if ('QubitPhysicalObject' == get_class($physicalObject) && null === $this->getPhysicalObject($physicalObject->id)) {
            $relation = new QubitRelation();
            $relation->setSubject($physicalObject);
            $relation->setTypeId(QubitTerm::HAS_PHYSICAL_OBJECT_ID);

            $this->relationsRelatedByobjectId[] = $relation;
        }

        return $this;
    }

    /**
     * Get a specific physical object related to this object.
     *
     * @param int $physicalObjectId the id of the related physical object
     *
     * @return mixed the QubitRelation object on success, null if no match found
     */
    public function getPhysicalObject($physicalObjectId)
    {
        $criteria = new Criteria();
        $criteria->add(QubitRelation::OBJECT_ID, $this->id);
        $criteria->add(QubitRelation::SUBJECT_ID, $physicalObjectId);

        return QubitRelation::getOne($criteria);
    }

    /**
     * Get all physical objects related to this object.
     */
    public function getPhysicalObjects()
    {
        return QubitRelation::getRelatedSubjectsByObjectId('QubitPhysicalObject', $this->id, ['typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID]);
    }

    /**
     * Delete this resource and its descendants from bottom to top, updating the
     * nested set values only at the end and doing it within a transaction.
     *
     * @return int number of resources deleted
     */
    public function deleteFullHierarchy()
    {
        $allowedModels = ['QubitInformationObject', 'QubitTerm'];

        if (!in_array(get_class($this), $allowedModels)) {
            throw new sfException(
                'deleteFullHierarchy() can only be called from QubitInformationObject and QubitTerm.'
            );
        }

        $n = 0;

        foreach ($this->descendants->andSelf()->orderBy('rgt') as $item) {
            // Avoid nested set update until the last deletion:
            // The queries used to update the nested may be time expensive as
            // they update all the resources above the deleted one, including
            // those outside the deleted tree and those that are inside and will
            // be deleted after. When the `deleteFromNestedSet` function is called,
            // the delta used to update the nested set values is calculated from
            // the resource's RGT - LFT difference. Running the deletion in a
            // transaction, the nested set can be updated only once at the end.
            if ($this->id !== $item->id) {
                $item->disableNestedSetUpdating = true;
            }

            $item->delete();
            ++$n;
        }

        return $n;
    }

    protected function insert($connection = null)
    {
        parent::insert($connection);

        self::insertSlug($connection);

        return $this;
    }

    protected function checkIfSlugIsReserved()
    {
        // Check if slug is used by a plugin that may not be enabled yet
        if (in_array($this->slug, ['api', 'sword'])) {
            return true;
        }

        return self::actionExistsForUrl($this->slug);
    }
}
