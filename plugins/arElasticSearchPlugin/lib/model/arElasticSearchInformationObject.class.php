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

class arElasticSearchInformationObject extends arElasticSearchModelBase
{
    protected static $conn;
    protected static $statement;
    protected static $counter = 0;

    protected $errors = [];

    public function load()
    {
        // Get count of all information objects
        $sql = 'SELECT COUNT(*)';
        $sql .= ' FROM '.QubitInformationObject::TABLE_NAME;
        $sql .= ' WHERE id > ?';

        $this->count = QubitPdo::fetchColumn($sql, [QubitInformationObject::ROOT_ID]);
    }

    public function populate()
    {
        $this->load();

        // Pass root data to top-levels to avoid ancestors query
        $ancestors = [[
            'id' => QubitInformationObject::ROOT_ID,
            'identifier' => null,
            'repository_id' => null,
        ]];

        // Recursively descend down hierarchy
        $this->recursivelyAddInformationObjects(
            QubitInformationObject::ROOT_ID,
            $this->count,
            ['ancestors' => $ancestors]
        );

        return $this->errors;
    }

    public function recursivelyAddInformationObjects($parentId, $totalRows, $options = [])
    {
        // Loop through children and add to search index
        foreach (self::getChildren($parentId) as $item) {
            $ancestors = $inheritedCreators = [];
            $repository = null;
            ++self::$counter;

            try {
                $node = new arElasticSearchInformationObjectPdo($item->id, $options);
                $data = $node->serialize();

                QubitSearch::getInstance()->addDocument($data, 'QubitInformationObject');

                $this->logEntry($data['i18n'][$data['sourceCulture']]['title'], self::$counter);

                $ancestors = array_merge($node->getAncestors(), [[
                    'id' => $node->id,
                    'identifier' => $node->identifier,
                    'repository_id' => $node->repository_id,
                ]]);
                $repository = $node->getRepository();
                $inheritedCreators = array_merge($node->inheritedCreators, $node->creators);
            } catch (sfException $e) {
                $this->errors[] = $e->getMessage();
            }

            // Descend hierarchy
            if (1 < ($item->rgt - $item->lft)) {
                // Pass ancestors, repository and creators down to descendants
                $this->recursivelyAddInformationObjects($item->id, $totalRows, [
                    'ancestors' => $ancestors,
                    'repository' => $repository,
                    'inheritedCreators' => $inheritedCreators,
                ]);
            }
        }
    }

    public static function update($object, $options = [])
    {
        // Update description
        $node = new arElasticSearchInformationObjectPdo($object->id);
        QubitSearch::getInstance()->addDocument($node->serialize(), 'QubitInformationObject');

        // Update descendants if requested and they exists
        if ($options['updateDescendants'] && $object->rgt - $object->lft > 1) {
            self::updateDescendants($object);
        }
    }

    public static function updateDescendants($object)
    {
        // Update synchronously in CLI tasks and jobs
        $context = sfContext::getInstance();
        $env = $context->getConfiguration()->getEnvironment();
        if (in_array($env, ['cli', 'worker'])) {
            foreach (self::getChildren($object->id) as $child) {
                // TODO: Use partial updates to only get and add
                // the fields that are inherited from the ancestors.
                // Be aware that transient descendants are entirely
                // added the first time to the search index in here
                // and they will require a complete update.
                self::update($child, ['updateDescendants' => true]);
            }

            return;
        }

        // Update asynchronously in other environments
        $jobOptions = [
            'ioIds' => [$object->id],
            'updateIos' => false,
            'updateDescendants' => true,
        ];
        QubitJob::runJob('arUpdateEsIoDocumentsJob', $jobOptions);

        // Let user know descendants update has started
        $jobsUrl = $context->routing->generate(null, ['module' => 'jobs', 'action' => 'browse']);
        $message = $context->i18n->__('Your description has been updated. Its descendants are being updated asynchronously – check the <a href="%1">job scheduler page</a> for status and details.', ['%1' => $jobsUrl]);
        $context->user->setFlash('notice', $message);
    }

    public static function getChildren($parentId)
    {
        if (!isset(self::$conn)) {
            self::$conn = Propel::getConnection();
        }

        if (!isset(self::$statement)) {
            $sql = 'SELECT io.id, io.lft, io.rgt';
            $sql .= ' FROM '.QubitInformationObject::TABLE_NAME.' io';
            $sql .= ' WHERE io.parent_id = ?';
            $sql .= ' ORDER BY io.lft';

            self::$statement = self::$conn->prepare($sql);
        }

        self::$statement->execute([$parentId]);

        return self::$statement->fetchAll(PDO::FETCH_OBJ);
    }
}
