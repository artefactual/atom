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
 * Regenerate nested set column values
 *
 * @package    symfony
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 */
class propelBuildNestedSetTask extends sfBaseTask
{
  private $rows = array(); // Holds all our SQL update queries

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace = 'propel';
    $this->name = 'build-nested-set';
    $this->briefDescription = 'Build all nested set values.';

    $this->detailedDescription = <<<EOF
Build all nested set values.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $tables = array(
      'information_object' => 'QubitInformationObject',
      'actor' => 'QubitActor',
      'term' => 'QubitTerm'
    );

    foreach ($tables as $table => $classname)
    {
      $this->logSection('propel', 'Build nested set for '.$table.'...');

      $sql = 'SELECT id, parent_id';
      $sql .= ' FROM '.constant($classname.'::TABLE_NAME');
      $sql .= ' ORDER BY parent_id ASC, lft ASC';

      $tree = $children = array();

      foreach ($conn->query($sql, PDO::FETCH_ASSOC) as $item)
      {
        // Add root node to tree
        if (constant($classname.'::ROOT_ID') == $item['id'])
        {
          array_push($tree, array(
            'id' => $item['id'],
            'lft' => 1,
            'rgt' => null,
            'children' => array())
          );
        }
        else
        {
          // build hash of child rows keyed on parent_id
          if (isset($children[$item['parent_id']]))
          {
            array_push($children[$item['parent_id']], $item['id']);
          }
          else
          {
            $children[$item['parent_id']] = array($item['id']);
          }
        }
      }

      // Recursively add child nodes
      self::addChildren($tree[0], $children, 1);

      // Crawl tree and build sql statement to update nested set columns
      $rows = self::getNsUpdateRows($tree[0], $classname);

      // Update database
      $conn->beginTransaction();
      try
      {
        // There seems to be some limit on how many rows we can update with one
        // exec() statement, so chunk the update rows
        $incr = 4000;
        for ($i=0; $i <= count($this->rows); $i+=$incr)
        {
          $sql = implode("\n", array_slice($this->rows, $i, $incr));
          $conn->exec($sql);
        }
      }
      catch (PDOException $e)
      {
        $conn->rollback();
        throw new sfException($e);
      }

      $conn->commit();
    } // endforeach

    $this->logSection('propel', 'Done!');
  }

  protected function addChildren(&$node, $children, $lft)
  {
    $width = 2;

    if (isset($children[$node['id']]))
    {
      $lft++;
      foreach ($children[$node['id']] as $id)
      {
        $child = array('id' => $id, 'lft' => $lft, 'rgt' => null, 'children' => array());

        $w0 = self::addChildren($child, $children, $lft);
        $lft += $w0;
        $width += $w0;

        array_push($node['children'], $child);
      }
    }

    $node['rgt'] = $node['lft'] + $width - 1;

    return $width;
  }

  protected function getNsUpdateRows($node, $classname)
  {
    $str  = 'UPDATE '.constant($classname.'::TABLE_NAME');
    $str .= ' SET lft = '.$node['lft'];
    $str .= ', rgt = '.$node['rgt'];
    $str .= ' WHERE id = '.$node['id'].";";
    $this->rows[$str] = $str;

    if (0 < count($node['children']))
    {
      foreach ($node['children'] as $child)
      {
        self::getNsUpdateRows($child, $classname);
      }
    }
  }
}
