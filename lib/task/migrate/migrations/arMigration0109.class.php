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
 * Make uuid column in aip table required and unique
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0109
{
  const
    VERSION = 109, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Remove duplicated aips
    $uuids = array();
    foreach (QubitAip::getAll() as $aip)
    {
      if (in_array($aip->uuid, $uuids))
      {
        $criteria = new Criteria;
        $criteria->addJoin(QubitInformationObject::ID, QubitRelation::OBJECT_ID);
        $criteria->add(QubitRelation::TYPE_ID, QubitTerm::AIP_RELATION_ID);
        $criteria->add(QubitRelation::SUBJECT_ID, $aip->id);
        $criteria->addDescendingOrderByColumn(QubitInformationObject::LFT);

        // Delete related info. objects
        foreach (QubitInformationObject::get($criteria) as $item)
        {
          foreach ($item->digitalObjects as $do)
          {
            $do->delete();
          }

          $item->delete();
        }

        $aip->delete();

        continue;
      }

      $uuids[] = $aip->uuid;
    }

    // Modify column
    $sql = <<<sql

ALTER TABLE `aip`
MODIFY COLUMN `uuid` VARCHAR(36) NOT NULL;

sql;

    QubitPdo::modify($sql);

    $sql = <<<sql

ALTER TABLE `aip`
ADD UNIQUE (`uuid`);

sql;

    QubitPdo::modify($sql);

    return true;
  }
}
