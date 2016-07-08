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

class ApiInformationObjectsUpdateAction extends QubitApiAction
{
  private $noteToDelete; // To specify a note to delete after updating

  protected function put($request, $payload)
  {
    if (null === $this->io = QubitObject::getBySlug($request->slug))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    if (QubitInformationObject::ROOT_ID === (int)$this->io->id)
    {
      throw new QubitApiForbiddenException;
    }

    foreach ($payload as $field => $value)
    {
      $this->processField($field, $value);
    }

    $this->io->save();

    if (isset($this->noteToDelete))
    {
      $this->noteToDelete->delete();
    }

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

          $this->io->eventsRelatedByobjectId[] = $event;
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

          $this->io->eventsRelatedByobjectId[] = $event;
        }

        break;

      case 'notes':
        // Multi-value not supported yet!
        if (is_array($value))
        {
          $value = array_pop($value);
        }
        if (empty($value))
        {
          break;
        }
        $note = false;
        if ($this->request->getMethod() === 'PUT')
        {
          foreach ($this->io->getNotes() as $item)
          {
            $note = $item;

            break;
          }
        }

        if (!empty($value->type))
        {
          $combinedNoteTypeData = $this->getNoteTypeData() + $this->getRadNoteTypeData();
          $noteTypeId = array_search($value->type, $combinedNoteTypeData);
        }
        if ($note !== false)
        {
          if (!empty($value->content))
          {
            $note->setContent($value->content);

            if (!empty($noteTypeId))
            {
              $note->setTypeId($noteTypeId);
            }

            $note->save();
          }
          else
          {
            $this->noteToDelete = $note;
          }
        }
        else
        {
          $note = new QubitNote;

          $note->setScope('QubitInformationObject');
          $note->setContent($value->content);

          $noteTypeId = (!empty($noteTypeId)) ? $noteTypeId : QubitTerm::GENERAL_NOTE_ID;
          $note->setTypeId($noteTypeId);

          $this->io->notes[] = $note;
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

  protected function getNoteTypeData()
  {
    return $this->simplifyTermData(QubitTerm::getNoteTypes());
  }

  protected function getRadNoteTypeData()
  {
    return $this->simplifyTermData(QubitTerm::getRADNotes());
  }

  protected function simplifyTermData($terms)
  {
    $termData = array();

    foreach($terms as $term)
    {
      $termData[$term->id] = $term->name;
    }

    return $termData;
  }
}
