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

class ApiInformationObjectsReadAction extends QubitApiAction
{
  protected function get($request)
  {
    try
    {
      $result = QubitSearch::getInstance()->index->getType('QubitInformationObject')->getDocument($this->request->id);
    }
    catch (\Elastica\Exception\NotFoundException $e)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    $doc = $result->getData();
    $data = array();

    $data['id'] = (int)$result->getId();
    $data['level_of_description_id'] = (int)$doc['levelOfDescriptionId'];
    $data['parent_id'] = (int)$doc['parentId'];

    if ($data['parent_id'] > 1)
    {
      $parentIo = QubitInformationObject::getById($data['parent_id']);
      $this->addItemToArray($data, 'parent', $parentIo->getTitle(array('cultureFallback' => true)));
    }

    $this->addItemToArray($data, 'identifier', $doc['identifier']);
    $this->addItemToArray($data, 'title', get_search_i18n($doc, 'title'));
    $this->addItemToArray($data, 'description', get_search_i18n($doc, 'scopeAndContent'));
    $this->addItemToArray($data, 'format', get_search_i18n($doc, 'extentAndMedium'));
    $this->addItemToArray($data, 'source', get_search_i18n($doc, 'locationOfOriginals'));
    $this->addItemToArray($data, 'rights', get_search_i18n($doc, 'accessConditions'));

    // TODO: It's bad that we need to hit the database, would be good to use
    // just one single source (ES), but I don't have time to update the index
    // right now with the info that I need here.
    if (null === $io = QubitInformationObject::getById($this->request->id))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    // Dublin Core types
    $types = array();
    foreach ($io->getTermRelations(QubitTaxonomy::DC_TYPE_ID) as $item)
    {
      $types[] = array(
        'id' => (int)$item->term->id,
        'name' => $item->term->getName(array('cultureFallback' => true)));
    }
    $this->addItemToArray($data, 'types', $types);

    // Names (it doesn't do culture fallback)
    $names = array();
    $sql = 'SELECT
              event.id,
              event.type_id,
              term_i18n.name AS type,
              actor.id AS actor_id,
              actor_i18n.authorized_form_of_name
            FROM event
            LEFT JOIN term_i18n ON (event.type_id = term_i18n.id)
            LEFT JOIN actor ON (event.actor_id = actor.id)
            LEFT JOIN actor_i18n ON (actor.id = actor_i18n.id)
            WHERE
                  event.type_id IS NOT NULL
              AND event.information_object_id = ?
              AND term_i18n.culture = ?
              AND actor_i18n.culture = ?';
    foreach (QubitPdo::fetchAll($sql, array(
      $io->id,
      'en',
      'en')) as $item)
    {
      $names[] = array(
        'event_id' => (int)$item->id,
        'actor_id' => (int)$item->actor_id,
        'type_id' => (int)$item->type_id,
        'type' => $item->type,
        'authorized_form_of_name' => $item->authorized_form_of_name);
    }
    $this->addItemToArray($data, 'names', $names);

    // Dates
    $dates = array();
    $sql = 'SELECT
              event.id,
              event.type_id,
              event.start_date,
              event.end_date,
              event_i18n.date
            FROM event
            LEFT JOIN event_i18n ON (event.id = event_i18n.id AND event.source_culture = event_i18n.culture)
            WHERE
                  event.information_object_id = ?
              AND (event.start_date IS NOT NULL OR event.end_date IS NOT NULL OR event_i18n.date IS NOT NULL)';
    foreach (QubitPdo::fetchAll($sql, array($io->id,)) as $item)
    {
      $dates[] = array(
        'event_id' => (int)$item->id,
        'type_id' => (int)$item->type_id,
        'start_date' => $item->start_date,
        'end_date' => $item->end_date,
        'date' => $item->date);
    }
    $this->addItemToArray($data, 'dates', $dates);

    return $data;
  }

  protected function put($request, $payload)
  {
    $io = $this->fetchInformationObjectOr404();

    // TODO: restrict to allowed fields
    foreach ($payload as $field => $value)
    {
      $field = lcfirst(sfInflector::camelize($field));
      $io->$field = $value;
    }

    $io->save();

    return $this->get($request);
  }

  protected function fetchInformationObjectOr404()
  {
    if (QubitInformationObject::ROOT_ID === (int)$this->request->id)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    if (null === $io = QubitInformationObject::getById($this->request->id))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    return $io;
  }
}
