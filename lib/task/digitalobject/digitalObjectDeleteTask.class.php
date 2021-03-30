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

class digitalObjectDeleteTask extends arBaseTask
{
    private $validMediaTypes;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $nDeleted = 0;
        $objectIds = [];

        $this->validMediaTypes = [
            'audio' => QubitTerm::AUDIO_ID,
            'image' => QubitTerm::IMAGE_ID,
            'text' => QubitTerm::TEXT_ID,
            'video' => QubitTerm::VIDEO_ID,
        ];

        parent::execute($arguments, $options);

        $t = new QubitTimer();

        // Remind user they are in dry run mode
        if ($options['dry-run']) {
            $this->logSection('digital-object', '*** DRY RUN (no changes will be made to the database) ***');
        }

        if ($options['media-type'] && !array_key_exists($options['media-type'], $this->validMediaTypes)) {
            error_log(sprintf(
                'Invalid value for "media-type", must be one of (%s)',
                implode(',', array_keys($this->validMediaTypes))
            ));

            exit(1);
        }

        $sql = "SELECT slug.object_id, object.class_name
            FROM slug
            JOIN object ON object.id = slug.object_id
            WHERE slug.slug = '".$arguments['slug']."'";

        $statement = QubitPdo::prepareAndExecute($sql);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row['object_id']) {
            throw new sfException('Invalid slug "'.$arguments['slug'].'" entered.');
        }

        if (!in_array($row['class_name'], ['QubitInformationObject', 'QubitRepository'])) {
            throw new sfException('Invalid slug with object type "'.$row['class_name'].'" entered.');
        }

        if (
            'QubitInformationObject' == $row['class_name']
            && null === $informationObject = QubitInformationObject::getById($row['object_id'])
        ) {
            throw new sfException('Failed to fetch information object with the slug given.');
        }
        if (
            'QubitRepository' == $row['class_name']
            && null === $repository = QubitRepository::getById($row['object_id'])
        ) {
            throw new sfException('Failed to fetch repository with the slug given.');
        }

        switch ($row['class_name']) {
            case 'QubitInformationObject':
                $objectIds = $options['and-descendants'] ? $this->getIoDescendantIds($informationObject->lft, $informationObject->rgt) : [$informationObject->id];

                break;

            case 'QubitRepository':
                // Get all linked top level information object recs.
                $sql = 'SELECT id, lft, rgt FROM '.QubitInformationObject::TABLE_NAME.' WHERE repository_id=:repository_id';
                $params = [':repository_id' => $repository->id];
                $relatedInformationObjects = QubitPdo::fetchAll($sql, $params, ['fetchMode' => PDO::FETCH_ASSOC]);

                foreach ($relatedInformationObjects as $io) {
                    // Always include descendants when deleting by repository.
                    $objectIds = array_merge($objectIds, $this->getIoDescendantIds($io['lft'], $io['rgt']));
                }

                break;
        }

        foreach ($objectIds as $id) {
            if (null !== $object = QubitInformationObject::getById($id)) {
                $success = $this->deleteDigitalObject($object, $options);

                $this->logSection(
                    'digital-object',
                    sprintf(
                        '(%d of %d) %s: %s',
                        $success ? ++$nDeleted : $nDeleted,
                        count($objectIds),
                        $success ? 'deleting digital object for' : 'nothing to delete',
                        $object->getTitle(['cultureFallback' => true])
                    )
                );
            }
            Qubit::clearClassCaches();
        }

        $this->logSection('digital-object', sprintf('%d digital objects deleted (%.2fs elapsed)', $nDeleted, $t->elapsed()));
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('slug', sfCommandArgument::REQUIRED, 'Slug.'),
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('and-descendants', null, sfCommandOption::PARAMETER_NONE, 'Remove digital objects for descendant archival descriptions as well'),
            new sfCommandOption('media-type', null, sfCommandOption::PARAMETER_OPTIONAL, 'Limit digital object deletion to a specific media type (e.g. "audio" or "image" or "text" or "video). "Other" is not supported', null),
            new sfCommandOption('dry-run', 'd', sfCommandOption::PARAMETER_NONE, 'Dry run (no database changes)', null),
        ]);

        $this->namespace = 'digitalobject';
        $this->name = 'delete';
        $this->briefDescription = 'Delete digital objects given an archival description slug.';

        $this->detailedDescription = <<<'EOF'
Delete digital objects by slug. Slug must be an information object, or a
repository.
EOF;
    }

    private function getIoDescendantIds($lft, $rgt)
    {
        $sql = 'SELECT io.id
            FROM '.QubitInformationObject::TABLE_NAME.' io
            WHERE io.lft >= :lft
            AND io.rgt <= :rgt
            ORDER BY io.lft ASC';

        $params = [':lft' => $lft, ':rgt' => $rgt];

        return QubitPDO::fetchAll($sql, $params, ['fetchMode' => PDO::FETCH_COLUMN]);
    }

    private function deleteDigitalObject($object, $options = [])
    {
        foreach ($object->digitalObjectsRelatedByobjectId as $do) {
            if (!$options['media-type'] || ($options['media-type'] && $do->mediaTypeId == $this->validMediaTypes[$options['media-type']])) {
                if (!$options['dry-run']) {
                    // Remove appropriate digital object files, empty directories left
                    // behind, and db entries
                    $do->delete();
                }

                return true;
            }
        }
    }
}
