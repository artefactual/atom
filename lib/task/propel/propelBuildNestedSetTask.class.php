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
 * Regenerate nested set column values.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class propelBuildNestedSetTask extends sfBaseTask
{
    private $children;
    private $conn;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $this->conn = $databaseManager->getDatabase('propel')->getConnection();

        $tables = [
            'information_object' => 'QubitInformationObject',
            'term' => 'QubitTerm',
            'menu' => 'QubitMenu',
        ];

        $excludeTables = [];

        if (!empty($options['exclude-tables'])) {
            $excludeTables = array_map('trim', explode(',', $options['exclude-tables']));
        }

        foreach ($tables as $table => $classname) {
            if (in_array($table, $excludeTables)) {
                $this->logSection('propel', 'Skip nested set build for '.$table.'.');

                continue;
            }

            $this->logSection('propel', 'Build nested set for '.$table.'...');

            $this->conn->beginTransaction();

            $sql = 'SELECT id, parent_id';
            $sql .= ' FROM '.constant($classname.'::TABLE_NAME');
            $sql .= ' ORDER BY parent_id ASC, lft ASC';

            $this->children = [];

            // Build hash of child rows keyed on parent_id
            foreach ($this->conn->query($sql, PDO::FETCH_ASSOC) as $item) {
                if (isset($this->children[$item['parent_id']])) {
                    array_push($this->children[$item['parent_id']], $item['id']);
                } else {
                    $this->children[$item['parent_id']] = [$item['id']];
                }
            }

            $rootNode = [
                'id' => $classname::ROOT_ID,
                'lft' => 1,
                'rgt' => null,
            ];

            try {
                self::recursivelyUpdateTree($rootNode, $classname);
            } catch (PDOException $e) {
                $this->conn->rollback();

                throw new sfException($e);
            }

            $this->conn->commit();
        }

        $this->logSection('propel', 'Done!');
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('exclude-tables', null, sfCommandOption::PARAMETER_OPTIONAL, 'Exclude tables (comma-separated). Options: information_object, term, menu'),
        ]);

        $this->namespace = 'propel';
        $this->name = 'build-nested-set';
        $this->briefDescription = 'Build all nested set values.';

        $this->detailedDescription = <<<'EOF'
Build nested set values. Optionally excluding tables (information_object, term, menu).
EOF;
    }

    protected function recursivelyUpdateTree($node, $classname)
    {
        $width = 2;
        $lft = $node['lft'];

        if (isset($this->children[$node['id']])) {
            ++$lft;

            foreach ($this->children[$node['id']] as $id) {
                $child = ['id' => $id, 'lft' => $lft, 'rgt' => null];

                // Update children first
                $w0 = self::recursivelyUpdateTree($child, $classname);
                $lft += $w0;
                $width += $w0;
            }

            // Clear already processed children
            unset($this->children[$node['id']]);
        }

        $node['rgt'] = $node['lft'] + $width - 1;

        $sql = 'UPDATE '.$classname::TABLE_NAME;
        $sql .= ' SET lft = '.$node['lft'];
        $sql .= ', rgt = '.$node['rgt'];
        $sql .= ' WHERE id = '.$node['id'].';';

        $this->conn->exec($sql);

        return $width;
    }
}
