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

class arElasticSearchRepository extends arElasticSearchModelBase
{
  public function populate()
  {
    $errors = array();

    $criteria = new Criteria;
    $criteria->add(QubitRepository::ID, QubitRepository::ROOT_ID, Criteria::NOT_EQUAL);
    $repositories = QubitRepository::get($criteria);

    $this->count = count($repositories);

    foreach ($repositories as $key => $repository)
    {
      try
      {
        $data = self::serialize($repository);

        $this->search->addDocument($data, 'QubitRepository');

        $this->logEntry($repository->__toString(), $key + 1);
      }
      catch (sfException $e)
      {
        $errors[] = $e->getMessage();
      }
    }

    return $errors;
  }

  public static function serialize($object)
  {
    $serialized = array();

    $serialized['id'] = $object->id;
    $serialized['slug'] = $object->slug;

    $serialized['identifier'] = $object->identifier;

    foreach ($object->getTermRelations(QubitTaxonomy::REPOSITORY_TYPE_ID) as $relation)
    {
      $serialized['types'][] = $relation->termId;
    }

    foreach ($object->getTermRelations(QubitTaxonomy::THEMATIC_AREA_ID) as $relation)
    {
      $serialized['thematicAreas'][] = $relation->termId;
    }

    foreach ($object->getTermRelations(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID) as $relation)
    {
      $serialized['geographicSubregions'][] = $relation->termId;
    }

    foreach ($object->contactInformations as $contactInformation)
    {
      $serialized['contactInformations'][] = arElasticSearchContactInformation::serialize($contactInformation);
    }

    $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
    foreach (QubitPdo::fetchAll($sql, array($object->id, QubitTerm::OTHER_FORM_OF_NAME_ID)) as $item)
    {
      $serialized['otherNames'][] = arElasticSearchOtherName::serialize($item);
    }

    $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
    foreach (QubitPdo::fetchAll($sql, array($object->id, QubitTerm::PARALLEL_FORM_OF_NAME_ID)) as $item)
    {
      $serialized['parallelNames'][] = arElasticSearchOtherName::serialize($item);
    }

    if ($object->existsLogo())
    {
      $serialized['logoPath'] = $object->getLogoPath();
    }

    $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($object->createdAt);
    $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($object->updatedAt);

    $serialized['sourceCulture'] = $object->sourceCulture;
    $serialized['i18n'] = self::serializeI18ns($object->id, array('QubitActor', 'QubitRepository'));
    self::addExtraSortInfo($serialized['i18n'], $object);

    return $serialized;
  }

  /**
   * We store extra I18n fields (city, region) for table sorting purposes in the repository browse page.
   *
   * These values will be the city & region of the primary contact if valid, otherwise the first contact
   * that has a valid city / region will be used.
   */
  private static function addExtraSortInfo(&$i18n, $object)
  {
    foreach (QubitSetting::getByScope('i18n_languages') as $setting)
    {
      $lang = $setting->getValue(array('sourceCulture' => true));

      if ($object->getCity(array('culture' => $lang)))
      {
        $i18n[$lang]['city'] = $object->getCity(array('culture' => $lang));
      }

      if ($object->getRegion(array('culture' => $lang)))
      {
        $i18n[$lang]['region'] = $object->getRegion(array('culture' => $lang));
      }
    }
  }

  public static function update($object)
  {
    $data = self::serialize($object);

    QubitSearch::getInstance()->addDocument($data, 'QubitRepository');

    return true;
  }
}
