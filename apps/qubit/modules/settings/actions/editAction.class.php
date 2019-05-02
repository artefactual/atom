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

class SettingsEditAction extends DefaultEditAction
{
  protected $settings = array();

  protected function earlyExecute()
  {
    $this->i18n = sfContext::getInstance()->i18n;

    // Load setting for each field name
    foreach ($this::$NAMES as $name)
    {
      $this->settings[$name] = QubitSetting::getByName($name);
    }
  }

  protected function setFormFieldDefault($name)
  {
    // If there's no settings default set, use blank string as default
    $settingDefault = (isset($this->settingDefaults[$name]))
      ? $this->settingDefaults[$name] : '';

    // Default setting value in form will be current setting value or, if none exists, settings default
    $settingValue = (null !== $this->settings[$name])
      ? $this->settings[$name]->getValue(array('sourceCulture' => true)) : $settingDefault;

    $this->form->setDefault($name, $settingValue);
  }

  protected function processField($field)
  {
    $name = $field->getName();

    if (in_array($name, $this::$NAMES))
    {
      if (null === $this->settings[$name])
      {
        $this->settings[$name] = new QubitSetting;
        $this->settings[$name]->name = $name;
      }
      $this->settings[$name]->setValue($field->getValue(), array('sourceCulture' => true));
      $this->settings[$name]->save();
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

        if (!empty($this->updateMessage))
        {
          $this->getUser()->setFlash('notice', $this->updateMessage);
        }

        $this->redirect(array('module' => 'settings', 'action' => $this->getContext()->getActionName()));
      }
    }
  }
}
