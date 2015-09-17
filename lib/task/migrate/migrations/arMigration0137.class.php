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
 * Add clipboard menu
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0137
{
  const
    VERSION = 137, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    if (null === QubitMenu::getByName('clipboard'))
    {
      $clipboardMenu = new QubitMenu;
      $clipboardMenu->parentId = QubitMenu::ROOT_ID;
      $clipboardMenu->name = 'clipboard';
      $clipboardMenu->label = 'Clipboard';
      $clipboardMenu->culture = 'en';
      $clipboardMenu->save();

      if (null === QubitMenu::getByName('clearClipboard'))
      {
        $menu = new QubitMenu;
        $menu->parentId = $clipboardMenu->id;
        $menu->name = 'clearClipboard';
        $menu->path = 'user/clipboardClear';
        $menu->sourceCulture = 'en';
        $menu->label = 'Clear selections';
        $menu->save();
      }

      if (null === QubitMenu::getByName('goToClipboard'))
      {
        $menu = new QubitMenu;
        $menu->parentId = $clipboardMenu->id;
        $menu->name = 'goToClipboard';
        $menu->path = 'user/clipboard';
        $menu->sourceCulture = 'en';
        $menu->label = 'Go to clipboard';
        $menu->save();
      }
    }

    return true;
  }
}
