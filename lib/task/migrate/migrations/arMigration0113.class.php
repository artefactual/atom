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
 * Create granted_right table, create default premis rights / settings
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0113
{
  const
    VERSION = 113, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    $sql = <<<sql

CREATE TABLE IF NOT EXISTS `granted_right`
(
  `rights_id` INTEGER  NOT NULL,
  `act_id` INTEGER,
  `restriction` TINYINT default 1,
  `start_date` DATE,
  `end_date` DATE,
  `notes` TEXT,
  `id` INTEGER  NOT NULL AUTO_INCREMENT,
  `serial_number` INTEGER default 0 NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `granted_right_FI_1` (`rights_id`),
  CONSTRAINT `granted_right_FK_1`
    FOREIGN KEY (`rights_id`)
    REFERENCES `rights` (`id`)
    ON DELETE CASCADE,
  INDEX `granted_right_FI_2` (`act_id`),
  CONSTRAINT `granted_right_FK_2`
    FOREIGN KEY (`act_id`)
    REFERENCES `term` (`id`)
    ON DELETE SET NULL
)Engine=InnoDB;


ALTER TABLE `rights_i18n` CHANGE `license_identifier` `identifier_value` TEXT  CHARACTER SET utf8  COLLATE utf8_unicode_ci  NULL;
ALTER TABLE `rights_i18n` ADD `identifier_type` TEXT  CHARACTER SET utf8  COLLATE utf8_unicode_ci  NULL  AFTER `culture`;
ALTER TABLE `rights_i18n` MODIFY COLUMN `identifier_type` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER `identifier_value`;
ALTER TABLE `rights_i18n` ADD `identifier_role` TEXT  CHARACTER SET utf8  COLLATE utf8_unicode_ci  NULL  AFTER `culture`;
ALTER TABLE `rights_i18n` MODIFY COLUMN `identifier_role` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER `identifier_type`;

INSERT IGNORE INTO granted_right (rights_id, act_id, restriction) SELECT id, act_id, restriction from rights;

ALTER TABLE rights DROP restriction;
ALTER TABLE rights DROP FOREIGN KEY rights_ibfk_3;
DROP INDEX rights_FI_3 ON rights;
ALTER TABLE rights DROP act_id;
sql;

    /**
     * The last three statements (regarding to .act_id) failed.
     * Fixed in arMigration0134.
     */

    QubitPdo::modify($sql);

    // Create default PREMIS settings
    $premisAccessRight = QubitSetting::getByName('premisAccessRight');
    $premisAccessRightValues = QubitSetting::getByName('premisAccessRightValues');

    if ($premisAccessRight === null)
    {
      $s = new QubitSetting;
      $s->setName('premisAccessRight');
      $s->setValue('disseminate');
      $s->save();
    }

    if ($premisAccessRightValues === null)
    {
      $s = new QubitSetting;
      $s->setName('premisAccessRightValues');
      $s->setValue('a:9:{s:12:"allow_master";s:1:"1";s:15:"allow_reference";s:1:"1";s:11:"allow_thumb";s:1:"1";s:18:"conditional_master";s:1:"0";s:21:"conditional_reference";s:1:"1";s:17:"conditional_thumb";s:1:"1";s:15:"disallow_master";s:1:"0";s:18:"disallow_reference";s:1:"0";s:14:"disallow_thumb";s:1:"0";}');
      $s->save();
    }

    // Create reference image warning messages
    $disallowWarning = QubitSetting::getByName('access_disallow_warning');
    $conditionalWarning = QubitSetting::getByName('access_conditional_warning');

    if ($disallowWarning === null)
    {
      $s = new QubitSetting;
      $s->setScope('ui_label');
      $s->setName('access_disallow_warning');
      $s->setValue('Access to this record is restricted because it contains personal or confidential information. Please contact the Reference Archivist for more information on accessing this record.');
      $s->save();
    }

    if ($conditionalWarning === null)
    {
      $s = new QubitSetting;
      $s->setScope('ui_label');
      $s->setName('access_conditional_warning');
      $s->setValue('This record has not yet been reviewed for personal or confidential information. Please contact the Reference Archivist to request access and initiate an access review.');
      $s->save();
    }

    // Create thumbnail permissions if they don't exist
    $sqlCount = '
      SELECT count(1) FROM acl_permission
      WHERE group_id = ? AND action = "readThumbnail"
    ';

    $sqlInsert = '
      INSERT INTO acl_permission (group_id, object_id, action, grant_deny, created_at, updated_at)
      VALUES (?, ?, "readThumbnail", 1, NOW(), NOW())
    ';

    $permissionCount = QubitPdo::fetchColumn($sqlCount, array(QubitAclGroup::ANONYMOUS_ID));
    if ($permissionCount == 0)
    {
      QubitPdo::prepareAndExecute($sqlInsert, array(QubitAclGroup::ANONYMOUS_ID, QubitInformationObject::ROOT_ID));
    }

    $permissionCount = QubitPdo::fetchColumn($sqlCount, array(QubitAclGroup::AUTHENTICATED_ID));
    if ($permissionCount == 0)
    {
      QubitPdo::prepareAndExecute($sqlInsert, array(QubitAclGroup::AUTHENTICATED_ID, QubitInformationObject::ROOT_ID));
    }

    return true;
  }
}
