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
 * Manage information objects in search index.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class arElasticSearchInformationObjectPdo
{
    public $ancestors;
    public $doc;
    public $repository;
    public $sourceCulture;
    public $creators = [];
    public $inheritedCreators = [];

    protected $data = [];
    protected $events;

    protected static $conn;
    protected static $statements;

    public function __construct($id, $options = [])
    {
        if (isset($options['conn'])) {
            self::$conn = $options['conn'];
        }

        if (!isset(self::$conn)) {
            self::$conn = Propel::getConnection();
        }

        $this->loadData($id, $options);

        // Get inherited ancestors
        if (isset($options['ancestors'])) {
            $this->ancestors = $options['ancestors'];
        }

        // Get inherited repository, unless a repository is set at current level
        if (isset($options['repository']) && !$this->__isset('repository_id')) {
            $this->repository = $options['repository'];
        }

        // Get inherited creators
        if (isset($options['inheritedCreators'])) {
            $this->inheritedCreators = $options['inheritedCreators'];
        }

        // Get creators
        $this->creators = $this->getActors(['typeId' => QubitTerm::CREATION_ID]);

        // Ignore inherited creators if there are directly related creators
        if (!empty($this->creators)) {
            $this->inheritedCreators = [];
        }
        // Otherwise, get them from the options
        elseif (isset($options['inheritedCreators'])) {
            $this->inheritedCreators = $options['inheritedCreators'];
        }
        // Or from the closest ancestor
        else {
            $this->inheritedCreators = $this->getClosestCreators();
        }
    }

    public function __isset($name)
    {
        if ('events' == $name) {
            return isset($this->events);
        }

        return isset($this->data[$name]);
    }

    public function __get($name)
    {
        if ('events' == $name) {
            return $this->events;
        }

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }

    public function __set($name, $value)
    {
        if ('events' == $name) {
            $this->events = $value;

            return;
        }

        $this->data[$name] = $value;
    }

    /**
     * Return an array of ancestors.
     *
     * @return array of ancestors
     */
    public function getAncestors()
    {
        if (!isset($this->ancestors) && isset($this->parent_id)) {
            $sql = 'WITH RECURSIVE cte AS
                (
                    SELECT io1.id, io1.parent_id, io1.identifier, io1.repository_id, 1 as lev
                    FROM information_object io1 WHERE io1.id = ?
                    UNION ALL
                    SELECT io2.id, io2.parent_id, io2.identifier, io2.repository_id, cte.lev + 1
                    FROM information_object io2 JOIN cte ON cte.parent_id=io2 .id
                )
                SELECT id, identifier, repository_id FROM cte ORDER BY lev DESC';

            $this->ancestors = QubitPdo::fetchAll(
                $sql,
                [$this->parent_id],
                ['fetchMode' => PDO::FETCH_ASSOC]
            );
        }

        if (!isset($this->ancestors) || empty($this->ancestors)) {
            throw new sfException(sprintf("%s: Couldn't find ancestors, please make sure parent_id values are correct", get_class($this)));
        }

        return $this->ancestors;
    }

    /**
     * Return an array of children.
     *
     * @return array of children
     */
    public function getChildren()
    {
        if (!isset($this->children)) {
            // Find children
            $sql = 'SELECT node.id';
            $sql .= ' FROM '.QubitInformationObject::TABLE_NAME.' node';
            $sql .= ' WHERE node.parent_id = :id';
            $sql .= ' ORDER BY lft';

            $this->children = QubitPdo::fetchAll($sql, [':id' => $this->id]);
        }

        return $this->children;
    }

    /**
     * Return the closest repository.
     *
     * @return QubitRepository
     */
    public function getRepository()
    {
        if (!isset($this->repository)) {
            if ($this->__isset('repository_id')) {
                $this->repository = QubitRepository::getById($this->__get('repository_id'));
            } else {
                foreach (array_reverse($this->getAncestors()) as $item) {
                    if (isset($item['repository_id'])) {
                        $this->repository = QubitRepository::getById($item['repository_id']);

                        break;
                    }
                }
            }
        }

        return $this->repository;
    }

    public function getClosestCreators()
    {
        $inheritedCreators = [];

        if (!isset(self::$statements['inheritedCreators'])) {
            $sql = 'SELECT event.actor_id as id';
            $sql .= ' FROM '.QubitEvent::TABLE_NAME.' event';
            $sql .= ' WHERE event.actor_id IS NOT NULL';
            $sql .= ' AND event.object_id = ?';
            $sql .= ' AND event.type_id = ?';

            self::$statements['inheritedCreators'] = self::$conn->prepare($sql);
        }

        foreach (array_reverse($this->getAncestors()) as $ancestor) {
            self::$statements['inheritedCreators']->execute([$ancestor['id'], QubitTerm::CREATION_ID]);

            foreach (self::$statements['inheritedCreators']->fetchAll(PDO::FETCH_OBJ) as $creator) {
                $inheritedCreators[] = $creator;
            }

            if (!empty($inheritedCreators)) {
                break;
            }
        }

        return $inheritedCreators;
    }

    /**
     * Get full reference code, with optional country code and repository prefixes as well.
     *
     * @param bool $includeRepoAndCountry Whether or not to prepend country code and repository identifier
     *
     * @return string The full reference code
     */
    public function getReferenceCode($includeRepoAndCountry = true)
    {
        if (null == $this->__get('identifier')) {
            return;
        }

        $refcode = '';
        $this->repository = $this->getRepository();

        if (isset($this->repository) && $includeRepoAndCountry) {
            if (null != $cc = $this->repository->getCountryCode(['culture' => $this->__get('culture')])) {
                $refcode .= $cc.' ';
            }

            if (isset($this->repository->identifier)) {
                $refcode .= $this->repository->identifier.' ';
            }
        }

        $identifiers = [];

        foreach ($this->getAncestors() as $item) {
            if (isset($item['identifier'])) {
                $identifiers[] = $item['identifier'];
            }
        }

        if (isset($this->identifier)) {
            $identifiers[] = $this->identifier;
        }

        $refcode .= implode(sfConfig::get('app_separator_character', '-'), $identifiers);

        return $refcode;
    }

    public function getActors($options = [])
    {
        $actors = [];

        if (!isset(self::$statements['actor'])) {
            $sql = 'SELECT actor.id, actor.entity_type_id, slug.slug';
            $sql .= ' FROM '.QubitActor::TABLE_NAME.' actor';
            $sql .= ' JOIN '.QubitSlug::TABLE_NAME.' slug
                ON actor.id = slug.object_id';
            $sql .= ' WHERE actor.id = ?';

            self::$statements['actor'] = self::$conn->prepare($sql);
        }

        if (!empty($this->events)) {
            foreach ($this->events as $item) {
                if (isset($item->actor_id)) {
                    // Filter by type
                    if (isset($options['typeId']) && $options['typeId'] != $item->type_id) {
                        continue;
                    }

                    self::$statements['actor']->execute([$item->actor_id]);

                    if ($actor = self::$statements['actor']->fetch(PDO::FETCH_OBJ)) {
                        $actors[] = $actor;
                    }
                }
            }
        }

        return $actors;
    }

    public function getNameAccessPoints()
    {
        $names = [];

        // Subject relations
        if (!isset(self::$statements['actorRelation'])) {
            $sql = 'SELECT actor.id';
            $sql .= ' FROM '.QubitActor::TABLE_NAME.' actor';
            $sql .= ' JOIN '.QubitRelation::TABLE_NAME.' relation
                ON actor.id = relation.object_id';
            $sql .= ' WHERE relation.subject_id = :resourceId
                AND relation.type_id = :typeId';

            self::$statements['actorRelation'] = self::$conn->prepare($sql);
        }

        self::$statements['actorRelation']->execute([
            ':resourceId' => $this->__get('id'),
            ':typeId' => QubitTerm::NAME_ACCESS_POINT_ID,
        ]);

        foreach (self::$statements['actorRelation']->fetchAll(PDO::FETCH_OBJ) as $item) {
            $names[$item->id] = $item;
        }

        // Add actors in related, non-creation events
        foreach ($this->events as $event) {
            if (isset($event->actor_id) && QubitTerm::CREATION_ID != $event->type_id) {
                $actor = new stdClass();
                $actor->id = $event->actor_id;
                $names[$actor->id] = $actor;
            }
        }

        return $names;
    }

    public function getNotesByType($typeId)
    {
        $sql = 'SELECT
            id, source_culture
            FROM '.QubitNote::TABLE_NAME.
            ' WHERE object_id = ? AND type_id = ?';

        return QubitPdo::fetchAll($sql, [$this->__get('id'), $typeId]);
    }

    public function getTermIdByNameAndTaxonomy($name, $taxonomyId, $culture = 'en')
    {
        $sql = 'SELECT t.id
            FROM term t
            LEFT JOIN term_i18n ti
            ON t.id=ti.id
            WHERE t.taxonomy_id=? AND ti.name=? AND ti.culture=?';

        return QubitPdo::fetchColumn($sql, [$taxonomyId, $name, $culture]);
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

    public function getRights()
    {
        if (!isset(self::$statements['rights'])) {
            $sql = 'SELECT rights.*, rightsi18n.*';
            $sql .= ' FROM '.QubitRights::TABLE_NAME.' rights';
            $sql .= ' JOIN '.QubitRightsI18n::TABLE_NAME.' rightsi18n
                ON rights.id = rightsi18n.id';
            $sql .= ' JOIN '.QubitRelation::TABLE_NAME.' rel
                ON rights.id = rel.object_id';
            $sql .= ' WHERE rel.subject_id = ?';
            $sql .= ' AND rel.type_id = '.QubitTerm::RIGHT_ID;

            self::$statements['rights'] = self::$conn->prepare($sql);
        }

        self::$statements['rights']->execute([
            $this->__get('id'),
        ]);

        return self::$statements['rights']->fetchAll(PDO::FETCH_CLASS);
    }

    public function getGrantedRights()
    {
        if (!isset(self::$statements['grantedRights'])) {
            $sql = 'SELECT gr.*';
            $sql .= ' FROM '.QubitGrantedRight::TABLE_NAME.' gr';
            $sql .= ' JOIN '.QubitRelation::TABLE_NAME.' rel
                ON gr.rights_id = rel.object_id';
            $sql .= ' WHERE rel.subject_id = ?';
            $sql .= ' AND rel.type_id = '.QubitTerm::RIGHT_ID;

            self::$statements['grantedRights'] = self::$conn->prepare($sql);
        }

        self::$statements['grantedRights']->execute([$this->__get('id')]);

        return self::$statements['grantedRights']->fetchAll(PDO::FETCH_CLASS);
    }

    /**
     * Get text transcript, if one exists.
     */
    public function getTranscript()
    {
        if (!$this->__isset('digital_object_id')) {
            return false;
        }

        if (!isset(self::$statements['transcript'])) {
            $sql = 'SELECT i18n.value
                FROM '.QubitProperty::TABLE_NAME.' property
                JOIN '.QubitPropertyI18n::TABLE_NAME.' i18n
                ON property.id = i18n.id
                WHERE property.name = "transcript"
                AND property.source_culture = i18n.culture
                AND property.object_id = ?';

            self::$statements['transcript'] = self::$conn->prepare($sql);
        }

        self::$statements['transcript']->execute([$this->__get('digital_object_id')]);

        return self::$statements['transcript']->fetchColumn();
    }

    /**
     * Get finding aid text transcript, if one exists.
     */
    public function getFindingAidTranscript()
    {
        if (!isset(self::$statements['findingAidTranscript'])) {
            $sql = 'SELECT i18n.value
                FROM '.QubitProperty::TABLE_NAME.' property
                JOIN '.QubitPropertyI18n::TABLE_NAME.' i18n
                ON property.id = i18n.id
                WHERE property.name = "findingAidTranscript"
                AND property.source_culture = i18n.culture
                AND property.object_id = ?';

            self::$statements['findingAidTranscript'] = self::$conn->prepare($sql);
        }

        self::$statements['findingAidTranscript']->execute([$this->__get('id')]);

        return self::$statements['findingAidTranscript']->fetchColumn();
    }

    /**
     * Get finding aid status.
     */
    public function getFindingAidStatus()
    {
        if (!isset(self::$statements['findingAidStatus'])) {
            $sql = 'SELECT i18n.value
                FROM '.QubitProperty::TABLE_NAME.' property
                JOIN '.QubitPropertyI18n::TABLE_NAME.' i18n
                ON property.id = i18n.id
                WHERE property.name = "findingAidStatus"
                AND property.source_culture = i18n.culture
                AND property.object_id = ?';

            self::$statements['findingAidStatus'] = self::$conn->prepare($sql);
        }

        self::$statements['findingAidStatus']->execute([$this->__get('id')]);

        return self::$statements['findingAidStatus']->fetchColumn();
    }

    public function serialize()
    {
        $serialized = [];

        // Add default null values to allow document updates using partial data.
        // To remove fields from the document is required the use of scripts, which
        // requires global configuration changes or deployments headaches. If there
        // is not a default value set in the mapping configuration, null values work
        // the same as missing fields in almost every case and allow us to 'remove'
        // fields without using scripts in partial updates.
        $serialized['findingAid'] = [
            'transcript' => null,
            'status' => null,
        ];

        $serialized['id'] = $this->id;
        $serialized['slug'] = $this->slug;
        $serialized['parentId'] = $this->parent_id;
        $serialized['identifier'] = $this->identifier;
        $serialized['referenceCode'] = $this->getReferenceCode();
        $serialized['referenceCodeWithoutCountryAndRepo'] = $this->getReferenceCode(false);
        $serialized['levelOfDescriptionId'] = $this->level_of_description_id;
        $serialized['lft'] = $this->lft;
        $serialized['publicationStatusId'] = $this->publication_status_id;

        // Alternative identifiers
        $alternativeIdentifiers = $this->getAlternativeIdentifiers();
        if (!empty($alternativeIdentifiers)) {
            $serialized['alternativeIdentifiers'] = $alternativeIdentifiers;
        }

        // NB: this will include the ROOT_ID
        $serialized['ancestors'] = array_column($this->getAncestors(), 'id');

        // NB: this should be an ordered array
        foreach ($this->getChildren() as $child) {
            $serialized['children'][] = $child->id;
        }

        // Copyright status
        $statusId = null;
        foreach ($this->getRights() as $item) {
            if (isset($item->copyright_status_id)) {
                $statusId = $item->copyright_status_id;

                break;
            }
        }
        if (null !== $statusId) {
            $serialized['copyrightStatusId'] = $statusId;
        }

        // Make sure that media_type_id gets a value in case that one was not
        // assigned, which seems to be a possibility when using the offline usage.
        if (null === $this->media_type_id && QubitTerm::OFFLINE_ID == $this->usage_id) {
            $this->media_type_id = QubitTerm::OTHER_ID;
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

        // Dates
        foreach ($this->events as $event) {
            $serialized['dates'][] = arElasticSearchEvent::serialize($event);

            // The dates indexed above are nested objects and that complicates sorting.
            // Additionally, we only show the first populated dates on the search
            // results. Indexing the first populated dates on different fields makes
            // sorting easier and more intuitive.
            if (isset($serialized['startDateSort']) || isset($serialized['endDateSort'])) {
                continue;
            }

            if (!empty($event->start_date)) {
                $serialized['startDateSort'] = arElasticSearchPluginUtil::normalizeDateWithoutMonthOrDay($event->start_date);
            }

            if (!empty($event->end_date)) {
                $serialized['endDateSort'] = arElasticSearchPluginUtil::normalizeDateWithoutMonthOrDay($event->end_date, true);
            }
        }

        // Transcript
        if (false !== $transcript = $this->getTranscript()) {
            $serialized['transcript'] = $transcript;
        }

        // Finding aid transcript
        if (false !== $findingAidTranscript = $this->getFindingAidTranscript()) {
            $serialized['findingAid']['transcript'] = $findingAidTranscript;
        }

        // Finding aid status
        if (false !== $findingAidStatus = $this->getFindingAidStatus()) {
            $serialized['findingAid']['status'] = (int) $findingAidStatus;
        }

        // Repository
        if (null !== $repository = $this->getRepository()) {
            $serialized['repository']['id'] = $this->repository->id;
            $serialized['repository']['slug'] = $this->repository->slug;
            $serialized['repository']['identifier'] = $this->repository->identifier;

            $serialized['repository']['i18n'] = arElasticSearchModelBase::serializeI18ns(
                $repository->id,
                ['QubitActor'],
                ['fields' => ['authorized_form_of_name']]
            );
        }

        // Related terms
        $relatedTerms = arElasticSearchModelBase::getRelatedTerms(
            $this->id,
            [
                QubitTaxonomy::MATERIAL_TYPE_ID,
                QubitTaxonomy::PLACE_ID,
                QubitTaxonomy::SUBJECT_ID,
                QubitTaxonomy::GENRE_ID,
            ]
        );

        // Material types
        if (isset($relatedTerms[QubitTaxonomy::MATERIAL_TYPE_ID])) {
            $serialized['materialTypeId'] = $relatedTerms[QubitTaxonomy::MATERIAL_TYPE_ID];
        }

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

        // Genres
        if (isset($relatedTerms[QubitTaxonomy::GENRE_ID])) {
            $serialized['directGenres'] = $relatedTerms[QubitTaxonomy::GENRE_ID];
            $extendedGenreIds = arElasticSearchModelBase::extendRelatedTerms(
                $relatedTerms[QubitTaxonomy::GENRE_ID]
            );

            foreach ($extendedGenreIds as $id) {
                $node = new arElasticSearchTermPdo($id);
                $serialized['genres'][] = $node->serialize();
            }
        }

        // Name access points
        foreach ($this->getNameAccessPoints() as $item) {
            $node = new arElasticSearchActorPdo($item->id);

            $names = [
                'id' => $node->id,
                'i18n' => arElasticSearchModelBase::serializeI18ns(
                    $node->id,
                    ['QubitActor'],
                    ['fields' => ['authorized_form_of_name']]
                ),
            ];

            // Add other names, parallel names, and standardized names
            $names += $node->serializeAltNames();

            $serialized['names'][] = $names;
        }

        // Creators
        foreach ($this->creators as $item) {
            $node = new arElasticSearchActorPdo($item->id);
            $serialized['creators'][] = $node->serialize();
        }

        // Inherited creators
        foreach ($this->inheritedCreators as $item) {
            $node = new arElasticSearchActorPdo($item->id);
            $serialized['inheritedCreators'][] = $node->serialize();
        }

        // Physical objects
        foreach ($this->getPhysicalObjects() as $item) {
            $serialized['physicalObjects'][] = arElasticSearchPhysicalObject::serialize($item);
        }

        // Notes
        foreach ($this->getNotesByType(QubitTerm::GENERAL_NOTE_ID) as $item) {
            $serialized['generalNotes'][] = arElasticSearchNote::serialize($item);
        }

        // PREMIS data
        if (null !== $premisData = arElasticSearchPluginUtil::getPremisData($this->id, self::$conn)) {
            $serialized['metsData'] = $premisData;
        }

        if (null !== $termId = $this->getTermIdByNameAndTaxonomy('Alpha-numeric designations', QubitTaxonomy::RAD_NOTE_ID)) {
            foreach ($this->getNotesByType($termId) as $item) {
                $serialized['alphaNumericNotes'][] = arElasticSearchNote::serialize($item);
            }
        }

        if (null !== $termId = $this->getTermIdByNameAndTaxonomy('Conservation', QubitTaxonomy::RAD_NOTE_ID)) {
            foreach ($this->getNotesByType($termId) as $item) {
                $serialized['conservationNotes'][] = arElasticSearchNote::serialize($item);
            }
        }

        if (null !== $termId = $this->getTermIdByNameAndTaxonomy('Physical description', QubitTaxonomy::RAD_NOTE_ID)) {
            foreach ($this->getNotesByType($termId) as $item) {
                $serialized['physicalDescriptionNotes'][] = arElasticSearchNote::serialize($item);
            }
        }

        if (null !== $termId = $this->getTermIdByNameAndTaxonomy('Continuation of title', QubitTaxonomy::RAD_TITLE_NOTE_ID)) {
            foreach ($this->getNotesByType($termId) as $item) {
                $serialized['continuationOfTitleNotes'][] = arElasticSearchNote::serialize($item);
            }
        }

        if (null !== $termId = $this->getTermIdByNameAndTaxonomy("Archivist's note", QubitTaxonomy::NOTE_TYPE_ID)) {
            foreach ($this->getNotesByType($termId) as $item) {
                $serialized['archivistsNotes'][] = arElasticSearchNote::serialize($item);
            }
        }

        if (null !== $termId = $this->getTermIdByNameAndTaxonomy('Publication note', QubitTaxonomy::NOTE_TYPE_ID)) {
            foreach ($this->getNotesByType($termId) as $item) {
                $serialized['publicationNotes'][] = arElasticSearchNote::serialize($item);
            }
        }

        if (false !== $item = $this->getProperty('titleStatementOfResponsibility')) {
            $serialized['titleStatementOfResponsibility'] = arElasticSearchProperty::serialize($item);
        }

        // Aips
        foreach ($this->getAips() as $item) {
            $node = new arElasticSearchAipPdo($item->id);
            $serialized['aip'][] = $node->serialize();
        }

        $serialized['actRights'] = $this->getActRights();
        $serialized['basisRights'] = $this->getBasisRights();

        $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($this->created_at);
        $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($this->updated_at);

        $serialized['sourceCulture'] = $this->source_culture;
        $serialized['i18n'] = arElasticSearchModelBase::serializeI18ns($this->id, ['QubitInformationObject']);

        // Add "Part of" information if this isn't a top level description
        if (count($this->ancestors) > 1) {
            $collectionRootId = $this->ancestors[1]['id'];
            $rootSlug = QubitPdo::fetchColumn('SELECT slug FROM slug WHERE object_id=?', [$collectionRootId]);

            if (!$rootSlug) {
                throw new sfException("No slug found for information object {$collectionRootId}");
            }

            $rootSourceCulture = QubitPdo::fetchColumn(
                'SELECT source_culture FROM information_object WHERE id=?',
                [$collectionRootId]
            );
            if (!$rootSourceCulture) {
                throw new sfException("No source culture found for information object {$collectionRootId}");
            }

            $i18nFields = arElasticSearchModelBase::serializeI18ns(
                $collectionRootId,
                ['QubitInformationObject'],
                ['fields' => ['title']]
            );

            $serialized['partOf']['id'] = $collectionRootId;
            $serialized['partOf']['sourceCulture'] = $rootSourceCulture;
            $serialized['partOf']['slug'] = $rootSlug;
            $serialized['partOf']['i18n'] = $i18nFields;
        }

        return $serialized;
    }

    protected function loadData($id, $options = [])
    {
        if (!isset(self::$statements['informationObject'])) {
            $sql = 'SELECT
                io.*,
                obj.created_at,
                obj.updated_at,
                slug.slug,
                pubstat.status_id as publication_status_id,
                do.id as digital_object_id,
                do.media_type_id as media_type_id,
                do.usage_id as usage_id,
                do.name as filename
                FROM '.QubitInformationObject::TABLE_NAME.' io
                JOIN '.QubitObject::TABLE_NAME.' obj
                ON io.id = obj.id
                JOIN '.QubitSlug::TABLE_NAME.' slug
                ON io.id = slug.object_id
                JOIN '.QubitStatus::TABLE_NAME.' pubstat
                ON io.id = pubstat.object_id
                LEFT JOIN '.QubitDigitalObject::TABLE_NAME.' do
                ON io.id = do.object_id
                WHERE io.id = :id';

            self::$statements['informationObject'] = self::$conn->prepare($sql);
        }

        // Do select
        self::$statements['informationObject']->execute([':id' => $id]);

        // Get first result
        $this->data = self::$statements['informationObject']->fetch(PDO::FETCH_ASSOC);

        if (false === $this->data) {
            throw new sfException("Couldn't find information object (id: {$id})");
        }

        // Load event data
        $this->loadEvents();

        return $this;
    }

    protected function loadEvents()
    {
        if (!isset($this->events)) {
            $events = [];

            if (!isset(self::$statements['event'])) {
                $sql = 'SELECT
                    event.id,
                    event.start_date,
                    event.end_date,
                    event.actor_id,
                    event.type_id,
                    event.source_culture,
                    i18n.date,
                    i18n.culture';
                $sql .= ' FROM '.QubitEvent::TABLE_NAME.' event';
                $sql .= ' JOIN '.QubitEventI18n::TABLE_NAME.' i18n
                    ON event.id = i18n.id';
                $sql .= ' WHERE event.object_id = ?';

                self::$statements['event'] = self::$conn->prepare($sql);
            }

            self::$statements['event']->execute([$this->__get('id')]);

            foreach (self::$statements['event']->fetchAll() as $item) {
                if (!isset($events[$item['id']])) {
                    $event = new stdClass();
                    $event->id = $item['id'];
                    $event->start_date = $item['start_date'];
                    $event->end_date = $item['end_date'];
                    $event->actor_id = $item['actor_id'];
                    $event->type_id = $item['type_id'];
                    $event->source_culture = $item['source_culture'];

                    $events[$item['id']] = $event;
                }

                $events[$item['id']]->dates[$item['culture']] = $item['date'];
            }

            $this->events = $events;
        }

        return $this->events;
    }

    protected function getAlternativeIdentifiers()
    {
        // Find langs and scripts
        if (!isset(self::$statements['alternativeIdentifiers'])) {
            $sql = 'SELECT node.name, i18n.value';
            $sql .= ' FROM '.QubitProperty::TABLE_NAME.' node';
            $sql .= ' JOIN '.QubitPropertyI18n::TABLE_NAME.' i18n
                ON node.id = i18n.id';
            $sql .= ' WHERE node.source_culture = i18n.culture
                AND node.object_id = ?
                AND node.scope = ?';

            self::$statements['alternativeIdentifiers'] = self::$conn->prepare($sql);
        }

        self::$statements['alternativeIdentifiers']->execute([
            $this->__get('id'),
            'alternativeIdentifiers',
        ]);

        $alternativeIdentifiers = [];
        foreach (self::$statements['alternativeIdentifiers']->fetchAll() as $item) {
            $tmp = [];

            $tmp['label'] = $item['name'];
            $tmp['identifier'] = $item['value'];

            $alternativeIdentifiers[] = $tmp;
        }

        return $alternativeIdentifiers;
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

    protected function getAips()
    {
        $sql = 'SELECT aip.id';
        $sql .= ' FROM '.QubitAip::TABLE_NAME.' aip';
        $sql .= ' JOIN '.QubitRelation::TABLE_NAME.' relation
            ON aip.id = relation.subject_id';
        $sql .= ' WHERE relation.object_id = ?
            AND relation.type_id = ?';

        self::$statements['aips'] = self::$conn->prepare($sql);
        self::$statements['aips']->execute([$this->__get('id'), QubitTerm::AIP_RELATION_ID]);

        return self::$statements['aips']->fetchAll(PDO::FETCH_OBJ);
    }

    protected function getPhysicalObjects()
    {
        $sql = 'SELECT phys.id, phys.source_culture';
        $sql .= ' FROM '.QubitPhysicalObject::TABLE_NAME.' phys';
        $sql .= ' JOIN '.QubitRelation::TABLE_NAME.' relation
            ON phys.id = relation.subject_id';
        $sql .= ' WHERE relation.object_id = ?
            AND relation.type_id = ?';

        self::$statements['physicalObjects'] = self::$conn->prepare($sql);
        self::$statements['physicalObjects']->execute([$this->__get('id'), QubitTerm::HAS_PHYSICAL_OBJECT_ID]);

        return self::$statements['physicalObjects']->fetchAll(PDO::FETCH_OBJ);
    }

    private function getBasisRights()
    {
        $basisRights = [];

        foreach ($this->getRights() as $right) {
            $basisRight = [];

            $basisRight['startDate'] = arElasticSearchPluginUtil::normalizeDateWithoutMonthOrDay($right->start_date);
            $basisRight['endDate'] = arElasticSearchPluginUtil::normalizeDateWithoutMonthOrDay($right->end_date, true);
            $basisRight['rightsNote'] = $right->rights_note;
            $basisRight['licenseTerms'] = $right->license_terms;

            if ($right->rights_holder_id) {
                $basisRight['rightsHolder'] = QubitActor::getById($right->rights_holder_id)->getAuthorizedFormOfName();
            }

            if ($right->basis_id) {
                $basisRight['basis'] = QubitTerm::getById($right->basis_id)->getName();
            }

            if ($right->copyright_status_id) {
                $basisRight['copyrightStatus'] = QubitTerm::getById($right->copyright_status_id)->getName();
            }

            $basisRights[] = $basisRight;
        }

        return $basisRights;
    }

    private function getActRights()
    {
        $actRights = [];
        foreach ($this->getGrantedRights() as $grantedRight) {
            $actRight = [];

            if ($grantedRight->act_id) {
                $actRight['act'] = QubitTerm::getById($grantedRight->act_id)->getName();
            }

            $actRight['restriction'] = QubitGrantedRight::getRestrictionString($grantedRight->restriction);
            $actRight['startDate'] = arElasticSearchPluginUtil::normalizeDateWithoutMonthOrDay($grantedRight->start_date);
            $actRight['endDate'] = arElasticSearchPluginUtil::normalizeDateWithoutMonthOrDay($grantedRight->end_date, true);

            $actRights[] = $actRight;
        }

        return $actRights;
    }
}
