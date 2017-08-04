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

require_once 'propel/engine/builder/om/php5/PHP5ObjectBuilder.php';

class QubitObjectBuilder extends PHP5ObjectBuilder
{
  protected
    $basePeerClassName = null,

    $classNameColumn = null,
    $cultureColumn = null,
    $nestedSetLeftColumn = null,
    $nestedSetRightColumn = null,
    $sourceCultureColumn = null,

    $inheritanceFk = null,
    $selfFk = null,

    $i18nFk = null,
    $emulateOnDeleteCascade = null,
    $emulateOnDeleteSetNull = null;

  public function __construct(Table $table)
  {
    parent::__construct($table);

    $this->initialize($table);
  }

  public function initialize(Table $table)
  {
    $this->basePeerClassName = preg_replace('/.*\./', null, $this->getBasePeer($table));

    foreach ($table->getColumns() as $column)
    {
      if ($column->getName() == 'class_name')
      {
        $this->classNameColumn = $column;
      }

      if ($column->getAttribute('isCulture'))
      {
        $this->cultureColumn = $column;
      }

      if ($column->getAttribute('nestedSetLeftKey'))
      {
        $this->nestedSetLeftColumn = $column;
      }

      if ($column->getAttribute('nestedSetRightKey'))
      {
        $this->nestedSetRightColumn = $column;
      }

      if ($column->getName() == 'source_culture')
      {
        $this->sourceCultureColumn = $column;
      }
    }

    foreach ($table->getForeignKeys() as $fk)
    {
      foreach ($fk->getLocalColumns() as $localName)
      {
        if ($table->getColumn($localName)->getAttribute('inheritanceKey'))
        {
          $this->inheritanceFk = $fk;
        }
      }

      if ($fk->getForeignTableName() == $table->getName())
      {
        $this->selfFk = $fk;
      }
    }

    foreach ($table->getReferrers() as $refFk)
    {
      if ($refFk->getTable()->getName() == $table->getAttribute('i18nTable'))
      {
        $this->i18nFk = $refFk;
      }

      if (!$this->getPlatform()->supportsNativeDeleteTrigger() || $this->getBuildProperty('emulateForeignKeyConstraints'))
      {
        if ($refFk->getOnDelete() == ForeignKey::CASCADE)
        {
          $this->emulateOnDeleteCascade = true;
        }

        if ($refFk->getOnDelete() == ForeignKey::SETNULL)
        {
          $this->emulateOnDeleteSetNull = true;
        }
      }
    }
  }

  protected function getBaseClass()
  {
    if (isset($this->inheritanceFk))
    {
      return self::getNewObjectBuilder($this->getForeignTable($this->inheritanceFk))->getObjectClassName();
    }
  }

  public function getClassName()
  {
    return $this->getBuildProperty('basePrefix').ucfirst($this->getTable()->getPhpName());
  }

  public function getColumnConstant(Column $column)
  {
    return "{$this->getPeerClassName()}::".strtoupper($column->getName());
  }

  protected function getColumnVarName(Column $column)
  {
    return $column->getPhpName();
  }

  public function getPeerClassName()
  {
    return $this->getStubObjectBuilder()->getClassName();
  }

  protected function getRetrieveMethodName()
  {
    $names = array();
    foreach ($this->getTable()->getPrimaryKey() as $column)
    {
      $names[] = ucfirst($column->getPhpName());
    }

    return 'getBy'.implode($names, 'And');
  }

  protected function getVarName($plural = null)
  {
    $name = $this->getTable()->getPhpName();
    if (!empty($plural))
    {
      $name .= 's';
    }

    return $name;
  }

  protected function addClassOpen(&$script)
  {
    $this->refFks = array();
    foreach ($this->getTable()->getReferrers() as $refFk)
    {
      $foreignPeerBuilder = self::getNewPeerBuilder($refFk->getTable());

      if ($foreignPeerBuilder->inheritanceFk != $refFk)
      {
        $this->refFks[] = $refFk;
      }
    }

    $extends = null;
    if (isset($this->inheritanceFk))
    {
      $this->baseClassName = preg_replace('/.*\./', null, $this->getBaseClass());
      $extends = " extends $this->baseClassName";
    }

    $script .= <<<script

abstract class {$this->getClassName()}$extends implements ArrayAccess
{
script;
  }

  protected function addClassBody(&$script)
  {
    $this->addConstants($script);
    $this->addSelectMethods($script);
    $this->addRetrieveByPkMethods($script);

    if (!isset($this->inheritanceFk))
    {
      $this->addUpdateMethods($script);
    }

    if (isset($this->nestedSetLeftColumn) && isset($this->nestedSetRightColumn))
    {
      $this->addAddOrderByPreorder($script);
      $this->addAddRootsCriteria($script);
    }

    $this->addConstructor($script);
    $this->addColumnMethods($script);

    if (!isset($this->inheritanceFk))
    {
      $this->addNew($script);
      $this->addDeleted($script);
    }

    $this->addManipulationMethods($script);

    if ($this->isAddGenericAccessors())
    {
      $this->addGetByName($script);
      $this->addGetByPosition($script);
      $this->addToArray($script);
    }

    if ($this->isAddGenericMutators())
    {
      $this->addSetByName($script);
      $this->addSetByPosition($script);
      $this->addFromArray($script);
    }

    if (!isset($this->inheritanceFk))
    {
      $this->addGetPrimaryKey($script);
      $this->addSetPrimaryKey($script);
    }

    $this->addFkMethods($script);
    $this->addRefFkMethods($script);

    if ($this->getTable()->getAttribute('isI18n'))
    {
      if (count($this->getTable()->getPrimaryKey()) > 1)
      {
        throw new Exception('i18n support only works with a single primary key');
      }

      $this->addI18nMethods($script);
    }

    if (isset($this->nestedSetLeftColumn) && isset($this->nestedSetRightColumn))
    {
      $this->addHasChildren($script);
      $this->addAddAncestorsCriteria($script);
      $this->addAddDescendantsCriteria($script);
      $this->addUpdateNestedSet($script);
      $this->addDeleteFromNestedSet($script);
      $this->addIsInTree($script);
      $this->addIsRoot($script);
      $this->addIsDescendantOf($script);
      $this->addMoveToFirstChildOf($script);
      $this->addMoveToLastChildOf($script);
      $this->addMoveToPrevSiblingOf($script);
      $this->addMoveToNextSiblingOf($script);
      $this->addMoveSubtreeTo($script);
      $this->addShiftRLValues($script);
    }

    $this->addCall($script);
  }

  protected function addConstants(&$script)
  {
    $consts = array();
    foreach ($this->getTable()->getColumns() as $column)
    {
      $upperColumnName = strtoupper($column->getName());
      $consts[] = <<<consts
    $upperColumnName = '{$this->getTable()->getName()}.$upperColumnName'
consts;
    }
    $consts = implode(",\n", $consts);

    $script .= <<<script

  const
    DATABASE_NAME = '{$this->getDatabase()->getName()}',

    TABLE_NAME = '{$this->getTable()->getName()}',

$consts;

script;
  }

  protected function addSelectMethods(&$script)
  {
    $this->addAddSelectColumns($script);

    $this->addGetFromRow($script);
    $this->addClearCache($script);

    $this->addGet($script);
    $this->addGetAll($script);
    $this->addGetOne($script);
  }

  protected function addAddSelectColumns(&$script)
  {
    $script .= <<<script

  public static function addSelectColumns(Criteria \$criteria)
  {

script;

    if (isset($this->inheritanceFk))
    {
      $foreignPeerBuilder = self::getNewPeerBuilder($this->getForeignTable($this->inheritanceFk));

      $adds = array();
      foreach ($this->inheritanceFk->getLocalForeignMapping() as $localName => $foreignName)
      {
        $adds[] = <<<adds
    \$criteria->addJoin({$this->getColumnConstant($this->getTable()->getColumn($localName))}, {$foreignPeerBuilder->getColumnConstant($this->getForeignTable($this->inheritanceFk)->getColumn($foreignName))});
adds;
      }
      $adds = implode("\n", $adds);

      $script .= <<<script
    parent::addSelectColumns(\$criteria);

$adds


script;
    }

    foreach ($this->getTable()->getColumns() as $column)
    {
      if ($column->isLazyLoad())
      {
        continue;
      }

      $script .= <<<script
    \$criteria->addSelectColumn({$this->getColumnConstant($column)});

script;
    }

    $script .= <<<script

    return \$criteria;
  }

script;
  }

  protected function addClearCache(&$script)
  {
    // Cache subclass instances in the base class
    if (isset($this->inheritanceFk))
    {
      return;
    }

    $script .= <<<script

  public static function clearCache()
  {
    self::\${$this->getVarName(true)} = array();
  }

script;
  }

  protected function addGetFromRow(&$script)
  {
    // Cache subclass instances in the base class
    if (isset($this->inheritanceFk))
    {
      return;
    }

    // Object instances are indexed by primary keys.  In the case of
    // multi table inheritance, all the primary keys are in the base table, so
    // we get the primary key values directly from the result set and possibly
    // avoid constructing a new object.  An alternative would be to construct a
    // new object, but return an old object if one existed.
    $keys = array();
    foreach ($this->getTable()->getPrimaryKey() as $primaryKey)
    {
      $position = $primaryKey->getPosition() - 1;
      $keys[] = <<<keys
    \$keys['{$this->getColumnVarName($primaryKey)}'] = \$row[$position];
keys;
    }
    $keys = implode("\n", $keys);

    $script .= <<<script

  protected static
    \${$this->getVarName(true)} = array();

  protected
    \$keys = array(),
    \$row = array();

  public static function getFromRow(array \$row)
  {
    \$keys = array();
$keys

    \$key = serialize(\$keys);
    if (!isset(self::\${$this->getVarName(true)}[\$key]))
    {

script;

    if (isset($this->classNameColumn))
    {
      $position = $this->classNameColumn->getPosition() - 1;
      $script .= <<<script
      \${$this->getVarName()} = new \$row[$position];

script;
    }
    else
    {
      $script .= <<<script
      \${$this->getVarName()} = new {$this->getObjectClassName()};

script;
    }

    $script .= <<<script

      \${$this->getVarName()}->keys = \$keys;
      \${$this->getVarName()}->row = \$row;

      \${$this->getVarName()}->new = false;

      self::\${$this->getVarName(true)}[\$key] = \${$this->getVarName()};
    }

    return self::\${$this->getVarName(true)}[\$key];
  }

script;
  }

  protected function addGet(&$script)
  {
    $script .= <<<script

  public static function get(Criteria \$criteria, array \$options = array())
  {
    if (!isset(\$options['connection']))
    {
      \$options['connection'] = Propel::getConnection({$this->getPeerClassName()}::DATABASE_NAME);
    }

    self::addSelectColumns(\$criteria);

    return QubitQuery::createFromCriteria(\$criteria, '{$this->getObjectClassName()}', \$options);
  }

script;
  }

  // The following three functions can disappear from multi table inheritance
  // children once we have late static binding.  Children must have the same
  // primary keys as the base table:
  // http://php.net/manual/en/language.oop5.late-static-bindings.php
  protected function addGetAll(&$script)
  {
    $script .= <<<script

  public static function getAll(array \$options = array())
  {
    return self::get(new Criteria, \$options);
  }

script;
  }

  protected function addGetOne(&$script)
  {
    $script .= <<<script

  public static function getOne(Criteria \$criteria, array \$options = array())
  {
    \$criteria->setLimit(1);

    return self::get(\$criteria, \$options)->__get(0, array('defaultValue' => null));
  }

script;
  }

  // Considder using getOne(), unless we drop that method.  Possibly add LIMIT
  // 1 anyway.
  protected function addRetrieveByPkMethods(&$script)
  {
    $args = array();
    $adds = array();
    foreach ($this->getTable()->getPrimaryKey() as $column)
    {
      $args[] = "\${$this->getColumnVarName($column)}";
      $adds[] = <<<adds
    \$criteria->add({$this->getColumnConstant($column)}, \${$this->getColumnVarName($column)});
adds;
    }
    $args = implode(', ', $args);
    $adds = implode("\n", $adds);

    $script .= <<<script

  public static function {$this->getRetrieveMethodName()}($args, array \$options = array())
  {
    \$criteria = new Criteria;
$adds

    if (1 == count(\$query = self::get(\$criteria, \$options)))
    {
      return \$query[0];
    }
  }

script;
  }

  protected function addUpdateMethods(&$script)
  {
    if (!empty($this->emulateOnDeleteCascade))
    {
      $this->addDoOnDeleteCascade($script);
    }

    if (!empty($this->emulateOnDeleteSetNull))
    {
      $this->addDoOnDeleteSetNull($script);
    }

    $this->addDoDelete($script);
  }

  protected function addDoDelete(&$script)
  {
    $script .= <<<script

  public static function doDelete(Criteria \$criteria, \$connection = null)
  {
    if (!isset(\$connection))
    {
      \$connection = Propel::getConnection();
    }

    \$affectedRows = 0;

script;

    if (!empty($this->emulateOnDeleteCascade))
    {
      $script .= <<<script

    \$affectedRows += self::doOnDeleteCascade(\$criteria, \$connection);

script;
    }

    if (!empty($this->emulateOnDeleteSetNull))
    {
      $script .= <<<script

    \$affectedRows += self::doOnDeleteSetNull(\$criteria, \$connection);

script;
    }

    $script .= <<<script

    \$affectedRows += $this->basePeerClassName::doDelete(\$criteria, \$connection);

    return \$affectedRows;
  }

script;
  }

  protected function addAddOrderByPreorder(&$script)
  {
    $script .= <<<script

  public static function addOrderByPreorder(Criteria \$criteria, \$order = Criteria::ASC)
  {
    if (\$order == Criteria::DESC)
    {
      return \$criteria->addDescendingOrderByColumn({$this->getColumnConstant($this->nestedSetLeftColumn)});
    }

    return \$criteria->addAscendingOrderByColumn({$this->getColumnConstant($this->nestedSetLeftColumn)});
  }

script;
  }

  protected function addAddRootsCriteria(&$script)
  {
    $adds = array();
    foreach ($this->selfFk->getLocalColumns() as $localName)
    {
      $adds[] = <<<script
    \$criteria->add({$this->getColumnConstant($this->getTable()->getColumn($localName))});
script;
    }
    $adds = implode("\n", $adds);

    $script .= <<<script

  public static function addRootsCriteria(Criteria \$criteria)
  {
$adds

    return \$criteria;
  }

script;
  }

  protected function addConstructor(&$script)
  {
    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script

  protected
    \$tables = array();

script;
    }

    $script .= <<<script

  public function __construct()
  {

script;

    if (isset($this->inheritanceFk))
    {
      $script .= <<<script
    parent::__construct();


script;
    }

    if (isset($this->classNameColumn))
    {
      // Bypass __get() because tables are not yet initialized
      $script .= <<<script
    \$this->values['{$this->getColumnVarName($this->classNameColumn)}'] = get_class(\$this);


script;
    }

    $script .= <<<script
    \$this->tables[] = Propel::getDatabaseMap({$this->getPeerClassName()}::DATABASE_NAME)->getTable({$this->getPeerClassName()}::TABLE_NAME);
  }

script;
  }

  protected function addColumnMethods(&$script)
  {
    if (isset($this->inheritanceFk) && 1 > count($this->refFks) && !$this->getTable()->getAttribute('isI18n') && (!isset($this->nestedSetLeftColumn) || !isset($this->nestedSetRightColumn)))
    {
      return;
    }

    // TODO Be more conservative about adding $refFkValues, and change the name
    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script

  protected
    \$values = array(),
    \$refFkValues = array();

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $adds = array();
      foreach ($this->getTable()->getPrimaryKey() as $column)
      {
        $adds[] = <<<adds
      \$criteria->add({$this->getColumnConstant($column)}, \$this->{$this->getColumnVarName($column)});
adds;
      }
      $adds = implode("\n", $adds);

      $script .= <<<script

  protected function rowOffsetGet(\$name, \$offset, \$options)
  {
    if (empty(\$options['clean']) && array_key_exists(\$name, \$this->values))
    {
      return \$this->values[\$name];
    }

    if (array_key_exists(\$name, \$this->keys))
    {
      return \$this->keys[\$name];
    }

    if (!array_key_exists(\$offset, \$this->row))
    {
      if (\$this->new)
      {
        return;
      }

      if (!isset(\$options['connection']))
      {
        \$options['connection'] = Propel::getConnection({$this->getPeerClassName()}::DATABASE_NAME);
      }

      \$criteria = new Criteria;
$adds

      call_user_func(array(get_class(\$this), 'addSelectColumns'), \$criteria);

      \$statement = $this->basePeerClassName::doSelect(\$criteria, \$options['connection']);
      \$this->row = \$statement->fetch();
    }

    return \$this->row[\$offset];
  }

script;
    }

    $script .= <<<script

  public function __isset(\$name)
  {
    \$args = func_get_args();


script;

    if (!isset($this->inheritanceFk) || $this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script
    \$options = array();
    if (1 < count(\$args))
    {
      \$options = \$args[1];
    }


script;
    }

    if (isset($this->inheritanceFk))
    {
      $script .= <<<script
    try
    {
      return call_user_func_array(array(\$this, '$this->baseClassName::__isset'), \$args);
    }
    catch (sfException \$e)
    {
    }

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script
    \$offset = 0;
    foreach (\$this->tables as \$table)
    {
      foreach (\$table->getColumns() as \$column)
      {
        if (\$name == \$column->getPhpName())
        {
          return null !== \$this->rowOffsetGet(\$name, \$offset, \$options);
        }

        if ("{\$name}Id" == \$column->getPhpName())
        {
          return null !== \$this->rowOffsetGet("{\$name}Id", \$offset, \$options);
        }

        \$offset++;
      }
    }

script;
    }

    foreach ($this->refFks as $refFk)
    {
      $script .= <<<script

    if ('{$this->getRefFkPhpNameAffix($refFk, true)}' == \$name)
    {
      return true;
    }

script;
    }

    if ($this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script

    try
    {
      if (!\$value = call_user_func_array(array(\$this->getCurrent{$this->getRefFkPhpNameAffix($this->i18nFk)}(\$options), '__isset'), \$args) && !empty(\$options['cultureFallback']))
      {
        return call_user_func_array(array(\$this->getCurrent{$this->getRefFkPhpNameAffix($this->i18nFk)}(array('sourceCulture' => true) + \$options), '__isset'), \$args);
      }

      return \$value;
    }
    catch (sfException \$e)
    {
    }

script;
    }

    if (isset($this->nestedSetLeftColumn) && isset($this->nestedSetRightColumn))
    {
      $script .= <<<script

    if ('ancestors' == \$name)
    {
      return true;
    }

    if ('descendants' == \$name)
    {
      return true;
    }

script;
    }

    $script .= <<<script

    throw new sfException("Unknown record property \"\$name\" on \"".get_class(\$this).'"');
  }

script;

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script

  public function offsetExists(\$offset)
  {
    \$args = func_get_args();

    return call_user_func_array(array(\$this, '__isset'), \$args);
  }

script;
    }

    $script .= <<<script

  public function __get(\$name)
  {
    \$args = func_get_args();


script;

    if (!isset($this->inheritanceFk) || 0 < count($this->refFks) || $this->getTable()->getAttribute('isI18n') || isset($this->nestedSetLeftColumn) && isset($this->nestedSetRightColumn))
    {
      $script .= <<<script
    \$options = array();
    if (1 < count(\$args))
    {
      \$options = \$args[1];
    }


script;
    }

    if (isset($this->inheritanceFk))
    {
      $script .= <<<script
    try
    {
      return call_user_func_array(array(\$this, '$this->baseClassName::__get'), \$args);
    }
    catch (sfException \$e)
    {
    }

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script
    \$offset = 0;
    foreach (\$this->tables as \$table)
    {
      foreach (\$table->getColumns() as \$column)
      {
        if (\$name == \$column->getPhpName())
        {
          return \$this->rowOffsetGet(\$name, \$offset, \$options);
        }

        if ("{\$name}Id" == \$column->getPhpName())
        {
          \$relatedTable = \$column->getTable()->getDatabaseMap()->getTable(\$column->getRelatedTableName());

          return call_user_func(array(\$relatedTable->getClassName(), 'getBy'.ucfirst(\$relatedTable->getColumn(\$column->getRelatedColumnName())->getPhpName())), \$this->rowOffsetGet("{\$name}Id", \$offset, \$options));
        }

        \$offset++;
      }
    }

script;
    }

    foreach ($this->refFks as $refFk)
    {
      $args = array();
      $conds = array();
      foreach ($refFk->getLocalForeignMapping() as $localName => $foreignName)
      {
        $args[] = "\$this->$foreignName";
        $conds[] = "!isset(\$this->$foreignName)";
      }
      $args = implode(', ', $args);
      $conds = implode(' || ', $conds);

      $script .= <<<script

    if ('{$this->getRefFkPhpNameAffix($refFk, true)}' == \$name)
    {
      if (!isset(\$this->refFkValues['{$this->getRefFkCollVarName($refFk)}']))
      {
        if ($conds)
        {
          \$this->refFkValues['{$this->getRefFkCollVarName($refFk)}'] = QubitQuery::create();
        }
        else
        {
          \$this->refFkValues['{$this->getRefFkCollVarName($refFk)}'] = self::get{$this->getRefFkPhpNameAffix($refFk, true)}ById($args, array('self' => \$this) + \$options);
        }
      }

      return \$this->refFkValues['{$this->getRefFkCollVarName($refFk)}'];
    }

script;
    }

    if ($this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script

    try
    {
      if (1 > strlen(\$value = call_user_func_array(array(\$this->getCurrent{$this->getRefFkPhpNameAffix($this->i18nFk)}(\$options), '__get'), \$args)) && !empty(\$options['cultureFallback']))
      {
        return call_user_func_array(array(\$this->getCurrent{$this->getRefFkPhpNameAffix($this->i18nFk)}(array('sourceCulture' => true) + \$options), '__get'), \$args);
      }

      return \$value;
    }
    catch (sfException \$e)
    {
    }

script;
    }

    if (isset($this->nestedSetLeftColumn) && isset($this->nestedSetRightColumn))
    {
      $script .= <<<script

    if ('ancestors' == \$name)
    {
      if (!isset(\$this->values['ancestors']))
      {
        if (\$this->new)
        {
          \$this->values['ancestors'] = QubitQuery::create(array('self' => \$this) + \$options);
        }
        else
        {
          \$criteria = new Criteria;
          \$this->addAncestorsCriteria(\$criteria);
          \$this->addOrderByPreorder(\$criteria);
          \$this->values['ancestors'] = self::get(\$criteria, array('self' => \$this) + \$options);
        }
      }

      return \$this->values['ancestors'];
    }

    if ('descendants' == \$name)
    {
      if (!isset(\$this->values['descendants']))
      {
        if (\$this->new)
        {
          \$this->values['descendants'] = QubitQuery::create(array('self' => \$this) + \$options);
        }
        else
        {
          \$criteria = new Criteria;
          \$this->addDescendantsCriteria(\$criteria);
          \$this->addOrderByPreorder(\$criteria);
          \$this->values['descendants'] = self::get(\$criteria, array('self' => \$this) + \$options);
        }
      }

      return \$this->values['descendants'];
    }

script;
    }

    $script .= <<<script

    throw new sfException("Unknown record property \"\$name\" on \"".get_class(\$this).'"');
  }

script;

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script

  public function offsetGet(\$offset)
  {
    \$args = func_get_args();

    return call_user_func_array(array(\$this, '__get'), \$args);
  }

script;
    }

    if (!isset($this->inheritanceFk) || $this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script

  public function __set(\$name, \$value)
  {
    \$args = func_get_args();

    \$options = array();
    if (2 < count(\$args))
    {
      \$options = \$args[2];
    }


script;
    }

    if (isset($this->inheritanceFk) && $this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script
    call_user_func_array(array(\$this, '$this->baseClassName::__set'), \$args);

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script
    \$offset = 0;
    foreach (\$this->tables as \$table)
    {
      foreach (\$table->getColumns() as \$column)
      {
        // Foreign key column name
        \$nameId = \$name.'Id';

        // Set local column values
        if (\$name === \$column->getPhpName())
        {
          \$this->values[\$name] = \$value;
        }

        // If this is a foreign key column then get primary key from related table
        else if (\$nameId === \$column->getPhpName())
        {
          if(!empty(\$value))
          {
            \$relatedTable = \$column->getTable()->getDatabaseMap()->getTable(\$column->getRelatedTableName());

            \$this->values[\$nameId] = \$value->__get(\$relatedTable->getColumn(\$column->getRelatedColumnName())->getPhpName(), \$options);
          }
          else
          {
            // If \$value is null, then don't try and fetch related object for primary key
            \$this->values[\$nameId] = null;
          }
        }

        \$offset++;
      }
    }

script;
    }

    if ($this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script

    call_user_func_array(array(\$this->getCurrent{$this->getRefFkPhpNameAffix($this->i18nFk)}(\$options), '__set'), \$args);

script;
    }

    if (!isset($this->inheritanceFk) || $this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script

    return \$this;
  }

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script

  public function offsetSet(\$offset, \$value)
  {
    \$args = func_get_args();

    return call_user_func_array(array(\$this, '__set'), \$args);
  }

script;
    }

    if (!isset($this->inheritanceFk) || $this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script

  public function __unset(\$name)
  {

script;
    }

    if ($this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script
    \$args = func_get_args();

    \$options = array();
    if (1 < count(\$args))
    {
      \$options = \$args[1];
    }


script;
    }

    if (isset($this->inheritanceFk) && $this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script
    call_user_func_array(array(\$this, '$this->baseClassName::__unset'), \$args);

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script
    \$offset = 0;
    foreach (\$this->tables as \$table)
    {
      foreach (\$table->getColumns() as \$column)
      {
        if (\$name == \$column->getPhpName())
        {
          \$this->values[\$name] = null;
        }

        if ("{\$name}Id" == \$column->getPhpName())
        {
          \$this->values["{\$name}Id"] = null;
        }

        \$offset++;
      }
    }

script;
    }

    if ($this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script

    call_user_func_array(array(\$this->getCurrent{$this->getRefFkPhpNameAffix($this->i18nFk)}(\$options), '__unset'), \$args);

script;
    }

    if (!isset($this->inheritanceFk) || $this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script

    return \$this;
  }

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script

  public function offsetUnset(\$offset)
  {
    \$args = func_get_args();

    return call_user_func_array(array(\$this, '__unset'), \$args);
  }

script;
    }

    if (!isset($this->inheritanceFk) || $this->getTable()->getAttribute('isI18n'))
    {
      $script .= <<<script

  public function clear()
  {

script;

      if ($this->getTable()->getAttribute('isI18n'))
      {
        $foreignPeerBuilder = self::getNewPeerBuilder($this->i18nFk->getTable());
        $script .= <<<script
    foreach (\$this->{$this->getRefFkCollVarName($this->i18nFk)} as \${$foreignPeerBuilder->getVarName()})
    {
      \${$foreignPeerBuilder->getVarName()}->clear();
    }


script;
      }

      if (isset($this->inheritanceFk))
      {
        $script .= <<<script
    return parent::clear();
  }

script;
      }

      if (!isset($this->inheritanceFk))
      {
        $script .= <<<script
    \$this->row = \$this->values = array();

    return \$this;
  }

script;
      }
    }
  }

  protected function addNew(&$script)
  {
    $script .= <<<script

  protected
    \$new = true;

script;
  }

  protected function addDeleted(&$script)
  {
    $script .= <<<script

  protected
    \$deleted = false;

script;
  }

  protected function addManipulationMethods(&$script)
  {
    $this->addSave($script);
    $this->addInsert($script);
    $this->addUpdate($script);
    $this->addDelete($script);
  }

  protected function addSave(&$script)
  {
    if (isset($this->inheritanceFk) && !$this->getTable()->getAttribute('isI18n'))
    {
      return;
    }

    $script .= <<<script

  public function save(\$connection = null)
  {

script;

    if (isset($this->inheritanceFk))
    {
      $script .= <<<script
    parent::save(\$connection);

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script
    if (\$this->deleted)
    {
      throw new PropelException('You cannot save an object that has been deleted.');
    }

    if (\$this->new)
    {
      \$this->insert(\$connection);
    }
    else
    {
      \$this->update(\$connection);
    }

    \$offset = 0;
    foreach (\$this->tables as \$table)
    {
      foreach (\$table->getColumns() as \$column)
      {
        if (array_key_exists(\$column->getPhpName(), \$this->values))
        {
          \$this->row[\$offset] = \$this->values[\$column->getPhpName()];
        }

        if (\$this->new && \$column->isPrimaryKey())
        {
          \$this->keys[\$column->getPhpName()] = \$this->values[\$column->getPhpName()];
        }

        \$offset++;
      }
    }

    \$this->new = false;
    \$this->values = array();

script;
    }

    if ($this->getTable()->getAttribute('isI18n'))
    {
      $foreignPeerBuilder = self::getNewPeerBuilder($this->i18nFk->getTable());

      $sets = array();
      foreach ($this->i18nFk->getLocalForeignMapping() as $localName => $foreignName)
      {
        $sets[] = <<<script
      \${$foreignPeerBuilder->getVarName()}->{$this->i18nFk->getTable()->getColumn($localName)->getPhpName()} = \$this->{$this->getColumnVarName($this->getTable()->getColumn($foreignName))};
script;
      }
      $sets = implode("\n", $sets);

      $script .= <<<script

    foreach (\$this->{$this->getRefFkCollVarName($this->i18nFk)} as \${$foreignPeerBuilder->getVarName()})
    {
$sets

      \${$foreignPeerBuilder->getVarName()}->save(\$connection);
    }

script;
    }

    $script .= <<<script

    return \$this;
  }

script;
  }

  protected function addInsert(&$script)
  {
    if (isset($this->inheritanceFk) && (!isset($this->nestedSetLeftColumn) || !isset($this->nestedSetRightColumn)))
    {
      return;
    }

    $script .= <<<script

  protected function param(\$column)
  {
    \$value = \$this->values[\$column->getPhpName()];

    // Convert to DateTime or SQL zero special case
    if (isset(\$value) && \$column->isTemporal() && !\$value instanceof DateTime)
    {
      // Year only: one or more digits.  Convert to SQL zero special case
      if (preg_match('/^\d+$/', \$value))
      {
        \$value .= '-0-0';
      }

      // Year and month only: one or more digits, plus separator, plus
      // one or more digits.  Convert to SQL zero special case
      else if (preg_match('/^\d+[-\/]\d+$/', \$value))
      {
        \$value .= '-0';
      }

      // Convert to DateTime if not SQL zero special case: year plus
      // separator plus zero to twelve (possibly zero padded) plus
      // separator plus one or more zeros
      if (!preg_match('/^\d+[-\/]0*(?:1[0-2]|\d)[-\/]0+$/', \$value))
      {
        try
        {
          \$value = new DateTime(\$value);
        }
        catch (Exception \$e)
        {
          return null;
        }
      }
    }

    return \$value;
  }

  protected function insert(\$connection = null)
  {
script;

    if (isset($this->nestedSetLeftColumn) && isset($this->nestedSetRightColumn))
    {
      $script .= <<<script

    \$this->updateNestedSet(\$connection);

script;
    }

    if (isset($this->inheritanceFk))
    {
      $script .= <<<script

    parent::insert(\$connection);

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script

    if (!isset(\$connection))
    {
      \$connection = Propel::getConnection();
    }

    \$offset = 0;
    foreach (\$this->tables as \$table)
    {
      \$criteria = new Criteria;
      foreach (\$table->getColumns() as \$column)
      {
        if (!array_key_exists(\$column->getPhpName(), \$this->values))
        {
          if ('createdAt' == \$column->getPhpName() || 'updatedAt' == \$column->getPhpName())
          {
            \$this->values[\$column->getPhpName()] = new DateTime;
          }

          if ('sourceCulture' == \$column->getPhpName())
          {
            \$this->values['sourceCulture'] = sfPropel::getDefaultCulture();
          }
        }

        if (array_key_exists(\$column->getPhpName(), \$this->values))
        {
          if (null !== \$param = \$this->param(\$column))
          {
            \$criteria->add(\$column->getFullyQualifiedName(), \$param);
          }
        }

        \$offset++;
      }

      if (null !== \$id = $this->basePeerClassName::doInsert(\$criteria, \$connection))
      {
        // Guess that the first primary key of the first table is auto
        // incremented
        if (\$this->tables[0] == \$table)
        {
          \$columns = \$table->getPrimaryKeyColumns();
          \$this->values[\$columns[0]->getPhpName()] = \$this->keys[\$columns[0]->getPhpName()] = \$id;
        }
      }
    }

script;
    }

    $script .= <<<script

    return \$this;
  }

script;
  }

  protected function addUpdate(&$script)
  {
    if (isset($this->inheritanceFk) && (!isset($this->nestedSetLeftColumn) || !isset($this->nestedSetRightColumn)))
    {
      return;
    }

    $script .= <<<script

  protected function update(\$connection = null)
  {
script;

    // TODO Only update nested set if the self foreign key has changed
    if (isset($this->nestedSetLeftColumn) && isset($this->nestedSetRightColumn))
    {
      $script .= <<<script

    // Update nested set keys only if parent id has changed
    if (isset(\$this->values['parentId']))
    {
      // Get the "original" parentId before any updates
      \$offset = 0;
      \$originalParentId = null;
      foreach (\$this->tables as \$table)
      {
        foreach (\$table->getColumns() as \$column)
        {
          if ('parentId' == \$column->getPhpName())
          {
            \$originalParentId = \$this->row[\$offset];
            break;
          }
          \$offset++;
        }
      }

      // If updated value of parentId is different then original value,
      // update the nested set
      if (\$originalParentId != \$this->values['parentId'])
      {
        \$this->updateNestedSet(\$connection);
      }
    }

script;
    }

    if (isset($this->inheritanceFk))
    {
      $script .= <<<script

    parent::update(\$connection);

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $script .= <<<script

    if (!isset(\$connection))
    {
      \$connection = Propel::getConnection();
    }

    \$offset = 0;
    foreach (\$this->tables as \$table)
    {
      \$criteria = new Criteria;
      \$selectCriteria = new Criteria;
      foreach (\$table->getColumns() as \$column)
      {
        if (!array_key_exists(\$column->getPhpName(), \$this->values))
        {
          if ('updatedAt' == \$column->getPhpName())
          {
            \$this->values['updatedAt'] = new DateTime;
          }
        }

        if (array_key_exists(\$column->getPhpName(), \$this->values))
        {
          if ('serialNumber' == \$column->getPhpName())
          {
            \$selectCriteria->add(\$column->getFullyQualifiedName(), \$this->values[\$column->getPhpName()]++);
          }

          \$criteria->add(\$column->getFullyQualifiedName(), \$this->param(\$column));
        }

        if (\$column->isPrimaryKey())
        {
          \$selectCriteria->add(\$column->getFullyQualifiedName(), \$this->keys[\$column->getPhpName()]);
        }

        \$offset++;
      }

      if (0 < \$criteria->size())
      {
        $this->basePeerClassName::doUpdate(\$selectCriteria, \$criteria, \$connection);
      }
    }

script;
    }

    $script .= <<<script

    return \$this;
  }

script;
  }

  protected function addDelete(&$script)
  {
    // If this class is not the base of an object hierarchy and it does not
    // contain any nested set behavior, then this method does nothing except
    // call the parent class' implementation.
    if (isset($this->inheritanceFk) && (!isset($this->nestedSetLeftColumn) || !isset($this->nestedSetRightColumn)))
    {
      return;
    }

    $script .= <<<script

  public function delete(\$connection = null)
  {
    if (\$this->deleted)
    {
      throw new PropelException('This object has already been deleted.');
    }

script;

    if (isset($this->nestedSetLeftColumn) && isset($this->nestedSetRightColumn))
    {
      $script .= <<<script

    \$this->clear();
    if (!property_exists(\$this, 'disableNestedSetUpdating') || \$this->disableNestedSetUpdating !== true)
    {
      \$this->deleteFromNestedSet(\$connection);
    }

script;
    }

    if (isset($this->inheritanceFk))
    {
      $script .= <<<script

    parent::delete(\$connection);

script;
    }

    if (!isset($this->inheritanceFk))
    {
      $adds = array();
      foreach ($this->getTable()->getPrimaryKey() as $column)
      {
        $adds[] = <<<adds
    \$criteria->add({$this->getColumnConstant($column)}, \$this->{$this->getColumnVarName($column)});
adds;
      }
      $adds = implode("\n", $adds);

      $script .= <<<script

    \$criteria = new Criteria;
$adds

    self::doDelete(\$criteria, \$connection);

    \$this->deleted = true;

script;
    }

    $script .= <<<script

    return \$this;
  }

script;
  }

  public function getFkPhpName(ForeignKey $fk)
  {
    if (count($localNames = $fk->getLocalColumns()) < 2 && '_id' == substr($localNames[0], -3))
    {
      return substr($this->getTable()->getColumn($localNames[0])->getPhpName(), 0, -2);
    }

    return $this->getFkPhpNameAffix($fk);
  }

  public function getFkVarName(ForeignKey $fk)
  {
    return strtolower(substr($phpName = $this->getFkPhpName($fk), 0, 1)).substr($phpName, 1);
  }

  public function getRefFkCollVarName(ForeignKey $refFk)
  {
    return strtolower(substr($phpName = $this->getRefFkPhpNameAffix($refFk, true), 0, 1)).substr($phpName, 1);
  }

  protected function addFkMethods(&$script)
  {
    foreach ($this->getTable()->getForeignKeys() as $fk)
    {
      if ($fk == $this->inheritanceFk)
      {
        continue;
      }

      $this->addFkAddJoinCriteria($script, $fk);
    }
  }

  protected function addFkAddJoinCriteria(&$script, ForeignKey $fk)
  {
    $foreignPeerBuilder = self::getNewPeerBuilder($this->getForeignTable($fk));

    $adds = array();
    foreach ($fk->getLocalForeignMapping() as $localName => $foreignName)
    {
      $adds[] = <<<adds
    \$criteria->addJoin({$this->getColumnConstant($this->getTable()->getColumn($localName))}, {$foreignPeerBuilder->getColumnConstant($this->getForeignTable($fk)->getColumn($foreignName))});
adds;
    }
    $adds = implode("\n", $adds);

    $script .= <<<script

  public static function addJoin{$this->getFkPhpName($fk)}Criteria(Criteria \$criteria)
  {
$adds

    return \$criteria;
  }

script;
  }

  protected function addRefFkMethods(&$script)
  {
    foreach ($this->refFks as $refFk)
    {
      $this->addRefFkAddCriteriaById($script, $refFk);
      $this->addRefFkGetById($script, $refFk);
      $this->addRefFkAddCriteria($script, $refFk);
    }
  }

  protected function addRefFkAddCriteriaById(&$script, ForeignKey $refFk)
  {
    $foreignPeerBuilder = self::getNewPeerBuilder($refFk->getTable());

    $args = array();
    $adds = array();
    foreach ($refFk->getLocalForeignMapping() as $localName => $foreignName)
    {
      $args[] = "$$foreignName";
      $adds[] = <<<adds
    \$criteria->add({$foreignPeerBuilder->getColumnConstant($refFk->getTable()->getColumn($localName))}, \$$foreignName);
adds;
    }
    $args = implode(', ', $args);
    $adds = implode("\n", $adds);

    $script .= <<<script

  public static function add{$this->getRefFkPhpNameAffix($refFk, true)}CriteriaById(Criteria \$criteria, $args)
  {
$adds

    return \$criteria;
  }

script;
  }

  protected function addRefFkGetById(&$script, ForeignKey $refFk)
  {
    $foreignPeerBuilder = self::getNewPeerBuilder($refFk->getTable());

    $args = array();
    foreach ($refFk->getForeignColumns() as $foreignName)
    {
      $args[] = "$$foreignName";
    }
    $args = implode(', ', $args);

    $script .= <<<script

  public static function get{$this->getRefFkPhpNameAffix($refFk, true)}ById($args, array \$options = array())
  {
    \$criteria = new Criteria;
    self::add{$this->getRefFkPhpNameAffix($refFk, true)}CriteriaById(\$criteria, $args);

    return {$foreignPeerBuilder->getPeerClassName()}::get(\$criteria, \$options);
  }

script;
  }

  protected function addRefFkAddCriteria(&$script, ForeignKey $refFk)
  {
    $args = array();
    foreach ($refFk->getForeignColumns() as $foreignName)
    {
      $args[] = "\$this->$foreignName";
    }
    $args = implode(', ', $args);

    $script .= <<<script

  public function add{$this->getRefFkPhpNameAffix($refFk, true)}Criteria(Criteria \$criteria)
  {
    return self::add{$this->getRefFkPhpNameAffix($refFk, true)}CriteriaById(\$criteria, $args);
  }

script;
  }

  protected function addI18nMethods(&$script)
  {
    $foreignPeerBuilder = self::getNewPeerBuilder($this->i18nFk->getTable());

    $script .= <<<script

  public function getCurrent{$this->getRefFkPhpNameAffix($this->i18nFk)}(array \$options = array())
  {
    if (!empty(\$options['sourceCulture']))
    {
      \$options['culture'] = \$this->{$this->getColumnVarName($this->sourceCultureColumn)};
    }

    if (!isset(\$options['culture']))
    {
      \$options['culture'] = sfPropel::getDefaultCulture();
    }

    \${$this->getRefFkCollVarName($this->i18nFk)} = \$this->{$this->getRefFkCollVarName($this->i18nFk)}->indexBy('culture');
    if (!isset(\${$this->getRefFkCollVarName($this->i18nFk)}[\$options['culture']]))
    {
      \${$this->getRefFkCollVarName($this->i18nFk)}[\$options['culture']] = new {$foreignPeerBuilder->getObjectClassName()};
    }

    return \${$this->getRefFkCollVarName($this->i18nFk)}[\$options['culture']];
  }

script;
  }

  protected function addHasChildren(&$script)
  {
    $script .= <<<script

  public function hasChildren()
  {
    return (\$this->{$this->getColumnVarName($this->nestedSetRightColumn)} - \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)}) > 1;
  }

script;
  }

  protected function addAddAncestorsCriteria(&$script)
  {
    $script .= <<<script

  public function addAncestorsCriteria(Criteria \$criteria)
  {
    return \$criteria->add({$this->getColumnConstant($this->nestedSetLeftColumn)}, \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)}, Criteria::LESS_THAN)->add({$this->getColumnConstant($this->nestedSetRightColumn)}, \$this->{$this->getColumnVarName($this->nestedSetRightColumn)}, Criteria::GREATER_THAN);
  }

script;
  }

  protected function addAddDescendantsCriteria(&$script)
  {
    $script .= <<<script

  public function addDescendantsCriteria(Criteria \$criteria)
  {
    return \$criteria->add({$this->getColumnConstant($this->nestedSetLeftColumn)}, \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)}, Criteria::GREATER_THAN)->add({$this->getColumnConstant($this->nestedSetRightColumn)}, \$this->{$this->getColumnVarName($this->nestedSetRightColumn)}, Criteria::LESS_THAN);
  }

script;
  }

  protected function addUpdateNestedSet(&$script)
  {
    $script .= <<<script

  protected function updateNestedSet(\$connection = null)
  {
// HACK Try to prevent modifying left and right values anywhere except in this
// method.  Perhaps it would be more logical to use protected visibility for
// these values?
unset(\$this->values['{$this->getColumnVarName($this->nestedSetLeftColumn)}']);
unset(\$this->values['{$this->getColumnVarName($this->nestedSetRightColumn)}']);
    if (!isset(\$connection))
    {
      \$connection = Propel::getConnection();
    }

    if (!isset(\$this->{$this->getColumnVarName($this->nestedSetLeftColumn)}) || !isset(\$this->{$this->getColumnVarName($this->nestedSetRightColumn)}))
    {
      \$delta = 2;
    }
    else
    {
      \$delta = \$this->{$this->getColumnVarName($this->nestedSetRightColumn)} - \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)} + 1;
    }

    if (null === \${$this->getFkVarName($this->selfFk)} = \$this->__get('{$this->getFkPhpName($this->selfFk)}', array('connection' => \$connection)))
    {
      \$statement = \$connection->prepare('
        SELECT MAX('.{$this->getColumnConstant($this->nestedSetRightColumn)}.')
        FROM '.{$this->getPeerClassName()}::TABLE_NAME);
      \$statement->execute();
      \$row = \$statement->fetch();
      \$max = \$row[0];

      if (!isset(\$this->{$this->getColumnVarName($this->nestedSetLeftColumn)}) || !isset(\$this->{$this->getColumnVarName($this->nestedSetRightColumn)}))
      {
        \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)} = \$max + 1;
        \$this->{$this->getColumnVarName($this->nestedSetRightColumn)} = \$max + 2;

        return \$this;
      }

      \$shift = \$max + 1 - \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)};
    }
    else
    {
      \$parent->clear();

      if (isset(\$this->{$this->getColumnVarName($this->nestedSetLeftColumn)}) && isset(\$this->{$this->getColumnVarName($this->nestedSetRightColumn)}) && \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)} <= \${$this->getFkVarName($this->selfFk)}->{$this->getColumnVarName($this->nestedSetLeftColumn)} && \$this->{$this->getColumnVarName($this->nestedSetRightColumn)} >= \${$this->getFkVarName($this->selfFk)}->{$this->getColumnVarName($this->nestedSetRightColumn)})
      {
        throw new PropelException('An object cannot be a descendant of itself.');
      }

      \$statement = \$connection->prepare('
        UPDATE '.{$this->getPeerClassName()}::TABLE_NAME.'
        SET '.{$this->getColumnConstant($this->nestedSetLeftColumn)}.' = '.{$this->getColumnConstant($this->nestedSetLeftColumn)}.' + ?
        WHERE '.{$this->getColumnConstant($this->nestedSetLeftColumn)}.' >= ?');
      \$statement->execute(array(\$delta, \${$this->getFkVarName($this->selfFk)}->{$this->getColumnVarName($this->nestedSetRightColumn)}));

      \$statement = \$connection->prepare('
        UPDATE '.{$this->getPeerClassName()}::TABLE_NAME.'
        SET '.{$this->getColumnConstant($this->nestedSetRightColumn)}.' = '.{$this->getColumnConstant($this->nestedSetRightColumn)}.' + ?
        WHERE '.{$this->getColumnConstant($this->nestedSetRightColumn)}.' >= ?');
      \$statement->execute(array(\$delta, \${$this->getFkVarName($this->selfFk)}->{$this->getColumnVarName($this->nestedSetRightColumn)}));

      if (!isset(\$this->{$this->getColumnVarName($this->nestedSetLeftColumn)}) || !isset(\$this->{$this->getColumnVarName($this->nestedSetRightColumn)}))
      {
        \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)} = \${$this->getFkVarName($this->selfFk)}->{$this->getColumnVarName($this->nestedSetRightColumn)};
        \$this->{$this->getColumnVarName($this->nestedSetRightColumn)} = \${$this->getFkVarName($this->selfFk)}->{$this->getColumnVarName($this->nestedSetRightColumn)} + 1;
        \${$this->getFkVarName($this->selfFk)}->{$this->getColumnVarName($this->nestedSetRightColumn)} += 2;

        return \$this;
      }

      if (\$this->{$this->getColumnVarName($this->nestedSetLeftColumn)} > \${$this->getFkVarName($this->selfFk)}->{$this->getColumnVarName($this->nestedSetRightColumn)})
      {
        \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)} += \$delta;
        \$this->{$this->getColumnVarName($this->nestedSetRightColumn)} += \$delta;
      }

      \$shift = \${$this->getFkVarName($this->selfFk)}->{$this->getColumnVarName($this->nestedSetRightColumn)} - \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)};
    }

    \$statement = \$connection->prepare('
      UPDATE '.{$this->getPeerClassName()}::TABLE_NAME.'
      SET '.{$this->getColumnConstant($this->nestedSetLeftColumn)}.' = '.{$this->getColumnConstant($this->nestedSetLeftColumn)}.' + ?, '.{$this->getColumnConstant($this->nestedSetRightColumn)}.' = '.{$this->getColumnConstant($this->nestedSetRightColumn)}.' + ?
      WHERE '.{$this->getColumnConstant($this->nestedSetLeftColumn)}.' >= ?
      AND '.{$this->getColumnConstant($this->nestedSetRightColumn)}.' <= ?');
    \$statement->execute(array(\$shift, \$shift, \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)}, \$this->{$this->getColumnVarName($this->nestedSetRightColumn)}));

    \$this->deleteFromNestedSet(\$connection);

    if (\$shift > 0)
    {
      \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)} -= \$delta;
      \$this->{$this->getColumnVarName($this->nestedSetRightColumn)} -= \$delta;
    }

    \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)} += \$shift;
    \$this->{$this->getColumnVarName($this->nestedSetRightColumn)} += \$shift;

    return \$this;
  }

script;
  }

  protected function addDeleteFromNestedSet(&$script)
  {
    $script .= <<<script

  protected function deleteFromNestedSet(\$connection = null)
  {
    if (!isset(\$connection))
    {
      \$connection = Propel::getConnection();
    }

    \$delta = \$this->{$this->getColumnVarName($this->nestedSetRightColumn)} - \$this->{$this->getColumnVarName($this->nestedSetLeftColumn)} + 1;

    \$statement = \$connection->prepare('
      UPDATE '.{$this->getPeerClassName()}::TABLE_NAME.'
      SET '.{$this->getColumnConstant($this->nestedSetLeftColumn)}.' = '.{$this->getColumnConstant($this->nestedSetLeftColumn)}.' - ?
      WHERE '.{$this->getColumnConstant($this->nestedSetLeftColumn)}.' >= ?');
    \$statement->execute(array(\$delta, \$this->{$this->getColumnVarName($this->nestedSetRightColumn)}));

    \$statement = \$connection->prepare('
      UPDATE '.{$this->getPeerClassName()}::TABLE_NAME.'
      SET '.{$this->getColumnConstant($this->nestedSetRightColumn)}.' = '.{$this->getColumnConstant($this->nestedSetRightColumn)}.' - ?
      WHERE '.{$this->getColumnConstant($this->nestedSetRightColumn)}.' >= ?');
    \$statement->execute(array(\$delta, \$this->{$this->getColumnVarName($this->nestedSetRightColumn)}));

    return \$this;
  }

script;
  }

  protected function addIsInTree(&$script)
  {
    $script .= <<<script

  public function isInTree()
  {
    return \$this->lft > 0 && \$this->rgt > \$this->lft;
  }

script;
  }

  protected function addIsRoot(&$script)
  {
    $script .= <<<script

  public function isRoot()
  {
      return \$this->isInTree() && \$this->lft == 1;
  }

script;
  }

  protected function addIsDescendantOf(&$script)
  {
    $script .= <<<script

  public function isDescendantOf(\$parent)
  {
    return \$this->isInTree() && \$this->lft > \$parent->lft && \$this->rgt < \$parent->rgt;
  }

script;
  }

  protected function addMoveToFirstChildOf(&$script)
  {
    $script .= <<<script

  public function moveToFirstChildOf(\$parent, PropelPDO \$con = null)
  {
    if (\$parent->isDescendantOf(\$this))
    {
      throw new PropelException('Cannot move a node as child of one of its subtree nodes.');
    }

    \$this->moveSubtreeTo(\$parent->lft + 1, \$con);

    return \$this;
  }

script;
  }

  protected function addMoveToLastChildOf(&$script)
  {
    $script .= <<<script

  public function moveToLastChildOf(\$parent, PropelPDO \$con = null)
  {
    if (\$parent->isDescendantOf(\$this))
    {
      throw new PropelException('Cannot move a node as child of one of its subtree nodes.');
    }

    \$this->moveSubtreeTo(\$parent->rgt, \$con);

    return \$this;
  }

script;
  }

  protected function addMoveToPrevSiblingOf(&$script)
  {
    $script .= <<<script

  public function moveToPrevSiblingOf(\$sibling, PropelPDO \$con = null)
  {
    if (!\$this->isInTree())
    {
      throw new PropelException('This object must be already in the tree to be moved. Use the insertAsPrevSiblingOf() instead.');
    }

    if (\$sibling->isRoot())
    {
      throw new PropelException('Cannot move to previous sibling of a root node.');
    }

    if (\$sibling->isDescendantOf(\$this))
    {
      throw new PropelException('Cannot move a node as sibling of one of its subtree nodes.');
    }

    \$this->moveSubtreeTo(\$sibling->lft, \$con);

    return \$this;
  }

script;
  }

  protected function addMoveToNextSiblingOf(&$script)
  {
    $script .= <<<script

  public function moveToNextSiblingOf(\$sibling, PropelPDO \$con = null)
  {
    if (!\$this->isInTree())
    {
      throw new PropelException('This object must be already in the tree to be moved. Use the insertAsPrevSiblingOf() instead.');
    }

    if (\$sibling->isRoot())
    {
      throw new PropelException('Cannot move to previous sibling of a root node.');
    }

    if (\$sibling->isDescendantOf(\$this))
    {
      throw new PropelException('Cannot move a node as sibling of one of its subtree nodes.');
    }

    \$this->moveSubtreeTo(\$sibling->rgt + 1, \$con);

    return \$this;
  }

script;
  }

  protected function addMoveSubtreeTo(&$script)
  {
    $script .= <<<script

  protected function moveSubtreeTo(\$destLeft, PropelPDO \$con = null)
  {
    \$left  = \$this->lft;
    \$right = \$this->rgt;

    \$treeSize = \$right - \$left +1;

    if (\$con === null)
    {
      \$con = Propel::getConnection();
    }

    \$con->beginTransaction();

    try
    {
      // make room next to the target for the subtree
      self::shiftRLValues(\$treeSize, \$destLeft, null, \$con);

      if (\$left >= \$destLeft) // src was shifted too?
      {
        \$left += \$treeSize;
        \$right += \$treeSize;
      }

      // move the subtree to the target
      self::shiftRLValues(\$destLeft - \$left, \$left, \$right, \$con);

      // remove the empty room at the previous location of the subtree
      self::shiftRLValues(-\$treeSize, \$right + 1, null, \$con);

      // update all loaded nodes
      // self::updateLoadedNodes(null, \$con);

      \$con->commit();
    }
    catch (PropelException \$e)
    {
      \$con->rollback();

      throw \$e;
    }
  }

script;
  }

  protected function addShiftRLValues(&$script)
  {
    $script .= <<<script

  /**
   * Adds \$delta to all L and R values that are >= \$first and <= \$last.
   * '\$delta' can also be negative.
   *
   * @param int \$delta Value to be shifted by, can be negative
   * @param int \$first First node to be shifted
   * @param int \$last Last node to be shifted (optional)
   * @param PropelPDO \$con Connection to use.
   */
  protected function shiftRLValues(\$delta, \$first, \$last = null, PropelPDO \$con = null)
  {
    if (\$con === null)
    {
      \$con = Propel::getConnection();
    }

    // Shift left column values
    \$whereCriteria = new Criteria;
    \$criterion = \$whereCriteria->getNewCriterion({$this->getColumnConstant($this->nestedSetLeftColumn)}, \$first, Criteria::GREATER_EQUAL);
    if (null !== \$last)
    {
      \$criterion->addAnd(\$whereCriteria->getNewCriterion({$this->getColumnConstant($this->nestedSetLeftColumn)}, \$last, Criteria::LESS_EQUAL));
    }
    \$whereCriteria->add(\$criterion);

    \$valuesCriteria = new Criteria;
    \$valuesCriteria->add({$this->getColumnConstant($this->nestedSetLeftColumn)}, array('raw' => {$this->getColumnConstant($this->nestedSetLeftColumn)} . ' + ?', 'value' => \$delta), Criteria::CUSTOM_EQUAL);

    BasePeer::doUpdate(\$whereCriteria, \$valuesCriteria, \$con);

    // Shift right column values
    \$whereCriteria = new Criteria;
    \$criterion = \$whereCriteria->getNewCriterion({$this->getColumnConstant($this->nestedSetRightColumn)}, \$first, Criteria::GREATER_EQUAL);
    if (null !== \$last)
    {
      \$criterion->addAnd(\$whereCriteria->getNewCriterion({$this->getColumnConstant($this->nestedSetRightColumn)}, \$last, Criteria::LESS_EQUAL));
    }
    \$whereCriteria->add(\$criterion);

    \$valuesCriteria = new Criteria;
    \$valuesCriteria->add({$this->getColumnConstant($this->nestedSetRightColumn)}, array('raw' => {$this->getColumnConstant($this->nestedSetRightColumn)} . ' + ?', 'value' => \$delta), Criteria::CUSTOM_EQUAL);

    BasePeer::doUpdate(\$whereCriteria, \$valuesCriteria, \$con);
  }

script;
  }

  protected function addCall(&$script)
  {
    if (isset($this->inheritanceFk))
    {
      return;
    }

    $script .= <<<script

  public function __call(\$name, \$args)
  {
    if ('get' == substr(\$name, 0, 3) || 'set' == substr(\$name, 0, 3))
    {
      \$args = array_merge(array(strtolower(substr(\$name, 3, 1)).substr(\$name, 4)), \$args);

      return call_user_func_array(array(\$this, '__'.substr(\$name, 0, 3)), \$args);
    }

    throw new sfException('Call to undefined method '.get_class(\$this)."::\$name");
  }

script;
  }

  protected function addClassClose(&$script)
  {
    $script .= <<<script
}

script;
  }
}
