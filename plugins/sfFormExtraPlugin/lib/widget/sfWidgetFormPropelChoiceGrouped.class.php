<?php

/**
 * A widget of grouped choices.
 * 
 * @package     sfFormExtraPlugin
 * @subpackage  widget
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfWidgetFormPropelChoiceGrouped.class.php 16067 2009-03-06 16:57:46Z Kris.Wallsmith $
 */
class sfWidgetFormPropelChoiceGrouped extends sfWidgetFormPropelChoice
{
  /**
   * Available options:
   * 
   *  * group_by_method:  A method on the current model that will return the
   *                      object the widget is grouped by (i.e. getAuthor)
   * 
   * @see sfWidget
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('group_by_method');

    parent::configure($options, $attributes);
  }

  /**
   * @see sfWidgetFormPropelChoice
   */
  public function getChoices()
  {
    $choices = array();
    if (!$this->getOption('expanded') && false !== $this->getOption('add_empty'))
    {
      $choices[''] = true === $this->getOption('add_empty') ? '' : $this->getOption('add_empty');
    }

    $methodKey = $this->getOption('key_method');
    if (!method_exists($this->getOption('model'), $methodKey))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->getOption('model'), $methodKey, __CLASS__));
    }

    $methodValue = $this->getOption('method');
    if (!method_exists($this->getOption('model'), $methodValue))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->getOption('model'), $methodValue, __CLASS__));
    }

    $methodParent = $this->getOption('group_by_method');
    if (!method_exists($this->getOption('model'), $methodParent))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->getOption('model'), $methodParent, __CLASS__));
    }

    $class = constant($this->getOption('model').'::PEER');

    $criteria = is_null($this->getOption('criteria')) ? new Criteria() : clone $this->getOption('criteria');
    if ($order = $this->getOption('order_by'))
    {
      $method = sprintf('add%sOrderByColumn', 0 === strpos(strtoupper($order[1]), 'ASC') ? 'Ascending' : 'Descending');
      $criteria->$method(call_user_func(array($class, 'translateFieldName'), $order[0], BasePeer::TYPE_PHPNAME, BasePeer::TYPE_COLNAME));
    }

    if ('doSelect' == $peerMethod = $this->getOption('peer_method'))
    {
      // apply join method
      $peerMethod = 'doSelectJoin'.substr($this->getOption('group_by_method'), 3);
    }
    $objects = call_user_func(array($class, $peerMethod), $criteria, $this->getOption('connection'));

    foreach ($objects as $object)
    {
      $parent = $object->$methodParent($this->getOption('connection'));
      if (!method_exists($parent, '__toString'))
      {
        throw new RuntimeException(sprintf('Class "%s" must implement a "__toString" method to be rendered in a "%s" widget', get_class($parent), __CLASS__));
      }

      $parentValue = (string) $parent;
      if (!isset($choices[$parentValue]))
      {
        $choices[$parentValue] = array();
      }

      $choices[$parentValue][$object->$methodKey()] = $object->$methodValue();
    }

    return $choices;
  }
}
