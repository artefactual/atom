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
 * This class is used to provide a model mapping for storing QubitInformationObject objects
 * within an ElasticSearch document index.
 *
 * @package    qtElasticSearchPlugin
 * @author     MJ Suhonos <mj@artefactual.com>
 */
class QubitInformationObjectMapping extends QubitMapping
{
  static function getProperties()
  {
    return array(
      'slug' => array(
        'type' => 'string',
        'index' => 'not_analyzed'),
      'referenceCode' => array(
        'type' => 'string',
        'index' => 'not_analyzed'),
      'identifier' => array(
        'type' => 'string',
        'index' => 'not_analyzed'),
      'levelOfDescriptionId' => array(
        'type' => 'integer',
        'index' => 'not_analyzed',
        'include_in_all' => false),
      'publicationStatusId' => array(
        'type' => 'integer',
        'index' => 'not_analyzed',
        'include_in_all' => false),
      'parentId' => array(
        'type' => 'integer',
        'index' => 'not_analyzed',
        'include_in_all' => false),
      'ancestors' => array(
        'type' => 'integer',
        'index' => 'not_analyzed',
        'include_in_all' => false),
      'children' => array(
        'type' => 'integer',
        'index' => 'not_analyzed',
        'include_in_all' => false),
      'hasDigitalObject' => array(
        'type' => 'boolean',
        'index' => 'not_analyzed',
        'include_in_all' => false),
      'digitalObject' => array(
        'type' => 'object',
        'properties' => array(
          'mediaTypeId' => array(
            'type' => 'integer',
            'index' => 'not_analyzed',
            'include_in_all' => false),
          'thumbnail_FullPath' => array(
            'type' => 'string',
            'index' => 'no'))),
      'materialTypeId' => array(
        'type' => 'integer',
        'index' => 'not_analyzed',
        'include_in_all' => false),
      'copyrightStatusId' => array(
        'type' => 'integer',
        'index' => 'not_analyzed',
        'include_in_all' => false),
      'dates' => array(
        'type' => 'object',
        'properties' => array(
          'startDate' => array(
            'type' => 'integer',
            'index' => 'not_analyzed'),
          'endDate' => array(
            'type' => 'integer',
            'index' => 'not_analyzed'),
          'typeId' => array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'include_in_all' => false),
          'actor' => array(
            'type' => 'string'))),
      'repository' => array(
        'type' => 'object',
        'properties' => QubitMapping::getI18nProperties()),
      'subjects' => array(
        'type' => 'object',
        'properties' => QubitMapping::getI18nProperties()),
      'places' => array(
        'type' => 'object',
        'properties' => QubitMapping::getI18nProperties()),
      'names' => array(
        'type' => 'object',
        'properties' => QubitMapping::getI18nProperties()),
      'creators' => array(
        'type' => 'object',
        'properties' => QubitMapping::getI18nProperties()))
      + self::getI18nProperties();
  }
}
