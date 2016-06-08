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
 * Add new term to digital object usages taxonomy
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0142
{
  const
    VERSION = 142, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    // Create new term for protected controlled vocabulary (digital object usages)
    QubitMigrate::bumpTerm(QubitTerm::OFFLINE_ID, $configuration);
    $term = new QubitTerm;
    $term->id = QubitTerm::OFFLINE_ID;
    $term->parentId = QubitTerm::ROOT_ID;
    $term->taxonomyId = QubitTaxonomy::DIGITAL_OBJECT_USAGE_ID;
    $term->name = 'Offline';
    $term->culture = 'en';
    $term->save();

    return true;
  }
}
