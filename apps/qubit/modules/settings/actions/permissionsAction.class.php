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
 * Permissions
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsPermissionsAction extends sfAction
{
  public function execute($request)
  {
    $this->permissionsForm = new SettingsPermissionsForm;
    $this->permissionsAccessStatementsForm = new SettingsPermissionsAccessStatementsForm;
    $this->permissionsCopyrightStatementForm = new SettingsPermissionsCopyrightStatementForm;

    $this->basis = array();
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item)
    {
      $this->basis[$item->slug] = $item->getName(array('cultureFallback' => true));
    }

    $this->copyrightStatementSetting = QubitSetting::getByName('digitalobject_copyright_statement');

    $this->response->addJavaScript('permissionsSettings');

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      // Give the user the ability to preview the copyright statement before
      // we persist the changes. We are reusing the viewCopyrightStatement
      // template, populating the properties that are needed.
      if ($request->hasParameter('preview'))
      {
        $this->setTemplate('viewCopyrightStatement', 'digitalobject');

        $this->preview = true;
        $this->resource = new QubitInformationObject;

        $this->permissionsCopyrightStatementForm->bind($request->getPostParameters());
        $statement = $this->permissionsCopyrightStatementForm->getValue('copyrightStatement');
        $statement = QubitHtmlPurifier::getInstance()->purify($statement);
        $this->copyrightStatement = $this->permissionsCopyrightStatementForm->getValue('copyrightStatement');

        return sfView::SUCCESS;
      }

      QubitCache::getInstance()->removePattern('settings:i18n:*');

      // PREMIS access permissions
      $this->permissionsForm->bind($request->getPostParameters());
      if ($this->permissionsForm->isValid())
      {
        $premisAccessRight = QubitSetting::getByName('premisAccessRight');
        $premisAccessRight->setValue($this->permissionsForm->getValue('granted_right'), array('sourceCulture' => true));
        $premisAccessRight->save();

        $premisAccessRightValues = QubitSetting::getByName('premisAccessRightValues');
        $premisAccessRightValues->setValue(serialize($this->permissionsForm->getValue('permissions')), array('sourceCulture' => true));
        $premisAccessRightValues->save();
      }

      // PREMIS access statements
      $values = $request->getPostParameters();
      $this->permissionsAccessStatementsForm->bind($values['accessStatements']);
      if ($this->permissionsAccessStatementsForm->isValid())
      {
        $values = $this->permissionsAccessStatementsForm->getValues();

        foreach ($values as $key => $value)
        {
          $setting = QubitSetting::getByNameAndScope($key, 'access_statement');
          $setting->setValue($value);
          $setting->save();
        }

        // Remove unused settings (e.g. a term of the basis taxonomy was
        // deleted). We use array_key_exists because isset() returns false
        // if the key is defined but its value is NULL.
        foreach (QubitSetting::getByScope('access_statement') as $setting)
        {
          if (!array_key_exists($setting->name, $values))
          {
            $setting->delete();
          }
        }
      }

      // Copyright statement
      $this->permissionsCopyrightStatementForm->bind($request->getPostParameters());
      if ($this->permissionsCopyrightStatementForm->isValid())
      {
        $setting = QubitSetting::getByName('digitalobject_copyright_statement_enabled');
        if (null === $setting)
        {
          $setting = new QubitSetting;
          $setting->name = 'digitalobject_copyright_statement_enabled';
          $setting->sourceCulture = sfConfig::get('sf_default_culture');
        }
        $setting->setValue($this->permissionsCopyrightStatementForm->getValue('copyrightStatementEnabled'), array('sourceCulture' => true));
        $setting->save();

        $statement = $this->permissionsCopyrightStatementForm->getValue('copyrightStatement');
        $statement = QubitHtmlPurifier::getInstance()->purify($statement);

        if (!empty($statement))
        {
          $setting = QubitSetting::getByName('digitalobject_copyright_statement');
          if (null === $setting)
          {
            $setting = new QubitSetting;
            $setting->name = 'digitalobject_copyright_statement';
          }
          $setting->setValue($statement);
          $setting->save();
        }
      }

      $this->redirect('settings/permissions');
    }
  }
}
