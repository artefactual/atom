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

/**
 * Export flatfile repository data
 *
 * @package    symfony
 * @subpackage library
 * @author     Mike Gale <mikeg@artefactual.com>
 */
class csvRepositoryExport extends QubitFlatfileExport
{

  public function __construct($destinationPath, $standard = null, $rowsPerFile = false)
  {
    parent::__construct($destinationPath, $standard, $rowsPerFile);
    $this->loadHelpers();
  }

  private function loadHelpers()
  {
    include_once sfConfig::get('sf_root_dir').'/lib/helper/QubitHelper.php';
    ProjectConfiguration::getActive()->loadHelpers('I18N');
  }

  /*
   * Information object-specific column setting before CSV row write
   *
   * @return void
   */
  protected function modifyRowBeforeExport()
  {
    $this->setColumn('parallelFormsOfName', $this->getNames(QubitTerm::PARALLEL_FORM_OF_NAME_ID));
    $this->setColumn('otherFormsOfName', $this->getNames(QubitTerm::OTHER_FORM_OF_NAME_ID));
    $this->setColumn('thematicAreas', $this->getRelatedTermNames(QubitTaxonomy::THEMATIC_AREA_ID));
    $this->setColumn('geographicSubregions', $this->getRelatedTermNames(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID));
    $this->setColumn('types', $this->getRelatedTermNames(QubitTaxonomy::REPOSITORY_TYPE_ID));
    $this->setColumn('legacyId', $this->resource->id);

    if (isset($this->resource->descStatus))
    {
      $this->setColumn('descriptionStatus', $this->resource->descStatus->name);
    }

    if (isset($this->resource->descDetail))
    {
      $this->setColumn('levelOfDetail', $this->resource->descDetail->name);
    }

    $this->setContactInfo();

    $this->setColumnToNotes('maintenanceNote', QubitTerm::MAINTENANCE_NOTE_ID);
  }

  private function getRelatedTermNames($taxonomyId)
  {
    $results = array();

    foreach ($this->resource->getTermRelations($taxonomyId) as $r)
    {
      if ($r->term->name)
      {
        $results[] = $r->term->name;
      }
    }

    return $results;
  }

  private function setContactInfo()
  {
    if (null === $c = $this->resource->getPrimaryContact())
    {
      return;
    }

    $this->setColumn('contactPerson', $c->getContactPerson());
    $this->setColumn('streetAddress', $c->getStreetAddress());
    $this->setColumn('telephone', $c->getTelephone());
    $this->setColumn('email', $c->getEmail());
    $this->setColumn('fax', $c->getFax());
    $this->setColumn('website', $c->getWebsite());
  }

  private function getNames($typeId)
  {
    $results = array();

    foreach ($this->resource->getOtherNames(array('typeId' => $typeId)) as $name)
    {
      $results[] = $name->__toString();
    }

    return $results;
  }
}
