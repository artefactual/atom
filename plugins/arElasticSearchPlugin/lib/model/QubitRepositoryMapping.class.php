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
 * This class is used to provide a model mapping for storing QubitRepository objects
 * within an ElasticSearch document index.
 *
 * @package    arElasticSearchPlugin
 * @author     MJ Suhonos <mj@artefactual.com>
 */
class QubitRepositoryMapping extends QubitMapping
{
  static function serialize($object)
  {
    $serialized = array();
    $serialized['slug'] = $object->slug;
    $serialized['identifier'] = $object->identifier;

    foreach ($object->getTermRelations(QubitTaxonomy::REPOSITORY_TYPE_ID) as $relation)
    {
      $serialized['types'][] = $relation->termId;
    }

    if ($contact = $object->getPrimaryContact())
    {
      $serialized['contact'] = QubitContactInformationMapping::serialize($contact);
    }
    // TODO: additional contact points if none are primary
    /*
    elseif (count($contacts = $object->getContactInformation()) > 0)
    {
      foreach ($contacts as $contact)
      {

      }
    }
    */

    $serialized['sourceCulture'] = $object->sourceCulture;

    // FIXME: actor properties should be merged on the same culture
    $actorI18ns = $object->actorI18ns->indexBy('culture');
    $serialized['actor'] = self::serializeI18ns(new QubitActor(), $actorI18ns);

    $objectI18ns = $object->repositoryI18ns->indexBy('culture');
    $serialized['i18n'] = self::serializeI18ns($object, $objectI18ns);

    $serialized['createdAt'] = Elastica_Util::convertDate($object->createdAt);
    $serialized['updatedAt'] = Elastica_Util::convertDate($object->updatedAt);

    return $serialized;
  }
}
