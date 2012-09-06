<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormDoctrineJQueryAutocompleter represents an autocompleter input widget rendered by JQuery
 * optimized for foreign key lookup.
 *
 * This implementation is based on sfWidgetFormPropelJQueryAutocompleter.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Roland Jungwirth <roland@top-node.com>
 * @version    SVN: $Id: sfWidgetFormPropelJQueryAutocompleter.class.php 12130 2008-10-10 14:51:07Z fabien $
 */
class sfWidgetFormDoctrineJQueryAutocompleter extends sfWidgetFormJQueryAutocompleter
{
  /**
   * @see sfWidget
   */
  public function __construct($options = array(), $attributes = array())
  {
    $options['value_callback'] = array($this, 'toString');

    parent::__construct($options, $attributes);
  }

  /**
   * Configures the current widget.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormJQueryAutocompleter
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('model');
    $this->addOption('method_for_query', 'findOneById');
    $this->addOption('method', '__toString');

    parent::configure($options, $attributes);
  }

  /**
   * Returns the text representation of a foreign key.
   *
   * @param string $value The primary key
   */
  protected function toString($value)
  {
    $object = null;
    if ($value != null)
    {
      $class = Doctrine::getTable($this->getOption('model'));
      $method = $this->getOption('method_for_query');

      $object = call_user_func(array($class, $method), $value);
    }

    $method = $this->getOption('method');

    if (!method_exists($this->getOption('model'), $method))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->getOption('model'), $method, __CLASS__));
    }

    return !is_null($object) ? $object->$method() : '';
  }
}
