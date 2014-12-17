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
 * Default template
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsTemplateAction extends sfAction
{
  public function execute($request)
  {
    $this->defaultTemplateForm = new SettingsDefaultTemplateForm;

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      // Handle default template form submission
      if (null !== $request->default_template)
      {
        $this->defaultTemplateForm->bind($request->default_template);
        if ($this->defaultTemplateForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateDefaultTemplateSettings($this->defaultTemplateForm);
          $this->redirect('settings/template');
        }
      }
    }

    $this->populateDefaultTemplateForm($this->defaultTemplateForm);
  }

  /**
   * Populate the default template settings from the database (non-localized)
   */
  protected function populateDefaultTemplateForm($form)
  {
    $infoObjectTemplate = QubitSetting::getByNameAndScope('informationobject', 'default_template');
    $actorTemplate = QubitSetting::getByNameAndScope('actor', 'default_template');
    $repositoryTemplate = QubitSetting::getByNameAndScope('repository', 'default_template');

    // Set defaults for global form
    $this->defaultTemplateForm->setDefaults(array(
      'informationobject' => (isset($infoObjectTemplate)) ? $infoObjectTemplate->getValue(array('sourceCulture'=>true)) : null,
      'actor' => (isset($actorTemplate)) ? $actorTemplate->getValue(array('sourceCulture'=>true)) : null,
      'repository' => (isset($repositoryTemplate)) ? $repositoryTemplate->getValue(array('sourceCulture'=>true)) : null,
    ));
  }

  /**
   * Update default template db values with form values (non-localized)
   *
   * @return $this;
   */
  protected function updateDefaultTemplateSettings($form)
  {
    if (null !== $newValue = $form->getValue('informationobject'))
    {
      $setting = QubitSetting::findAndSave('informationobject', $newValue, array(
        'scope'=>'default_template', 'createNew'=>true, 'sourceCulture'=>true));
    }

    if (null !== $newValue = $form->getValue('actor'))
    {
      $setting = QubitSetting::findAndSave('actor', $newValue, array(
        'scope'=>'default_template', 'createNew'=>true, 'sourceCulture'=>true));
    }

    if (null !== $newValue = $form->getValue('repository'))
    {
      $setting = QubitSetting::findAndSave('repository', $newValue, array(
        'scope'=>'default_template', 'createNew'=>true, 'sourceCulture'=>true));
    }

    return $this;
  }
}
