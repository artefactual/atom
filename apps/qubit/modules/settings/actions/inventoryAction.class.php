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

class SettingsInventoryAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'levels');

  protected function earlyExecute()
  {
    $this->settingLevels = QubitSetting::getByName('inventory_levels');
    if (null === $this->settingLevels)
    {
      $this->settingLevels = new QubitSetting;
      $this->settingLevels->name = 'inventory_levels';
    }
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'levels':
        $value = unserialize($this->settingLevels->getValue());
        if (false !== $value)
        {
          foreach ($value as $key => $item)
          {
            if (null === QubitTerm::getById($item))
            {
              $this->unknownValueDetected = true;
              unset($value[$key]);
            }
          }

          $this->form->setDefault('levels', $value);
        }

        $this->form->setValidator('levels', new sfValidatorPass);

        $choices = array();
        foreach (QubitTerm::getLevelsOfDescription() as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $size = count($choices);
        if ($size === 0)
        {
          $size = 4;
        }

        $this->form->setWidget('levels', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true), array('size' => $size)));

        break;
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'levels':
        $levels = $this->form->getValue('levels');
        if (empty($levels))
        {
          $levels = array();
        }
        $this->settingLevels->value = serialize($levels);

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

        if (null !== $this->settingLevels->value)
        {
          $this->settingLevels->save();
        }

        QubitCache::getInstance()->removePattern('settings:i18n:*');

        $this->redirect(array('module' => 'settings', 'action' => 'inventory'));
      }
    }
  }
}
