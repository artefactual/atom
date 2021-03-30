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
 * Manage actors in search index.
 *
 * @author     MJ Suhonos <mj@suhonos.ca>
 */
class arElasticSearchActorPdo
{
    public $i18ns;

    protected $data = [];

    protected static $conn;
    protected static $lookups;
    protected static $statements;
    protected static $converseTermIds;

    /**
     * METHODS.
     *
     * @param mixed $id
     * @param mixed $options
     */
    public function __construct($id, $options = [])
    {
        if (isset($options['conn'])) {
            self::$conn = $options['conn'];
        }

        if (!isset(self::$conn)) {
            self::$conn = Propel::getConnection();
        }

        $this->loadData($id, $options);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __get($name)
    {
        if ('events' == $name && !isset($this->data[$name])) {
            $this->data[$name] = $this->getEvents();
        }

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function getMimeType()
    {
        if (!$this->__isset('digital_object_id')) {
            return;
        }

        if (null !== $digitalObject = QubitDigitalObject::getById($this->__get('digital_object_id'))) {
            return $digitalObject->getMimeType();
        }
    }

    public function getThumbnailPath()
    {
        if (!$this->__isset('digital_object_id')) {
            return;
        }

        $criteria = new Criteria();
        $criteria->add(QubitDigitalObject::PARENT_ID, $this->__get('digital_object_id'));
        $criteria->add(QubitDigitalObject::USAGE_ID, QubitTerm::THUMBNAIL_ID);

        if (null !== $thumbnail = QubitDigitalObject::getOne($criteria)) {
            return $thumbnail->getFullPath();
        }
    }

    public function getDigitalObjectAltText()
    {
        if (!$this->__isset('digital_object_id')) {
            return;
        }

        $criteria = new Criteria();
        $criteria->add(QubitDigitalObject::PARENT_ID, $this->__get('digital_object_id'));

        if (null !== $do = QubitDigitalObject::getOne($criteria)) {
            return $do->getDigitalObjectAltText();
        }
    }

    public function serialize()
    {
        $serialized = [];

        $serialized['id'] = $this->id;
        $serialized['slug'] = $this->slug;

        $serialized['entityTypeId'] = $this->entity_type_id;
        $serialized['hasDigitalObject'] = !is_null($this->media_type_id);

        $serialized['descriptionIdentifier'] = $this->description_identifier;
        $serialized['corporateBodyIdentifiers'] = $this->corporate_body_identifiers;

        // Add other names, parallel names, and standardized names
        $serialized += $this->serializeAltNames();

        if (false !== $maintainingRepositoryId = $this->getMaintainingRepositoryId()) {
            $serialized['maintainingRepositoryId'] = (int) $maintainingRepositoryId;
        }

        foreach ($this->getOccupations() as $occupation) {
            $occupationArray = [];

            $i18nFields = arElasticSearchModelBase::serializeI18ns(
                $occupation->term_id,
                ['QubitTerm'],
                ['fields' => ['name']]
            );

            if (isset($occupation->note_id)) {
                $i18nFields = arElasticSearchModelBase::serializeI18ns(
                    $occupation->note_id,
                    ['QubitNote'],
                    ['fields' => ['content'], 'merge' => $i18nFields]
                );
            }

            $occupationArray['id'] = $occupation->term_id;
            $occupationArray['i18n'] = $i18nFields;

            $serialized['occupations'][] = $occupationArray;
        }

        // Related terms
        $relatedTerms = arElasticSearchModelBase::getRelatedTerms(
            $this->id,
            [QubitTaxonomy::PLACE_ID, QubitTaxonomy::SUBJECT_ID]
        );

        // Related objects
        $serialized['actorRelations'] = self::serializeObjectRelations($this->id);
        $serialized['actorDirectRelationTypes'] = self::serializeObjectDirectRelationTypes($this->id, $serialized['actorRelations']);

        // Places
        if (isset($relatedTerms[QubitTaxonomy::PLACE_ID])) {
            $serialized['directPlaces'] = $relatedTerms[QubitTaxonomy::PLACE_ID];
            $extendedPlaceIds = arElasticSearchModelBase::extendRelatedTerms(
                $relatedTerms[QubitTaxonomy::PLACE_ID]
            );

            foreach ($extendedPlaceIds as $id) {
                $node = new arElasticSearchTermPdo($id);
                $serialized['places'][] = $node->serialize();
            }
        }

        // Subjects
        if (isset($relatedTerms[QubitTaxonomy::SUBJECT_ID])) {
            $serialized['directSubjects'] = $relatedTerms[QubitTaxonomy::SUBJECT_ID];
            $extendedSubjectIds = arElasticSearchModelBase::extendRelatedTerms(
                $relatedTerms[QubitTaxonomy::SUBJECT_ID]
            );

            foreach ($extendedSubjectIds as $id) {
                $node = new arElasticSearchTermPdo($id);
                $serialized['subjects'][] = $node->serialize();
            }
        }

        // Maintenance notes
        $sql = 'SELECT id, source_culture FROM '.QubitNote::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
        foreach (QubitPdo::fetchAll($sql, [$this->id, QubitTerm::MAINTENANCE_NOTE_ID]) as $item) {
            $serialized['maintenanceNotes'][] = arElasticSearchNote::serialize($item);
        }

        // Media
        if ($this->media_type_id) {
            $serialized['digitalObject']['mediaTypeId'] = $this->media_type_id;
            $serialized['digitalObject']['usageId'] = $this->usage_id;
            $serialized['digitalObject']['filename'] = $this->filename;
            $serialized['digitalObject']['thumbnailPath'] = $this->getThumbnailPath();
            $serialized['digitalObject']['digitalObjectAltText'] = $this->getDigitalObjectAltText();

            $serialized['hasDigitalObject'] = true;
        } else {
            $serialized['hasDigitalObject'] = false;
        }

        $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($this->created_at);
        $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($this->updated_at);

        $serialized['sourceCulture'] = $this->source_culture;
        $serialized['i18n'] = arElasticSearchModelBase::serializeI18ns($this->id, ['QubitActor']);

        return $serialized;
    }

    public function serializeAltNames()
    {
        $serialized = [];

        $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
        foreach (QubitPdo::fetchAll($sql, [$this->id, QubitTerm::OTHER_FORM_OF_NAME_ID]) as $item) {
            $serialized['otherNames'][] = arElasticSearchOtherName::serialize($item);
        }

        $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
        foreach (QubitPdo::fetchAll($sql, [$this->id, QubitTerm::PARALLEL_FORM_OF_NAME_ID]) as $item) {
            $serialized['parallelNames'][] = arElasticSearchOtherName::serialize($item);
        }

        $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
        foreach (QubitPdo::fetchAll($sql, [$this->id, QubitTerm::STANDARDIZED_FORM_OF_NAME_ID]) as $item) {
            $serialized['standardizedNames'][] = arElasticSearchOtherName::serialize($item);
        }

        return $serialized;
    }

    public static function serializeObjectDirectRelationTypes($actorId, $relationData)
    {
        $relationTypeIds = [];

        // Cycle through each relation an actor's involved in
        foreach ($relationData as $relation) {
            $typeId = $relation['typeId'];

            if ($relation['objectId'] == $actorId) {
                // Add type ID to array if it hasn't already been added
                if (!in_array($typeId, $relationTypeIds)) {
                    $relationTypeIds[] = $typeId;
                }
            } else {
                // If actor is the subject of the relation then look up the converse of the type
                if (isset(self::$converseTermIds[$typeId])) {
                    // Get cached type ID
                    $converseTermId = self::$converseTermIds[$typeId];
                } else {
                    // Look up converse term, if any
                    $sql = 'SELECT IF(object_id=?, subject_id, object_id) AS converse_id
                        FROM relation
                        WHERE (object_id=? or subject_id=?)
                        AND type_id=?';

                    $result = QubitPdo::fetchColumn($sql, [$typeId, $typeId, $typeId, QubitTerm::CONVERSE_TERM_ID]);

                    $converseTermId = !empty($result) ? $result : $typeId;

                    // Cache result
                    self::$converseTermIds[$typeId] = $converseTermId;
                }

                // Add type ID to array if it hasn't already been added
                if (!in_array($converseTermId, $relationTypeIds)) {
                    $relationTypeIds[] = $converseTermId;
                }
            }
        }

        return $relationTypeIds;
    }

    public static function serializeObjectRelations($actorId)
    {
        $sql = 'SELECT r.object_id AS objectId, r.subject_id AS subjectId, r.type_id AS typeId
            FROM '.QubitRelation::TABLE_NAME.' r
            INNER JOIN '.QubitTerm::TABLE_NAME.' t ON r.type_id=t.id
            WHERE t.taxonomy_id='.QubitTaxonomy::ACTOR_RELATION_TYPE_ID.'
            AND object_id=? OR r.subject_id=?';

        return QubitPdo::fetchAll($sql, [$actorId, $actorId], ['fetchMode' => PDO::FETCH_ASSOC]);
    }

    protected function loadData($id)
    {
        if (!isset(self::$statements['actor'])) {
            $sql = 'SELECT
                actor.*,
                slug.slug,
                object.created_at,
                object.updated_at,
                do.id as digital_object_id,
                do.media_type_id as media_type_id,
                do.usage_id as usage_id,
                do.name as filename
                FROM '.QubitActor::TABLE_NAME.' actor
                JOIN '.QubitSlug::TABLE_NAME.' slug
                ON actor.id = slug.object_id
                JOIN '.QubitObject::TABLE_NAME.' object
                ON actor.id = object.id
                LEFT JOIN '.QubitDigitalObject::TABLE_NAME.' do
                ON actor.id = do.object_id
                WHERE actor.id = :id';

            self::$statements['actor'] = self::$conn->prepare($sql);
        }

        // Do select
        self::$statements['actor']->execute([':id' => $id]);

        // Get first result
        $this->data = self::$statements['actor']->fetch(PDO::FETCH_ASSOC);

        if (false === $this->data) {
            throw new sfException("Couldn't find actor (id: {$id})");
        }

        self::$statements['actor']->closeCursor();

        return $this;
    }

    protected function getMaintainingRepositoryId()
    {
        if (!isset(self::$statements['maintainingRepository'])) {
            $sql = 'SELECT rel.subject_id';
            $sql .= ' FROM '.QubitRelation::TABLE_NAME.' rel';
            $sql .= ' WHERE rel.object_id = :object_id';
            $sql .= '   AND rel.type_id = :type_id';

            self::$statements['maintainingRepository'] = self::$conn->prepare($sql);
        }

        self::$statements['maintainingRepository']->execute([
            ':object_id' => $this->id,
            ':type_id' => QubitTerm::MAINTAINING_REPOSITORY_RELATION_ID,
        ]);

        return self::$statements['maintainingRepository']->fetchColumn();
    }

    protected function getOccupations()
    {
        if (!isset(self::$statements['occupations'])) {
            $sql = 'SELECT term.id as term_id, note.id as note_id';
            $sql .= ' FROM '.QubitObjectTermRelation::TABLE_NAME.' rel';
            $sql .= ' JOIN '.QubitTerm::TABLE_NAME.' term
                ON rel.term_id = term.id';
            $sql .= ' LEFT JOIN '.QubitNote::TABLE_NAME.' note
                ON rel.id = note.object_id
                AND note.type_id = :type_id';
            $sql .= ' WHERE rel.object_id = :object_id';
            $sql .= ' AND term.taxonomy_id = :taxonomy_id';

            self::$statements['occupations'] = self::$conn->prepare($sql);
        }

        self::$statements['occupations']->execute([
            ':type_id' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID,
            ':object_id' => $this->id,
            ':taxonomy_id' => QubitTaxonomy::ACTOR_OCCUPATION_ID,
        ]);

        return self::$statements['occupations']->fetchAll(PDO::FETCH_OBJ);
    }

    protected function getProperty($name)
    {
        $sql = 'SELECT prop.id, prop.source_culture';
        $sql .= ' FROM '.QubitProperty::TABLE_NAME.' prop';
        $sql .= ' WHERE prop.object_id = ? AND prop.name = ?';

        self::$statements['property'] = self::$conn->prepare($sql);
        self::$statements['property']->execute([$this->__get('id'), $name]);

        return self::$statements['property']->fetch(PDO::FETCH_OBJ);
    }
}
