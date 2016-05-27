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
 * 1. Change browse digital object link destination to now use archival
 * description browse page, see #9769
 *
 * 2. Add default_archival_description_browse_view default setting
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0139
{
  const
    VERSION = 139, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    if (null !== $menu = QubitMenu::getByName('browseDigitalObjects'))
    {
      $menu->path = 'informationobject/browse?view=card&onlyMedia=1';
      $menu->save();
    }

    if (null === QubitSetting::getByName('default_archival_description_browse_view'))
    {
      $setting = new QubitSetting;
      $setting->setName('default_archival_description_browse_view');
      $setting->setSourceCulture('en');
      $setting->setValue('table');
      $setting->save();
    }

    return true;
  }
}
