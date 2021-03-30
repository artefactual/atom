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
 * Add clipboard save-related tables
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0157
{
    public const VERSION = 157;
    public const MIN_MILESTONE = 2;

    /**
     * Upgrade.
     *
     * @param mixed $configuration
     *
     * @return bool True if the upgrade succeeded, False otherwise
     */
    public function up($configuration)
    {
        // Add table clipboard_save
        $sql = <<<'sql'

CREATE TABLE `clipboard_save` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clipboard_save_FI_1` (`user_id`),
  CONSTRAINT `clipboard_save_FK_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

sql;
        QubitPdo::modify($sql);

        // Add table clipboard_save_item
        $sql = <<<'sql'

CREATE TABLE `clipboard_save_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `save_id` int(11) DEFAULT NULL,
  `item_class_name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clipboard_save_item_FI_1` (`save_id`),
  CONSTRAINT `clipboard_save_item_FK_1` FOREIGN KEY (`save_id`) REFERENCES `clipboard_save` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

sql;
        QubitPdo::modify($sql);

        // Add menu items
        if (null !== $parentMenu = QubitMenu::getByName('clipboard')) {
            $menu = new QubitMenu();
            $menu->parentId = $parentMenu->id;
            $menu->sourceCulture = 'en';
            $menu->name = 'loadClipboard';
            $menu->label = 'Load clipboard';
            $menu->path = 'user/clipboardLoad';
            $menu->save();
        }

        if (null !== $parentMenu = QubitMenu::getByName('clipboard')) {
            $menu = new QubitMenu();
            $menu->parentId = $parentMenu->id;
            $menu->sourceCulture = 'en';
            $menu->name = 'saveClipboard';
            $menu->label = 'Save clipboard';
            $menu->path = 'user/clipboardSave';
            $menu->save();
        }

        return true;
    }
}
