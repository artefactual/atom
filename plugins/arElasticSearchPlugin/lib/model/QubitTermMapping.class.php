<?php

/*
* This file is part of Qubit Toolkit.
*
* Qubit Toolkit is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Qubit Toolkit is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This class is used to provide a model mapping for storing QubitTerm objects
 * within an ElasticSearch document index.
 *
 * @package    arElasticSearchPlugin
 * @author     MJ Suhonos <mj@artefactual.com>
 */
class QubitTermMapping extends QubitMapping
{
  static function getProperties()
  {
    return array(
      'slug' => array(
        'type' => 'string',
        'index' => 'not_analyzed'),
      'taxonomyId' => array(
        'type' => 'integer',
        'index' => 'not_analyzed',
        'include_in_all' => false))
      + self::getI18nProperties()
      + self::getTimestampProperties();
  }

  static function serialize($object)
  {
    $serialized = array();
    $serialized['slug'] = $object->slug;
    $serialized['taxonomyId'] = $object->taxonomyId;

    $serialized['sourceCulture'] = $object->sourceCulture;
    $objectI18ns = $object->termI18ns->indexBy('culture');
    $serialized['i18n'] = self::serializeI18ns($object, $objectI18ns);

    $serialized['createdAt'] = Elastica_Util::convertDate($object->createdAt);
    $serialized['updatedAt'] = Elastica_Util::convertDate($object->updatedAt);

    return $serialized;
  }
}
