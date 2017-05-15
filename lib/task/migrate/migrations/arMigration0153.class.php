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
 * Add taxonomy and terms for job statuses
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0153
{
  const
    VERSION = 153, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::JOB_STATUS_ID, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::JOB_STATUS_ID;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->sourceCulture = 'en';
    $taxonomy->setName('Job status', array('culture' => 'en'));
    $taxonomy->save();

    $terms = array(
      QubitTerm::JOB_STATUS_IN_PROGRESS_ID => 'In progress',
      QubitTerm::JOB_STATUS_COMPLETED_ID => 'Completed',
      QubitTerm::JOB_STATUS_ERROR_ID => 'Error'
    );

    foreach ($terms as $id => $name)
    {
      QubitMigrate::bumpTerm($id, $configuration);
      $term = new QubitTerm;
      $term->id = $id;
      $term->parentId = QubitTerm::ROOT_ID;
      $term->taxonomyId = QubitTaxonomy::JOB_STATUS_ID;
      $term->sourceCulture = 'en';
      $term->setName($name, array('culture' => 'en'));
      $term->save();
    }

    return true;
  }
}
