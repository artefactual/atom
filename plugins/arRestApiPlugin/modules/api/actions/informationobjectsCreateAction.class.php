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

class ApiInformationObjectsCreateAction extends QubitApiAction
{
  /**
   * TODO: Share code with ApiInformationObjectsUpdateAction
   */
  protected function post($request, $payload)
  {
    if (QubitInformationObject::ROOT_ID === (int)$request->id)
    {
      throw new QubitApiForbiddenException;
    }

    $this->io = new QubitInformationObject();
    $this->io->parentId = QubitInformationObject::ROOT_ID;
    $this->io->setPublicationStatusByName('Published');

    foreach ($payload as $field => $value)
    {
      $this->processField($field, $value);
    }

    $this->io->save();

    $this->response->setStatusCode(201);

    // TODO: return full object
    return array(
      'id' => (int)$this->io->id,
      'parent_id' => (int)$this->io->parentId);
  }

  protected function processField($field, $value)
  {
    switch ($field)
    {
      case 'identifier':
      case 'level_of_description_id':
      case 'parent_id':
      case 'title':
        $field = lcfirst(sfInflector::camelize($field));
        $this->io->$field = $value;

        break;

      case 'description':
        $this->io->scopeAndContent = $value;

        break;

      case 'format':
        $this->io->extentAndMedium = $value;

        break;

      case 'source':
        $this->io->locationOfOriginals = $value;

        break;

      case 'rights':
        $this->io->accessConditions = $value;

        break;

      case 'names':
        // Multi-value not supported yet!
        if (is_array($value))
        {
          $value = array_pop($value);
        }
        if (empty($value) || empty($value->type_id))
        {
          break;
        }
        $event = false;
        if ($this->request->getMethod() === 'PUT')
        {
          foreach ($this->io->getActorEvents() as $item)
          {
            $event = $item;

            break;
          }
        }

        // The user passed a name but not the ID so I'll create
        if (isset($value->authorized_form_of_name) && !isset($value->actor_id))
        {
          $actor = new QubitActor;
          $actor->authorizedFormOfName = $value->authorized_form_of_name;
          $actor->save();

          $value->actor_id = $actor->id;
        }

        if ($event !== false)
        {
          $event->typeId = $value->type_id;
          $event->actorId = $value->actor_id;
          $event->save();
        }
        else
        {
          $event = new QubitEvent;
          $event->typeId = $value->type_id;
          $event->actorId = $value->actor_id;

          $this->io->events[] = $event;
        }

        break;

      case 'dates':
        // Multi-value not supported yet!
        if (is_array($value))
        {
          $value = array_pop($value);
        }
        if (empty($value))
        {
          break;
        }
        $event = false;
        if ($this->request->getMethod() === 'PUT')
        {
          foreach ($this->io->getDates() as $item)
          {
            $event = $item;

            break;
          }
        }

        if ($event !== false)
        {
          $event->startDate = $value->start_date;
          $event->endDate = $value->end_date;
          $event->date = $value->date;
          $event->save();
        }
        else
        {
          $event = new QubitEvent;
          $event->startDate = $value->start_date;
          $event->endDate = $value->end_date;
          $event->date = $value->date;
          $event->typeId = QubitTerm::CREATION_ID;

          $this->io->events[] = $event;
        }

        break;

      case 'types':
        // Multi-value not supported yet!
        if (is_array($value))
        {
          $value = array_pop($value);
        }
        if (empty($value))
        {
          break;
        }
        $relation = false;
        foreach ($this->io->getTermRelations(QubitTaxonomy::DC_TYPE_ID) as $item)
        {
          $relation = $item;

          break;
        }
        if ($relation !== false)
        {
          $relation->termId = $value->id;
          $relation->save();
        }
        else
        {
          $relation = new QubitObjectTermRelation;
          $relation->termId = $value->id;

          $this->io->objectTermRelationsRelatedByobjectId[] = $relation;
        }

        break;

      case 'level_of_description':
        $criteria = new Criteria;
        $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
        $criteria->add(QubitTermI18n::NAME, $value, Criteria::LIKE);
        if (null !== $term = QubitTerm::getOne($criteria))
        {
          $this->io->levelOfDescriptionId = $term->id;
        }

        break;
    }
  }
}
