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
 * Updates the publication status to the descendants of an information object. Uses
 * ES updateByQuery to update the ES index rather than replace each child document.
 */
class arUpdatePublicationStatusJob extends arBaseJob
{
    /**
     * @see arBaseJob::$requiredParameters
     */
    protected $extraRequiredParameters = [
        'objectId',
        'publicationStatusId',
    ];

    public function runJob($parameters)
    {
        if (null === $publicationStatus = QubitTerm::getById($parameters['publicationStatusId'])) {
            $this->error($this->i18n->__('Invalid publication status id: %1', ['%1' => $parameters['publicationStatusId']]));

            return false;
        }

        if (null === $resource = QubitInformationObject::getById($parameters['objectId'])) {
            $this->error($this->i18n->__('Invalid description id: %1', ['%1' => $parameters['objectId']]));

            return false;
        }

        $sql = 'UPDATE status
            JOIN information_object io ON status.object_id = io.id
            SET status.status_id = :publicationStatus
            WHERE status.type_id = :publicationStatusType
            AND io.lft > :lft
            AND io.rgt < :rgt';

        $message = $this->i18n->__(
            'Updating publication status for the descendants of "%1" to "%2".',
            ['%1' => $resource->getTitle(['cultureFallback' => true]), '%2' => $publicationStatus->name]
        );

        $this->job->addNoteText($message);
        $this->info($message);

        $params = [
            ':publicationStatus' => $publicationStatus->id,
            ':publicationStatusType' => QubitTerm::STATUS_TYPE_PUBLICATION_ID,
            ':lft' => $resource->lft,
            ':rgt' => $resource->rgt,
        ];

        $descriptionsUpdated = QubitPdo::modify(
            $sql,
            $params
        );

        // Use updateByQuery to update publication status in ES for resource.
        $query = new \Elastica\Query\Term();
        $query->setTerm('ancestors', $resource->id);

        $queryScript = \Elastica\Script\AbstractScript::create([
            'script' => [
                'source' => 'ctx._source.publicationStatusId = '.$publicationStatus->id,
                'lang' => 'painless',
            ],
        ]);

        // Set to 'proceed' so index update does not abort if a document version
        // conflict occurs. This happens if an ES doc is updated during this update.
        // Ignore conflicts since conflict documents would still be indexed with the
        // updated publication status from the DB.
        $options = [
            'conflicts' => 'proceed',
        ];

        $response = QubitSearch::getInstance()->index->getIndex('QubitInformationObject')->updateByQuery($query, $queryScript, $options)->getData();

        $message = $this->i18n->__(
            'Index update completed in %1 ms.',
            ['%1' => $response['took']]
        );
        $this->info($message);

        if (!empty($response['failures'])) {
            $message = $this->i18n->__(
                'Indexing failures occurred when updating publication status. %1 records were not updated.',
                ['%1' => count($response['failures'])]
            );
            $this->job->addNoteText($message);
            $this->info($message);

            foreach ($response['failures'] as $key => $failure) {
                $message = $this->i18n->__(
                    '%1: $2',
                    ['%1' => $key, '%2' => $failure]
                );
                $this->info($message);
            }

            $this->job->setStatusError(
                $this->i18n->__('Some descendants were not successfully re-indexed. Please try again.')
            );
            $this->job->save();

            return false;
        }

        $message = $this->i18n->__(
            '%1 descriptions updated.',
            ['%1' => $descriptionsUpdated]
        );
        $this->job->addNoteText($message);
        $this->info($message);

        $this->job->setStatusCompleted();
        $this->job->save();

        return true;
    }
}
