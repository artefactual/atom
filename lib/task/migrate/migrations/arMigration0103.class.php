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
 * Include 'Related material descriptions' term in the relationType taxonomy
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0103
{
  const
    VERSION = 103, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    QubitMigrate::bumpTerm(QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID, $configuration);
    $term = new QubitTerm;
    $term->id = QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID;
    $term->parentId = QubitTerm::ROOT_ID;
    $term->taxonomyId = QubitTaxonomy::RELATION_TYPE_ID;
    $term->sourceCulture = 'en';
    $term->setName('Related material descriptions', array('culture' => 'en'));
    $term->save();

    return true;
  }
}
