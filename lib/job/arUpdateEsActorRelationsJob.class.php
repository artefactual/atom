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
 * Updates actor document relationships in the Elasticsearch index.
 */
class arUpdateEsActorRelationsJob extends arBaseJob
{
    /**
     * @see arBaseJob::$requiredParameters
     */
    protected $extraRequiredParameters = ['actorIds'];

    public function runJob($parameters)
    {
        if (empty($parameters['actorIds'])) {
            $this->error($this->i18n->__('Called arUpdateEsActorRelationsJob without specifying what needs to be updated.'));

            return false;
        }

        $message = $this->i18n->__('Updating the relationships of %1 actor(s).', ['%1' => count($parameters['actorIds'])]);

        $this->job->addNoteText($message);
        $this->info($message);

        $count = 0;
        foreach ($parameters['actorIds'] as $id) {
            if (null === $object = QubitActor::getById($id)) {
                $this->info($this->i18n->__('Invalid actor id: %1', ['%1' => $id]));

                continue;
            }

            // Don't count invalid description ids
            ++$count;

            self::updateActorRelationships($object);
            $message = $this->i18n->__('Updated %1 actors(s).', ['%1' => $count]);

            // Minimize memory use in case we're dealing with a large number of information objects
            Qubit::clearClassCaches();

            // Status update every 100 descriptions
            if (0 == $count % 100) {
                $this->info($message);
            }
        }

        // Final status update, if total count is not a multiple of 100
        if (0 != $count % 100) {
            $this->info($message);
        }

        $this->job->setStatusCompleted();
        $this->job->save();

        return true;
    }

    public static function updateActorRelationships($actor)
    {
        $relationData = arElasticSearchActorPdo::serializeObjectRelations($actor->id);
        $directRelationTypes = arElasticSearchActorPdo::serializeObjectDirectRelationTypes($actor->id, $relationData);

        QubitSearch::getInstance()->partialUpdate(
            $actor,
            ['actorRelations' => $relationData, 'actorDirectRelationTypes' => $directRelationTypes]
        );
    }

    public static function previousRelationActorIds($actorId)
    {
        try {
            // Get actor's previously indexed relations from Elasticsearch
            $doc = QubitSearch::getInstance()->index->getType('QubitActor')->getDocument($actorId);

            return self::uniqueIdsFromRelationData($doc->getData()['actorRelations']);
        } catch (\Elastica\Exception\NotFoundException $e) {
            return [];
        }
    }

    public static function relationActorIds($actorId)
    {
        // Get actor's current relations from database
        $relationData = arElasticSearchActorPdo::serializeObjectRelations($actorId);

        return self::uniqueIdsFromRelationData($relationData);
    }

    public static function uniqueIdsFromRelationData($relationData)
    {
        // Parse out unique actor IDs
        $actors = [];

        foreach ($relationData as $relation) {
            $actors[] = $relation['objectId'];
            $actors[] = $relation['subjectId'];
        }

        return array_unique($actors);
    }
}
