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
 * Add column to determine whether a menu item is deleteable
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0166
{
  const
    VERSION = 166, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    if (false === QubitPdo::fetchOne("SHOW COLUMNS IN ". QubitMenu::TABLE_NAME." LIKE ?", array('deleteable')))
    {
      QubitMigrate::addColumn(QubitMenu::TABLE_NAME, 'renameable TINYINT default 1');
      QubitMigrate::addColumn(QubitMenu::TABLE_NAME, 'deleteable TINYINT default 1');

      // Prevent renaming and deletion of "myProfile" menu item
      $criteria = new Criteria;
      $criteria->add(QubitMenu::NAME, 'myProfile');

      foreach(QubitMenu::get($criteria) as $menu)
      {
        $menu->renameable = false;
        $menu->deleteable = false;
        $menu->save();
      }

      // Prevent renaming and deletion of existing protected menu items with established IDs
      $protected = array(
        QubitMenu::ROOT_ID,
        QubitMenu::MAIN_MENU_ID,
        QubitMenu::QUICK_LINKS_ID,
        QubitMenu::ADD_EDIT_ID,
        QubitMenu::ADMIN_ID
      );

      foreach($protected as $id)
      {
        $menu = QubitMenu::getById($id);
        $menu->renameable = false;
        $menu->deleteable = false;
        $menu->save();
      }
    }

    return true;
  }
}
