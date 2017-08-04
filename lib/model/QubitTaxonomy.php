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

class QubitTaxonomy extends BaseTaxonomy
{
  public $disableNestedSetUpdating = false;

  const
    ROOT_ID = 30,
    DESCRIPTION_DETAIL_LEVEL_ID = 31,
    ACTOR_ENTITY_TYPE_ID = 32,
    DESCRIPTION_STATUS_ID = 33,
    LEVEL_OF_DESCRIPTION_ID = 34,
    SUBJECT_ID = 35,
    ACTOR_NAME_TYPE_ID = 36,
    NOTE_TYPE_ID = 37,
    REPOSITORY_TYPE_ID = 38,
    EVENT_TYPE_ID = 40,
    QUBIT_SETTING_LABEL_ID = 41,
    PLACE_ID = 42,
    FUNCTION_ID = 43,
    HISTORICAL_EVENT_ID = 44,
    COLLECTION_TYPE_ID = 45,
    MEDIA_TYPE_ID = 46,
    DIGITAL_OBJECT_USAGE_ID = 47,
    PHYSICAL_OBJECT_TYPE_ID = 48,
    RELATION_TYPE_ID = 49,
    MATERIAL_TYPE_ID = 50,

    // Rules for Archival Description (RAD) taxonomies
    RAD_NOTE_ID = 51,
    RAD_TITLE_NOTE_ID = 52,

    MODS_RESOURCE_TYPE_ID = 53,
    DC_TYPE_ID = 54,
    ACTOR_RELATION_TYPE_ID = 55,
    RELATION_NOTE_TYPE_ID = 56,
    TERM_RELATION_TYPE_ID = 57,
    STATUS_TYPE_ID = 59,
    PUBLICATION_STATUS_ID = 60,
    ISDF_RELATION_TYPE_ID = 61,

    // Accession taxonomies
    ACCESSION_RESOURCE_TYPE_ID = 62,
    ACCESSION_ACQUISITION_TYPE_ID = 63,
    ACCESSION_PROCESSING_PRIORITY_ID = 64,
    ACCESSION_PROCESSING_STATUS_ID = 65,
    DEACCESSION_SCOPE_ID = 66,

    // Right taxonomies
    RIGHT_ACT_ID = 67,
    RIGHT_BASIS_ID = 68,
    COPYRIGHT_STATUS_ID = 69,

    // Metadata templates
    INFORMATION_OBJECT_TEMPLATE_ID = 70,

    // Metadata templates
    AIP_TYPE_ID = 71,

    THEMATIC_AREA_ID = 72,
    GEOGRAPHIC_SUBREGION_ID = 73,

    // DACS notes
    DACS_NOTE_ID = 74,

    // PREMIS Rights Statues
    RIGHTS_STATUTES_ID = 75,

    // Genre taxonomy
    GENRE_ID = 78,

    JOB_STATUS_ID = 79,

    ACTOR_OCCUPATION_ID = 80;

  public static
    $lockedTaxonomies = array(
      self::QUBIT_SETTING_LABEL_ID,
      self::COLLECTION_TYPE_ID,
      self::DIGITAL_OBJECT_USAGE_ID,
      self::MEDIA_TYPE_ID,
      self::RELATION_TYPE_ID,
      self::RELATION_NOTE_TYPE_ID,
      self::TERM_RELATION_TYPE_ID,
      self::ROOT_ID,
      self::STATUS_TYPE_ID,
      self::PUBLICATION_STATUS_ID,
      self::ACTOR_NAME_TYPE_ID,
      self::INFORMATION_OBJECT_TEMPLATE_ID,
      self::JOB_STATUS_ID);

  public function __toString()
  {
    if (!$this->getName())
    {
      return (string) $this->getName(array('sourceCulture' => true));
    }

    return (string) $this->getName();
  }

  protected function insert($connection = null)
  {
    if (!isset($this->slug))
    {
      $this->slug = QubitSlug::slugify($this->__get('name', array('sourceCulture' => true)));
    }

    return parent::insert($connection);
  }

  public static function getRoot()
  {
    return parent::getById(self::ROOT_ID);
  }

  public static function addEditableTaxonomyCriteria($criteria)
  {
    $criteria->add(QubitTaxonomy::ID, self::$lockedTaxonomies, Criteria::NOT_IN);

    return $criteria;
  }

  public static function getEditableTaxonomies()
  {
    $criteria = new Criteria;
    $criteria = self::addEditableTaxonomyCriteria($criteria);

    // Add criteria to sort by name with culture fallback
    $criteria->addAscendingOrderByColumn('name');
    $options = array('returnClass'=>'QubitTaxonomy');
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTaxonomy', $options);

    return QubitTaxonomy::get($criteria);
  }

  public static function getTaxonomyTerms($taxonomyId, $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);

    // Only include top-level terms if option is set
    if (isset($options['level']) && $options['level'] == 'top')
    {
      $criteria->add(QubitTerm::PARENT_ID, QubitTerm::ROOT_ID);
    }

    // Sort alphabetically
    $criteria->addAscendingOrderByColumn('name');

    // Do source culture fallback
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');

    return QubitTerm::get($criteria);
  }
}
