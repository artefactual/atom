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

class updatePublicationStatusTask extends arBaseTask
{
    protected $failureCount = 0;

    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('publicationStatus', sfCommandArgument::REQUIRED, 'Desired publication status [draft|published]'),
            new sfCommandArgument('slug', sfCommandArgument::REQUIRED, 'Resource slug'),
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('ignore-descendants', 'i', sfCommandOption::PARAMETER_NONE, 'Don\'t update descendants', null),
            new sfCommandOption('no-confirm', 'y', sfCommandOption::PARAMETER_NONE, 'No confirmation message', null),
            new sfCommandOption('repo', 'r', sfCommandOption::PARAMETER_NONE, 'Update all descriptions in given repository', null),
        ]);

        $this->namespace = 'tools';
        $this->name = 'update-publication-status';
        $this->briefDescription = 'Updates the publication status of description(s)';
        $this->detailedDescription = <<<'EOF'
This task can be used to update the publication status of either an individual
description or, if the --repo option is used, all of the descriptions in a
repository. Descendents of updated descriptions will also be updated unless the
--ignore-descendants option is used.
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $criteria = new Criteria();
        $criteria->add(QubitSlug::SLUG, $arguments['slug']);
        $criteria->addJoin(QubitSlug::OBJECT_ID, QubitObject::ID);

        if (!$options['repo']) {
            $resource = QubitInformationObject::get($criteria)->__get(0);
        } else {
            $resource = QubitRepository::get($criteria)->__get(0);
        }

        $publicationStatus = QubitTerm::getById($this->getPublicationStatusIdByName($arguments['publicationStatus']));

        // Check if the resource exists
        if (!isset($resource)) {
            throw new sfException('Resource not found');
        }

        // Check if the given status is correct and exists
        if (!isset($publicationStatus)) {
            throw new sfException('Publication status not found');
        }
        if (QubitTaxonomy::PUBLICATION_STATUS_ID != $publicationStatus->taxonomyId) {
            throw new sfException('Given term is not part of the publication status taxonomy');
        }

        // Final confirmation
        if (!$options['no-confirm']) {
            if (
                !$this->askConfirmation(
                    [
                        'Please, confirm that you want to change',
                        'the publication status of "'.$resource->__toString().'"',
                        'to "'.$publicationStatus.'" (y/N)',
                    ],
                    'QUESTION_LARGE',
                    false
                )
            ) {
                $this->logSection('tools', 'Bye!');

                return 1;
            }
        }

        // Do work
        if (!$options['repo']) {
            $this->updatePublicationStatus($resource, $publicationStatus);

            if (!$options['ignore-descendants']) {
                $this->updatePublicationStatusDescendants($resource, $publicationStatus);
            }
        } else {
            $criteria = new Criteria();
            $criteria->add(QubitInformationObject::REPOSITORY_ID, $resource->id);

            foreach (QubitInformationObject::get($criteria) as $item) {
                $this->updatePublicationStatus($item, $publicationStatus);

                if (!$options['ignore-descendants']) {
                    $this->updatePublicationStatusDescendants($item, $publicationStatus);
                }
            }
        }

        if (!empty($this->failureCount)) {
            $this->logSection(
                'tools',
                sprintf(
                    'Indexing failures occurred when updating publication status. %d records were not updated.',
                    $this->failureCount
                )
            );
        }

        echo "\n";
        $this->logSection('tools', 'Finished updating publication statuses');
    }

    protected function updatePublicationStatus($resource, $publicationStatus)
    {
        $resource->indexOnSave = false;
        $resource->setPublicationStatus($publicationStatus->id);
        $resource->save();

        QubitSearch::getInstance()->partialUpdate(
            $resource,
            ['publicationStatusId' => $publicationStatus->id]
        );
    }

    protected function updatePublicationStatusDescendants($resource, $publicationStatus)
    {
        $sql = 'UPDATE status
            JOIN information_object io ON status.object_id = io.id
            SET status.status_id = :publicationStatus
            WHERE status.type_id = :publicationStatusType
            AND io.lft > :lft
            AND io.rgt < :rgt';

        $params = [
            ':publicationStatus' => $publicationStatus->id,
            ':publicationStatusType' => QubitTerm::STATUS_TYPE_PUBLICATION_ID,
            ':lft' => $resource->lft,
            ':rgt' => $resource->rgt,
        ];

        $descriptionsUpdated = QubitPdo::modify($sql, $params);

        // Use updateByQuery to update publication status in ES for resource.
        $query = new \Elastica\Query\Term();
        $query->setTerm('ancestors', $resource->id);

        $queryScript = \Elastica\Script\AbstractScript::create([
            'script' => [
                'source' => 'ctx._source.publicationStatusId = '.$publicationStatus->id,
                'lang' => 'painless',
            ],
        ]);

        $options = ['conflicts' => 'proceed'];

        $response = QubitSearch::getInstance()->index->getIndex('QubitInformationObject')->updateByQuery($query, $queryScript, $options)->getData();

        if (!empty($response['failures'])) {
            $this->failures += count($response['failures']);
        }
    }

    private function getPublicationStatusIdByName($pubStatus)
    {
        $sql = 'SELECT t.id FROM term t JOIN term_i18n ti ON t.id = ti.id
            WHERE t.taxonomy_id = ? AND ti.name = ?';

        $pubStatusId = QubitPdo::fetchColumn($sql, [QubitTaxonomy::PUBLICATION_STATUS_ID, $pubStatus]);
        if (!$pubStatusId) {
            throw new sfException("Invalid publication status specified: {$pubStatus}");
        }

        return $pubStatusId;
    }
}
