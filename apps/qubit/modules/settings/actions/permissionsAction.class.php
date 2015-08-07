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
    $this->permissionsCopyrightStatementForm = new SettingsPermissionsCopyrightStatementForm;

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      if (null !== $request->permissions && null !== $request->granted_right)
      {
        $requestGrantedRight = QubitTaxonomy::getBySlug($request->granted_right);
        $permissions = array_map(function($v){ return (int) (bool) $v; }, $request->permissions);

        // validate granted_right exists
        if (null === $requestGrantedRight)
        {
          throw new sfException('invalid new PremisAccessRight value');
        }

        if (array_keys($request->permissions) !== array_keys(QubitSetting::$premisAccessRightValueDefaults))
        {
          throw new sfException('invalid new permissions values for premisAccessRightValues');
        }

        $premisAccessRight = QubitSetting::getByName('premisAccessRight');
        $premisAccessRightValues = QubitSetting::getByName('premisAccessRightValues');

        $premisAccessRight->value = $requestGrantedRight->slug;
        $premisAccessRight->save();

        $premisAccessRightValues->value = serialize($request->permissions);
        $premisAccessRightValues->save();
      }

      $this->permissionsCopyrightStatementForm->bind($request->getPostParameters());
      if ($this->permissionsCopyrightStatementForm->isValid())
      {
        $setting = QubitSetting::getByName('digitalobject_copyright_statement_enabled');
        if (null === $this->setting)
        {
          $setting = new QubitSetting;
          $setting->name = 'digitalobject_copyright_statement_enabled';
        }
        $setting->value = $this->permissionsCopyrightStatementForm->getValue('copyrightStatementEnabled');
        $setting->save();

        $statement = $this->permissionsCopyrightStatementForm->getValue('copyrightStatement');
        $statement = QubitHtmlPurifier::getInstance()->purify($statement);

        if (!empty($statement))
        {
          $setting = QubitSetting::getByName('digitalobject_copyright_statement');
          if (null === $this->setting)
          {
            $setting = new QubitSetting;
            $setting->name = 'digitalobject_copyright_statement';
          }
          $setting->value = $statement;
          $setting->save();
        }
      }

      $this->redirect('settings/permissions');
    }
  }
}
