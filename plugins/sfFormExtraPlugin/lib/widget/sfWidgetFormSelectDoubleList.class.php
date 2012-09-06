<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSelectDoubleList represents a multiple select displayed as a double list.
 *
 * This widget needs some JavaScript to work. So, you need to include the JavaScripts
 * files returned by the getJavaScripts() method.
 *
 * If you use symfony 1.2, it can be done automatically for you.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormSelectDoubleList.class.php 32834 2011-07-27 07:04:31Z fabien $
 */
class sfWidgetFormSelectDoubleList extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * choices:            An array of possible choices (required)
   *  * class:              The main class of the widget
   *  * class_select:       The class for the two select tags
   *  * label_unassociated: The label for unassociated
   *  * label_associated:   The label for associated
   *  * unassociate:        The HTML for the unassociate link
   *  * associate:          The HTML for the associate link
   *  * associated_first:   Whether the associated list if first (true by default)
   *  * template:           The HTML template to use to render this widget
   *                        The available placeholders are:
   *                          * label_associated
   *                          * label_unassociated
   *                          * associate
   *                          * unassociate
   *                          * associated
   *                          * unassociated
   *                          * class
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('choices');

    $this->addOption('class', 'double_list');
    $this->addOption('class_select', 'double_list_select');
    $this->addOption('associated_first', true);
    $this->addOption('label_unassociated', 'Unassociated');
    $this->addOption('label_associated', 'Associated');
    $associated_first = isset($options['associated_first']) ? $options['associated_first'] : true;

    if ($associated_first)
    {
      $associate_image = 'previous.png';
      $unassociate_image = 'next.png';
      $float = 'left';
    }
    else
    {
      $associate_image = 'next.png';
      $unassociate_image = 'previous.png';
      $float = 'right';
    }

    $this->addOption('unassociate', '<img src="/sfFormExtraPlugin/images/'.$unassociate_image.'" alt="unassociate" />');
    $this->addOption('associate', '<img src="/sfFormExtraPlugin/images/'.$associate_image.'" alt="associate" />');
    $this->addOption('template', <<<EOF
<div class="%class%">
  <div style="float: left">
    <div style="float: $float">
      <div class="double_list_label">%label_associated%</div>
      %associated%
    </div>
    <div style="float: $float; margin-top: 2em">
      %associate%
      <br />
      %unassociate%
    </div>
    <div style="float: $float">
      <div class="double_list_label">%label_unassociated%</div>
      %unassociated%
    </div>
  </div>
  <br style="clear: both" />
  <script type="text/javascript">
    sfDoubleList.init(document.getElementById('%id%'), '%class_select%');
  </script>
</div>
EOF
);
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if (is_null($value))
    {
      $value = array();
    }

    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    $associated = array();
    $unassociated = array();
    foreach ($choices as $key => $option)
    {
      if (in_array(strval($key), $value))
      {
        $associated[$key] = $option;
      }
      else
      {
        $unassociated[$key] = $option;
      }
    }

    $size = isset($attributes['size']) ? $attributes['size'] : (isset($this->attributes['size']) ? $this->attributes['size'] : 10);

    $associatedWidget = new sfWidgetFormSelect(array('multiple' => true, 'choices' => $associated), array('size' => $size, 'class' => $this->getOption('class_select').'-selected'));
    $associatedWidget->setParent($this->getParent());

    $unassociatedWidget = new sfWidgetFormSelect(array('multiple' => true, 'choices' => $unassociated), array('size' => $size, 'class' => $this->getOption('class_select')));
    $unassociatedWidget->setParent($this->getParent());

    return strtr($this->getOption('template'), array(
      '%class%'              => $this->getOption('class'),
      '%class_select%'       => $this->getOption('class_select'),
      '%id%'                 => $this->generateId($name),
      '%label_associated%'   => $this->translate($this->getOption('label_associated')),
      '%label_unassociated%' => $this->translate($this->getOption('label_unassociated')),
      '%associate%'          => sprintf('<a href="#" onclick="%s">%s</a>', 'sfDoubleList.move(\'unassociated_'.$this->generateId($name).'\', \''.$this->generateId($name).'\'); return false;', $this->getOption('associate')),
      '%unassociate%'        => sprintf('<a href="#" onclick="%s">%s</a>', 'sfDoubleList.move(\''.$this->generateId($name).'\', \'unassociated_'.$this->generateId($name).'\'); return false;', $this->getOption('unassociate')),
      '%associated%'         => $associatedWidget->render($name),
      '%unassociated%'       => $unassociatedWidget->render('unassociated_'.$name),
    ));
  }

  /**
   * Gets the JavaScript paths associated with the widget.
   *
   * @return array An array of JavaScript paths
   */
  public function getJavascripts()
  {
    return array('/sfFormExtraPlugin/js/double_list.js');
  }

  public function __clone()
  {
    if ($this->getOption('choices') instanceof sfCallable)
    {
      $callable = $this->getOption('choices')->getCallable();
      if (is_array($callable))
      {
        $callable[0] = $this;
        $this->setOption('choices', new sfCallable($callable));
      }
    }
  }
}
