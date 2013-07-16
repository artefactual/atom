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
  public static function serialize($object)
  {
    $serialized = array();

    $serialized['id'] = $object->id;
    $serialized['slug'] = $object->slug;

    $serialized['taxonomyId'] = $object->taxonomy_id;

    $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
    foreach (QubitPdo::fetchAll($sql, array($object->id, QubitTerm::ALTERNATIVE_LABEL_ID)) as $item)
    {
      $serialized['useFor'][] = arElasticSearchOtherName::serialize($item);
    }

    $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($object->created_at);
    $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($object->updated_at);

    $serialized['sourceCulture'] = $object->source_culture;
    $serialized['i18n'] = arElasticSearchModelBase::serializeI18ns($object->id, array('QubitTerm'));

    return $serialized;
  }
}
