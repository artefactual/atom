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
    protected static $statements = [];
    protected static $counter = 0;

    protected $parents = [];
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
        $this->loadTree(QubitInformationObject::ROOT_ID);

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
        foreach (self::getCachedChildren($parentId) as $item) {
            $id = $item->id;

            $ancestors = $inheritedCreators = [];
            $repository = null;
            ++self::$counter;

            try {
                $node = new arElasticSearchInformationObjectPdo($id, $options);
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
            if (array_search($id, $this->parents)) {
                // Pass ancestors, repository and creators down to descendants
                $this->recursivelyAddInformationObjects($id, $totalRows, [
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

        if (!isset(self::$statements['getChildren'])) {
            $sql = 'SELECT
                  io.id,
                  io.lft,
                  io.rgt';
            $sql .= ' FROM '.QubitInformationObject::TABLE_NAME.' io';
            $sql .= ' WHERE io.parent_id = ?';
            $sql .= ' ORDER BY io.lft';

            self::$statements['getChildren'] = self::$conn->prepare($sql);
        }

        self::$statements['getChildren']->execute([$parentId]);

        return self::$statements['getChildren']->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getCachedChildren($parentId)
    {
        if (!isset(self::$conn)) {
            self::$conn = Propel::getConnection();
        }

        if (!isset(self::$statements['getCachedChildren'])) {
            $sql = 'SELECT id';
            $sql .= ' FROM indexing_sequence';
            $sql .= ' WHERE parent_id = ?';

            $statements['getCachedChildren'] = self::$conn->prepare($sql);
        }

        $statements['getCachedChildren']->execute([$parentId]);

        return $statements['getCachedChildren']->fetchAll(PDO::FETCH_OBJ);
    }

    public function loadTree($parentId)
    {
        if (!isset(self::$conn)) {
            self::$conn = Propel::getConnection();
        }

        // Create table if it doesn't exist
        if (!isset($this->statements['loadTreeCreateTable']))
        {
          $sql = 'CREATE TABLE IF NOT EXISTS indexing_sequence (id int, parent_id int)';

          $this->statements['loadTreeCreateTable'] = self::$conn->prepare($sql);
        }

        $this->statements['loadTreeCreateTable']->execute();

        // Delete existing sequence
        if (!isset($this->statements['loadTreeDeleteIndexingSequence']))
        {
          $sql = 'DELETE FROM indexing_sequence';

          $this->statements['loadTreeDeleteIndexingSequence'] = self::$conn->prepare($sql);
        }

        $this->statements['loadTreeDeleteIndexingSequence']->execute();

        // Loop through hierarchy and add to search index
        if (!isset($this->statements['loadTreeInsertIntoIndexingSequence']))
        {
            $sql = 'INSERT INTO indexing_sequence
                WITH RECURSIVE cte (id, parent_id) AS (
                  SELECT id, parent_id FROM information_object WHERE parent_id = 1
                  UNION ALL
                  SELECT i.id, i.parent_id FROM information_object i
                  INNER JOIN cte ON i.parent_id = cte.id
                )
                SELECT * FROM cte';

            $this->statements['loadTreeInsertIntoIndexingSequence'] = self::$conn->prepare($sql);
        }

        $this->statements['loadTreeInsertIntoIndexingSequence']->execute([$parentId]);

        // Cache parents into memory
        if (!isset($this->statements['loadTreeGetParents']))
        {
            $sql = 'SELECT DISTINCT parent_id FROM indexing_sequence';

            $this->statements['loadTreeGetParents'] = self::$conn->prepare($sql);
        }

        $this->statements['loadTreeGetParents']->execute();
        $parents = $this->statements['loadTreeGetParents']->fetchAll(PDO::FETCH_ASSOC);

        foreach ($parents as $row) {
            $this->parents[] = $row['parent_id'];
        }
    }
}
