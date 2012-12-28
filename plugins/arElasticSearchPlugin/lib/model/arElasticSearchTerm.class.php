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

class arElasticSearchTerm extends arElasticSearchModelBase
{
  public function populate()
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    $sql  = 'SELECT term.id';
    $sql .= ' FROM '.QubitTerm::TABLE_NAME.' term';
    $sql .= ' JOIN '.QubitObject::TABLE_NAME.' object ON (term.id = object.id)';
    $sql .= ' WHERE term.taxonomy_id IN (:subject, :place)';
    $sql .= ' AND term.id != '.QubitTerm::ROOT_ID;

    $terms = QubitPdo::fetchAll($sql, array(
      ':subject' => QubitTaxonomy::SUBJECT_ID,
      ':place' => QubitTaxonomy::PLACE_ID));

    $this->count = count($terms);

    foreach ($terms as $key => $item)
    {
      $data = self::serialize($item);

      $this->search->addDocument($data, 'QubitTerm');

      $this->logEntry($data['i18n'][$data['sourceCulture']]['name'], $key + 1);
    }
  }

  public static function serialize($object)
  {
    $serialized = array();

    $serialized['id'] = $object->id;
    $serialized['slug'] = $object->slug;

    $serialized['taxonomyId'] = $object->taxonomyId;

    $serialized['createdAt'] = Elastica_Util::convertDate($object->createdAt);
    $serialized['updatedAt'] = Elastica_Util::convertDate($object->updatedAt);

    $serialized['sourceCulture'] = $object->sourceCulture;
    $serialized['i18n'] = self::serializeI18ns($object);

    return $serialized;
  }
}
