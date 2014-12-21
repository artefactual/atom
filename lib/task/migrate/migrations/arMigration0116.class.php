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
 * Fix MODS resource types terms
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0116
{
  const
    VERSION = 116, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    $termNames = array(
      'sound recording - musical' => 'sound recording-musical',
      'sound recording - nonmusical' => 'sound recording-nonmusical'
    );

    foreach ($termNames as $oldName => $newName)
    {
      $criteria = new Criteria;
      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MODS_RESOURCE_TYPE_ID);
      $criteria->add(QubitTermI18n::CULTURE, 'en');
      $criteria->add(QubitTermI18n::NAME, $oldName);

      if (null !== $term = QubitTerm::getOne($criteria))
      {
        $term->setName($newName, array('culture' => 'en'));
        $term->save();
      }
    }

    $setting = new QubitSetting;
    $setting->setName('publicFindingAid');
    $setting->setValue(1);
    $setting->setEditable(1);
    $setting->setDeleteable(0);
    $setting->save();

    return true;
  }
}
