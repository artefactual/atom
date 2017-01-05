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

/*
 * Add new setting for enabling institutional scoping.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0149
{
  const
    VERSION = 149, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    if (null === QubitSetting::getByName('enable_institutional_scoping'))
    {
      $setting = new QubitSetting;
      $setting->name = 'enable_institutional_scoping';
      $setting->value = 0;
      $setting->editable = 1;
      $setting->culture = 'en';
      $setting->save();
    }

    if (null === QubitSetting::getByName('globalSearch'))
    {
      $setting = new QubitSetting;
      $setting->name  = 'globalSearch';
      $setting->scope = 'ui_label';
      $setting->editable = 1;
      $setting->deleteable = 0;
      $setting->source_culture = 'en';
      $setting->setValue('Search', array('culture' => 'en'));
      $setting->save();
    }

    if (null === QubitSetting::getByName('institutionSearchHoldings'))
    {
      $setting = new QubitSetting;
      $setting->name  = 'institutionSearchHoldings';
      $setting->scope = 'ui_label';
      $setting->editable = 1;
      $setting->deleteable = 0;
      $setting->source_culture = 'en';
      $setting->setValue('Search our collection', array('culture' => 'en'));
      $setting->save();
    }

    if (null === QubitMenu::getByName('browseInstitution'))
    {
      $browseInstMenu = new QubitMenu;
      $browseInstMenu->parentId = QubitMenu::ROOT_ID;
      $browseInstMenu->name = 'browseInstitution';
      $browseInstMenu->label = 'Browse our collection';
      $browseInstMenu->culture = 'en';
      $browseInstMenu->save();

      if (null === QubitMenu::getByName('browseInformationObjectsInstitution'))
      {
        $menu = new QubitMenu;
        $menu->parentId = $browseInstMenu->id;
        $menu->name = 'browseInformationObjectsInstitution';
        $menu->path = 'informationobject/browse?repos=%currentRealm%';
        $menu->sourceCulture = 'en';
        $menu->label = 'Archival descriptions';
        $menu->save();
      }
    }

    return true;
  }
}
