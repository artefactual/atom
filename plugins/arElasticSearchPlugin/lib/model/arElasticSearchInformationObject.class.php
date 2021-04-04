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
    protected static $statements;
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

        // Recursively descend down hierarchy
        $this->addInformationObjects(
            QubitInformationObject::ROOT_ID,
            $this->count
        );

        return $this->errors;
    }

    public function addInformationObjects($parentId, $totalRows, $options = [])
    {
        $max = isset($options['limit']) ? $options['limit'] : null;

        // Loop through descendants and add to search index
        foreach (self::getChildren($parentId, $options) as $item) {
            ++self::$counter;

            $parentId = $item->parent_id;

            $options = [];

            if (isset($this->parents[($item->parent_id)])) {
                $options['ancestors'] = $this->parents[($item->parent_id)]['ancestors'];
                $options['inheritedCreators'] = $this->parents[($item->parent_id)]['inheritedCreators'];
            }

            try {
                $node = new arElasticSearchInformationObjectPdo($item->id, $options);
                $data = $node->serialize();

                QubitSearch::getInstance()->addDocument($data, 'QubitInformationObject');

                $this->logEntry($data['i18n'][$data['sourceCulture']]['title'], self::$counter, $max);

                if (!isset($this->parents[($item->parent_id)])) {
                    $this->parents[($item->parent_id)] = [
                      'ancestors' => $node->getAncestors(),
                      'inheritedCreators' => $node->inheritedCreators
                    ];
                }
            } catch (sfException $e) {
                $this->errors[] = $e->getMessage();
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

    public static function getChildren($parentId, $options = [])
    {
        // Assemble LIMIT clause
        $skip = (!empty($options['skip'])) ? $options['skip'] : 0;
        $limit = (!empty($options['limit'])) ? $options['limit'] : 0;

        $limitClause = '';

        if ($limit) {
            $limitClause .= sprintf('LIMIT %d ', $limit);
        }

        if ($skip) {
            $limitClause .= sprintf('OFFSET %d ', $skip);
        }

        // Recursively fetch children
        if (!isset(self::$conn)) {
            self::$conn = Propel::getConnection();
        }

        if (!isset(self::$statements['getChildren'])) {
            $sql = 'WITH RECURSIVE cte (id, parent_id) AS (
                        SELECT id, parent_id FROM information_object WHERE parent_id = ?
                        UNION ALL
                        SELECT i.id, i.parent_id FROM information_object i
                        INNER JOIN cte ON i.parent_id = cte.id
                        '.$limitClause.'
                    )
                SELECT * FROM cte';

            self::$statements['getChildren'] = self::$conn->prepare($sql);
        }

        self::$statements['getChildren']->execute([$parentId]);

        return self::$statements['getChildren']->fetchAll(PDO::FETCH_OBJ);
    }
}
