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

class ActorMoveDescriptionRelationsTask extends arBaseTask
{
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument(
                'source',
                sfCommandArgument::REQUIRED,
                'The slug of the source actor'
            ),
            new sfCommandArgument(
                'target',
                sfCommandArgument::REQUIRED,
                'The slug of the target actor'
            ),
        ]);

        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                true
            ),
            new sfCommandOption(
                'env',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The environment',
                'cli'
            ),
            new sfCommandOption(
                'connection',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The connection name',
                'propel'
            ),
            new sfCommandOption(
                'skip-index',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Skip Elasticsearch indexing'
            ),
        ]);

        $this->namespace = 'actor';
        $this->name = 'move-description-relations';
        $this->briefDescription = 'Move actor-description relations';
        $this->detailedDescription = <<<'EOF'
Move description relations from a source actor to a target actor,
including all events and name access point relations.
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        if (null === $source = QubitActor::getBySlug($arguments['source'])) {
            throw new sfException(
                'An actor with slug "'.$arguments['source'].'" could not be found.'
            );
        }

        if (null === $target = QubitActor::getBySlug($arguments['target'])) {
            throw new sfException(
                'An actor with slug "'.$arguments['target'].'" could not be found.'
            );
        }

        $this->log(
            'Moving description relations from "'
            .$source->getAuthorizedFormOfName(['cultureFallback' => true])
            .'" to "'
            .$target->getAuthorizedFormOfName(['cultureFallback' => true])
            .'" ...'
        );

        // Amalgamate related description ids before update
        $relatedIoIds = [];
        if (!$options['skip-index']) {
            $sql = "SELECT event.object_id FROM event
                JOIN object ON event.object_id=object.id
                WHERE event.actor_id=:sourceId
                AND object.class_name='QubitInformationObject'
                UNION ALL
                SELECT relation.subject_id FROM relation
                JOIN object ON relation.subject_id=object.id
                WHERE relation.object_id=:sourceId
                AND relation.type_id=:typeId
                AND object.class_name='QubitInformationObject'";
            $params = [
                ':sourceId' => $source->id,
                ':typeId' => QubitTerm::NAME_ACCESS_POINT_ID,
            ];
            $relatedIoIds = QubitPdo::fetchAll(
                $sql,
                $params,
                ['fetchMode' => PDO::FETCH_COLUMN]
            );
        }

        // Move all events
        $sql = "UPDATE event
            JOIN object ON event.object_id=object.id
            SET event.actor_id=:targetId
            WHERE event.actor_id=:sourceId
            AND object.class_name='QubitInformationObject'";
        $params = [':targetId' => $target->id, ':sourceId' => $source->id];
        $updatedCount = QubitPdo::modify($sql, $params);

        // Move name access point relations
        $sql = "UPDATE relation
            JOIN object ON relation.subject_id=object.id
            SET relation.object_id=:targetId
            WHERE relation.object_id=:sourceId
            AND relation.type_id=:typeId
            AND object.class_name='QubitInformationObject'";
        $params = [
            ':targetId' => $target->id,
            ':sourceId' => $source->id,
            ':typeId' => QubitTerm::NAME_ACCESS_POINT_ID,
        ];
        $updatedCount += QubitPdo::modify($sql, $params);

        $this->log($updatedCount.' description relations moved.');

        // Update Elasticsearch index
        if (!$options['skip-index']) {
            $this->log('Updating Elasticsearch index ...');

            $search = QubitSearch::getInstance();
            $search->update($source);
            $search->update($target);
            foreach ($relatedIoIds as $id) {
                $search->update(QubitInformationObject::getById($id));
            }
        } else {
            $this->log(
                'The Elasticsearch index has not been updated. '
                .'Please run search:populate manually to update the actor-description '
                .'relations in the search index.'
            );
        }

        $this->log('Done!');
    }
}
