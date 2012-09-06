<?php

/**
 * A widget of grouped choices.
 * 
 * @package     sfFormExtraPlugin
 * @subpackage  widget
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfWidgetFormDoctrineChoiceGrouped.class.php 16265 2009-03-12 15:23:41Z Kris.Wallsmith $
 */
class sfWidgetFormDoctrineChoiceGrouped extends sfWidgetFormDoctrineChoice
{
  /**
   * Available options:
   * 
   *  * group_by: The name of the relation to use for grouping
   * 
   * @see sfWidget
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('group_by');

    parent::configure($options, $attributes);
  }

  /**
   * @see sfWidgetFormDoctrineChoice
   */
  public function getChoices()
  {
    if (is_null($this->getOption('table_method')))
    {
      $query = is_null($this->getOption('query')) ? Doctrine::getTable($this->getOption('model'))->createQuery() : $this->getOption('query');

      if ($order = $this->getOption('order_by'))
      {
        $query->addOrderBy($order[0].' '.$order[1]);
      }

      $objects = $query->execute();
    }
    else
    {
      $tableMethod = $this->getOption('table_method');
      $results = Doctrine::getTable($this->getOption('model'))->$tableMethod();

      if ($results instanceof Doctrine_Query)
      {
        $objects = $results->execute();
      }
      else if ($results instanceof Doctrine_Collection)
      {
        $objects = $results;
      }
      else if ($results instanceof Doctrine_Record)
      {
        $objects = new Doctrine_Collection($this->getOption('model'));
        $objects[] = $results;
      }
      else
      {
        $objects = array();
      }
    }

    $choices = array();
    if (false !== $this->getOption('add_empty'))
    {
      $choices[''] = true === $this->getOption('add_empty') ? '' : $this->getOption('add_empty');
    }

    $method = $this->getOption('method');
    $keyMethod = $this->getOption('key_method');
    $groupBy = $this->getOption('group_by');

    foreach ($objects as $object)
    {
      $parent = (string) $object->$groupBy;

      if (!isset($choices[$parent]))
      {
        $choices[$parent] = array();
      }

      $choices[$parent][$object->$keyMethod()] = $object->$method();
    }

    return $choices;
  }
}
