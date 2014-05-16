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

class drmcTweaksTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
    ));

    $this->namespace        = 'drmc';
    $this->name             = 'tweaks';
    $this->briefDescription = 'Tweak DRMC AIP data';
    $this->detailedDescription = <<<EOF
Tweak DRMC data
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);

    $departmentTaxonomyId = sfConfig::get('app_drmc_taxonomy_departments_id');

    // create example department terms if they don't exist
    $exampleDepartments = array(
      'Drawings and Prints',
      'Archives',
      'Film',
      'Library',
      'MPA'
    );

    foreach($exampleDepartments as $department)
    {
      QubitFlatfileImport::createOrFetchTerm($departmentTaxonomyId, $department);
    }

    // load department terms and create simpler array to pick random ones from
    $departmentTerms = QubitFlatfileImport::getTaxonomyTerms($departmentTaxonomyId);
    $departments = array();
    foreach($departmentTerms as $departmentTerm)
    {
      array_push($departments, array(
        'id'   => $departmentTerm->id,
        'name' => $departmentTerm->name
      ));
    }

    // get appropriate info objects
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, sfConfig::get('app_drmc_lod_artwork_record_id'));
    $items = QubitInformationObject::get($criteria);

    $image_media_term = QubitFlatfileImport::createOrFetchTerm(QubitTaxonomy::MEDIA_TYPE_ID, 'Image');
    $mime_types = array(
      'image/jpeg',
      'image/gif',
      'text/html',
      'application/pdf'
    );

    // add random collection dates
    foreach($items as $item) {
      // add random collection date to information object
      $random = mt_rand(1262055681, 1399488461);
      $randomDate = date("Y-m-d", $random);
      $item->addProperty('Dated', $randomDate);

      // assign random create date within the last year
      $randomDatetime = rand(time()-(86400 * 365) , time());
      $item->createdAt = date('Y-m-d', $randomDatetime);

      $item->save();

      // add random department to information object
      $randomDepartment = rand(0, count($departments) - 1);
      QubitFlatfileImport::createObjectTermRelation(
        $item->id,
        $departments[$randomDepartment]['id']
      );

      // add random byte size to associated digital object
      $criteria = new Criteria;
      $criteria->add(QubitDigitalObject::INFORMATION_OBJECT_ID, $item->id);
      $do = QubitDigitalObject::getOne($criteria);

      // if digital object doesn't exist, make one with random size
      if (!$do)
      {
        $do = new QubitDigitalObject;
        $do->informationObject = $item;
      }
      $do->byteSize = rand(1000, 10000000);
      $do->mediaTypeId = $image_media_term->id;
      $mime_type = $mime_types[(rand(0, count($mime_types)-1))];
      $do->mimeType = $mime_type;
      $do->save();

      print '.';
    }

    print "\nTweaks made.\n";
  }
}
