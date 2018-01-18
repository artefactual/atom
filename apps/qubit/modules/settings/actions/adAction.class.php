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

class SettingsADAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'ldapHost',
      'ldapBaseDn');

  protected function earlyExecute()
  {

  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'ldapHost':
      case 'ldapBaseDn':
      // Determine and set field default value
      if (null !== $this->{$name} = QubitSetting::getByName($name))
      {
        $default = $this->{$name}->getValue(array('sourceCulture' => true));
      }
      else
      {
        $default = (isset($defaults[$name])) ? $defaults[$name] : '';
      }
      $this->form->setDefault($name, $default);
      // Set validator and widget
      $this->form->setWidget($name, new sfWidgetFormInput);
      break;
    }
  }

  protected function processField($field)
  {
    switch ($name = $field->getName())
    {
      case 'ldapHost':
      case 'ldapBaseDn':
      if (null === $this->{$name})
      {
        $this->{$name} = new QubitSetting;
        $this->{$name}->name = $name;
        $this->{$name}->scope = 'ad';
      }
      $this->{$name}->setValue($field->getValue(), array('sourceCulture' => true));
      $this->{$name}->save();
      break;
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->processForm();
        QubitCache::getInstance()->removePattern('settings:i18n:*');
        $this->redirect(array('module' => 'settings', 'action' => 'ad'));
      }
    }
  }
}
