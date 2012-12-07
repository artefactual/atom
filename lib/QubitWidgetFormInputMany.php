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
 * sfWidgetFormSelectMany represents an HTML input tag with multiple values.
 * Because HTML doesn't support multi-value inputs natively, we are faking it
 * with a list of related inputs.
 *
 * @package    AccesstoMemory
 * @subpackage widget
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitWidgetFormInputMany extends sfWidgetFormInput
{
  /**
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormSelect
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->addRequiredOption('defaults');
    $this->addOption('fieldname', 'name');
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $inputStr = '';

    $fieldname = $this->getOption('fieldname');
    $defaults = $this->getOption('defaults');
    if ($defaults instanceof sfCallable)
    {
      $defaults = $defaults->call();
    }

    // http://trac.symfony-project.org/ticket/7208
    $null = $this->renderTag('input', array('name' => $name, 'type' => 'hidden'));

    if (is_array($defaults) && 0 < count($defaults))
    {
      $inputStr .= '<ul class="multiInput" id="'.$name."\">\n";
      foreach ($defaults as $key => $default)
      {
        $inputStr .= "\t<li>";
        if (sfContext::getInstance()->user->getCulture() != $default->sourceCulture && 0 < strlen($source = $default->__get($fieldname, array('sourceCulture' => true))))
        {
          $inputStr .= <<<EOF
      <div class="default-translation">
        $source
      </div>
EOF;
        }
        $inputStr .= $this->renderTag('input', array_merge(array('type' => $this->getOption('type'), 'name' => $name.'['.$key.']', 'value' => $default->__get($fieldname)), $attributes))."</li>\n";
      }
      $inputStr .= "</ul>\n";
    }

    $attributes['class'] = (isset($attributes['class'])) ? $attributes['class'] + ' multiInput' : 'multiInput';

    // Add a new value
    $new = $this->renderTag('input', array_merge(array('name' => $name.'[new]', 'type' => $this->getOption('type')), $attributes));

    return $null.$inputStr.$new;
  }
}
