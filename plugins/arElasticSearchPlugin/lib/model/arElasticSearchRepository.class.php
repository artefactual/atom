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
    $criteria = new Criteria;
    $criteria->add(QubitRepository::ID, QubitRepository::ROOT_ID, Criteria::NOT_EQUAL);
    $repositories = QubitRepository::get($criteria);

    $this->count = count($repositories);

    foreach ($repositories as $key => $repository)
    {
      $data = self::serialize($repository);

      $this->search->addDocument($data, 'QubitRepository');

      $this->logEntry($repository->__toString(), $key + 1);
    }
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

    /*
    if ($contact = $object->getPrimaryContact())
    {
      $serialized['contact'] = QubitContactInformationMapping::serialize($contact);
    }
    */

    // TODO: additional contact points if none are primary
    /*
    elseif (count($contacts = $object->getContactInformation()) > 0)
    {
      foreach ($contacts as $contact)
      {

      }
    }
    */

    $serialized['createdAt'] = Elastica_Util::convertDate($object->createdAt);
    $serialized['updatedAt'] = Elastica_Util::convertDate($object->updatedAt);

    $serialized['sourceCulture'] = $object->sourceCulture;
    $serialized['i18n'] = self::serializeI18ns($object, array('QubitActor'));

    return $serialized;
  }
}
