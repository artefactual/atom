<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorDoctrineNestedSetLevel is a class that validates the max allowed  
 * depth level for Doctrine nested set objects.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Hugo Hamon <hugo.hamon@sensio.com>
 */
class sfValidatorDoctrineNestedSetLevel extends sfValidatorBase 
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * max_level:    The max depth to test (required integer)
   *  * model:        The model class (required)
   *  * alias:        The alias of the root component used in the query
   *  * query:        A query to use when retrieving objects
   *  * column:       The column name for the where clause statement (use primary key by default)
   *  * level_column: The level column name (use level by default)
   *  * connection:   The Doctrine connection to use (null by default)
   *
   * Available error messages:
   * 
   *  * invalid:        The related object has already the max level value
   *  * invalid_record: Unable to find the related object
   * 
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('model');
    $this->addRequiredOption('max_level');
    $this->addOption('alias', 'a');
    $this->addOption('query', null);
    $this->addOption('column', null);
    $this->addOption('level_column', 'level');
    $this->addOption('connection', null);
    
    $this->addMessage('invalid_record', 'Unable to find the related record');
  }
  
  /**
   * @see sfValidatorBase
   * 
   * @throws sfValidatorError
   */
  protected function doClean($value)
  {
    $level = $this->getObjectLevelValue($value);

    if ($level >= (int) $this->getOption('max_level'))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $level));
    }

    return $value;
  }
  
  /**
   * Returns the level column to use for comparison.
   *
   * The primary key is used by default.
   *
   * @return string The column name
   */
  protected function getWhereColumn()
  {
    $table = $this->getDoctrineTable();
    
    if ($this->getOption('column'))
    {
      return $table->getColumnName($this->getOption('column'));
    }

    $identifier = (array) $table->getIdentifier();
    $columnName = current($identifier);

    return $table->getColumnName($columnName);
  }
  
  /**
   * Returns the level column name
   *
   * @return string The column name
   */
  protected function getLevelColumn()
  {
    $table = $this->getDoctrineTable();
    
    return $table->getColumnName($this->getOption('level_column'));
  }
  
  /**
   * Returns the Doctrine table object
   *
   * @return Doctrine_Table
   */
  protected function getDoctrineTable()
  {
    return Doctrine::getTable($this->getOption('model'));
  }
  
  /**
   * Returns the object level's value
   *
   * @param mixed $value The value of the where column to retrieve the object
   * 
   * @return int The level value of the found object
   * 
   * @throws sfValidatorError
   */
  protected function getObjectLevelValue($value)
  {
    $a = $this->getOption('alias');
    $q = is_null($this->getOption('query')) ? Doctrine_Query::create()->from($this->getOption('model') . ' ' . $a) : $this->getOption('query');
    $q->select($a . '.' . $this->getLevelColumn());
    $q->addWhere($a . '.' . $this->getWhereColumn() . ' = ?', $value);

    $result = $q->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
    
    if (!$result)
    {
      throw new sfValidatorError($this, 'invalid_record', array('value' => $value)); 
    }
    
    return (int) $result[ $this->getLevelColumn() ];
  }
}