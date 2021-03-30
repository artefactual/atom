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

class arElasticSearchAccession extends arElasticSearchModelBase
{
    protected static $statements;

    public function load()
    {
        $accessionIds = QubitPdo::fetchAll(
            'SELECT id FROM '.QubitAccession::TABLE_NAME,
            [],
            ['fetchMode' => PDO::FETCH_COLUMN]
        );

        $this->count = count($accessionIds);

        return $accessionIds;
    }

    public function populate()
    {
        $errors = [];

        foreach ($this->load() as $key => $id) {
            try {
                $data = self::serialize($id);

                $this->search->addDocument($data, 'QubitAccession');

                $this->logEntry($data['identifier'], $key + 1);
            } catch (sfException $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }

    public static function update($object)
    {
        $data = self::serialize($object->id);

        QubitSearch::getInstance()->addDocument($data, 'QubitAccession');

        return true;
    }

    public static function getAccessionEvents($accessionId)
    {
        if (!isset(self::$statements['event'])) {
            $sql = 'SELECT
                event.id,
                event.type_id,
                event.date,
                note.id AS note_id
                FROM '.QubitAccessionEvent::TABLE_NAME.' event
                INNER JOIN '.QubitNote::TABLE_NAME.' note
                ON (event.id=note.object_id AND note.type_id=?)
                WHERE event.accession_id = ?';

            self::$statements['event'] = self::$conn->prepare($sql);
        }

        self::$statements['event']->execute([QubitTerm::ACCESSION_EVENT_NOTE_ID, $accessionId]);

        $events = [];
        foreach (self::$statements['event']->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $event = ['id' => $item['id']];

            $event['date'] = arElasticSearchPluginUtil::normalizeDateWithoutMonthOrDay($item['date']);
            $event['dateString'] = Qubit::renderDate($item['date']);

            // Serialize type term
            if ($item['type_id']) {
                $event['type'] = ['i18n' => arElasticSearchModelBase::serializeI18ns(
                    $item['type_id'],
                    ['QubitTerm'],
                    ['fields' => ['name']]
                )];
            }

            // Serialize note
            if ($item['note_id']) {
                $event['notes'] = ['i18n' => arElasticSearchModelBase::serializeI18ns(
                    $item['note_id'],
                    ['QubitNote'],
                    ['fields' => ['content']]
                )];
            }

            // Serialize accession event i18n data
            $event['i18n'] = arElasticSearchModelBase::serializeI18ns($item['id'], ['QubitAccessionEvent']);

            $events[] = $event;
        }

        return $events;
    }

    private static function serialize($id)
    {
        if (!isset(self::$conn)) {
            self::$conn = Propel::getConnection();
        }

        if (!isset(self::$statements['accession'])) {
            $sql = 'SELECT acc.*, slug.slug
                FROM '.QubitAccession::TABLE_NAME.' acc
                JOIN '.QubitSlug::TABLE_NAME.' slug ON acc.id = slug.object_id
                WHERE acc.id = :id';

            self::$statements['accession'] = self::$conn->prepare($sql);
        }

        self::$statements['accession']->execute([':id' => $id]);
        $data = self::$statements['accession']->fetch(PDO::FETCH_ASSOC);

        if (false === $data) {
            throw new sfException("Couldn't find accession (id: {$id})");
        }

        $serialized = [];
        $serialized['id'] = $id;
        $serialized['slug'] = $data['slug'];
        $serialized['identifier'] = $data['identifier'];
        $serialized['date'] = arElasticSearchPluginUtil::convertDate($data['date']);
        $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($data['created_at']);
        $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($data['updated_at']);
        $serialized['sourceCulture'] = $data['source_culture'];
        $serialized['i18n'] = self::serializeI18ns($id, ['QubitAccession']);

        $sql = 'SELECT o.id, o.source_culture, o.type_id FROM '.QubitOtherName::TABLE_NAME." o \r
            INNER JOIN ".QubitTerm::TABLE_NAME." t ON o.type_id=t.id \r
            WHERE o.object_id = ? AND t.taxonomy_id= ?";
        $params = [$id, QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID];

        foreach (QubitPdo::fetchAll($sql, $params) as $item) {
            $serialized['alternativeIdentifiers'][] = arElasticSearchOtherName::serialize($item);
        }

        foreach (QubitRelation::getRelationsBySubjectId($id, ['typeId' => QubitTerm::DONOR_ID]) as $item) {
            $serialized['donors'][] = arElasticSearchDonor::serialize($item->object);
        }

        foreach (QubitRelation::getRelationsByObjectId($id, ['typeId' => QubitTerm::CREATION_ID]) as $item) {
            $node = new arElasticSearchActorPdo($item->subject->id);
            $serialized['creators'][] = $node->serialize();
        }

        $serialized['accessionEvents'] = self::getAccessionEvents($id);

        return $serialized;
    }
}
