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

    $sql  = 'SELECT term.id, term.source_culture, slug, taxonomy_id, created_at, updated_at';
    $sql .= ' FROM '.QubitTerm::TABLE_NAME.' term';
    $sql .= ' JOIN '.QubitObject::TABLE_NAME.' object ON (term.id = object.id)';
    $sql .= ' JOIN '.QubitSlug::TABLE_NAME.' slug ON (term.id = slug.object_id)';
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

    $serialized['taxonomyId'] = $object->taxonomy_id;

    $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($object->createdAt);
    $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($object->updatedAt);

    $serialized['sourceCulture'] = $object->source_culture;
    $serialized['i18n'] = self::serializeI18ns($object->id, array('QubitTerm'));

    return $serialized;
  }

  public static function update($object)
  {
    $data = self::serialize($object);

    QubitSearch::getInstance()->addDocument($data, 'QubitTerm');

    return true;
  }
}
