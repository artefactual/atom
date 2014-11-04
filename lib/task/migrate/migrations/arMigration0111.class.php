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
 * Create granted_right table, jobs table, jobs menu items
 * Add job table
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0111
{
  const
    VERSION = 111, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Create job table
    $sql = <<<sql
CREATE TABLE IF NOT EXISTS `job` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status_id` int(11) NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_FK_2_idx` (`user_id`),
  KEY `job_FK_3_idx` (`object_id`),
  CONSTRAINT `job_FK_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `job_FK_3` FOREIGN KEY (`object_id`) REFERENCES `object` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `job_FK_1` FOREIGN KEY (`id`) REFERENCES `object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
sql;

    QubitPdo::modify($sql);

    // Create job menu item under manage->jobs
    $jobs = QubitMenu::getByName('jobs');
    if ($jobs === null)
    {
      $node = new QubitMenu;
      $node->parentId = QubitMenu::MANAGE_ID;
      $node->name = 'jobs';
      $node->path = 'jobs/browse';
      $node->label = 'Jobs';
      $node->culture = 'en';
      $node->save();
    }

    return true;
  }
}
