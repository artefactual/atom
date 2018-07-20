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
 * Prevent various menus from being renamed or deleted
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0167
{
  const
    VERSION = 167, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    $this->lockMenusByName();
    $this->lockMenusById();

    return true;
  }

  function lockMenusByName()
  {
    // Lock down menus referred to in code by name
    $menuNames = array(
      'accessions',
      'browseDigitalObjects',
      'browseInstitution',
      'browseSubjects',
      'clipboard',
      'globalReplace',
      'groups',
      'importSkos',
      'jobs',
      'login',
      'logout',
      'plugins',
      'privacy',
      'settings',
      'staticPagesMenu',
      'taxonomies',
      'users'
    );

    foreach ($menuNames as $menuName)
    {
      $criteria = new Criteria;
      $criteria->add(QubitMenu::NAME, $menuName);

      if (null !== $menu = QubitMenu::getOne($criteria))
      {
        $menu->renameable = false;
        $menu->deleteable = false;
        $menu->save();
      }
    }
  }

  function lockMenusById()
  {
    // Prevent renaming and deletion of existing protected menu items with established IDs
    $menuIds = array(
      QubitMenu::BROWSE_ID,
      QubitMenu::IMPORT_ID,
      QubitMenu::MANAGE_ID
    );

    foreach($menuIds as $id)
    {
      $menu = QubitMenu::getById($id);
      $menu->renameable = false;
      $menu->deleteable = false;
      $menu->save();
    }
  }
}
