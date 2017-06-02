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

class ActorOccupationsComponent extends sfComponent
{
  public function execute($request)
  {
    // Create form. The note field has to be named content to allow translations
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
    $this->form->setValidator('occupation', new sfValidatorString);
    $this->form->setValidator('content', new sfValidatorString);
    $this->form->setWidget('occupation', new sfWidgetFormSelect(array('choices' => array())));
    $this->form->setWidget('content', new sfWidgetFormTextarea);

    $this->occupations = $this->resource->getOccupations();
    $this->occupationsTaxonomy = QubitTaxonomy::getById(QubitTaxonomy::ACTOR_OCCUPATION_ID);
  }

  public function processForm()
  {
    $finalOccupations = array();

    if (is_array($this->request->occupations))
    {
      foreach ($this->request->occupations as $item)
      {
        // Continue only if occupation field is populated
        if (strlen($item['occupation']) < 1)
        {
          continue;
        }

        $relation = null;
        if (isset($item['id']))
        {
          $relation = QubitObjectTermRelation::getById($item['id']);

          // Store occupations that haven't been deleted by multiRow.js
          $finalOccupations[] = $relation->id;
        }

        if (is_null($relation))
        {
          $relation = new QubitObjectTermRelation;
          $this->resource->objectTermRelationsRelatedByobjectId[] = $relation;
        }

        $params = $this->context->routing->parse(Qubit::pathInfo($item['occupation']));
        $relation->term = $params['_sf_route']->resource;

        // Attach note to new relations if populated
        if (!isset($item['id']) && strlen($item['content']) > 0)
        {
          $relation->notes[] = $note = new QubitNote;
          $note->typeId = QubitTerm::ACTOR_OCCUPATION_NOTE_ID;
          $note->content = $item['content'];
        }

        // Save the old relations, because adding an existing relation with
        // "$this->resource->objectTermRelations[] =" overrides the unsaved changes
        if (isset($item['id']))
        {
          // Check existing note
          $note = $relation->getNotesByType(array(
            'noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID
          ))->offsetGet(0);

          if (!isset($note) && strlen($item['content']) > 0)
          {
            // Add new note
            $relation->notes[] = $note = new QubitNote;
            $note->typeId = QubitTerm::ACTOR_OCCUPATION_NOTE_ID;
            $note->content = $item['note'];
          }
          else if (isset($note) && strlen($item['content']) > 0)
          {
            // Update note
            $note->content = $item['content'];
            $note->save();
          }
          else if (isset($note) && strlen($item['content']) < 1)
          {
            $deleteNote = true;

            // Check other cultures
            foreach ($note->noteI18ns as $i18n)
            {
              // If there is a content in other culture do not delete the note, just update
              if ($i18n->culture !== $this->context->user->getCulture() && !empty($i18n->content))
              {
                $note->content = $item['content'];
                $note->save();

                $deleteNote = false;

                break;
              }
            }

            // Delete note without content in any culture
            if ($deleteNote)
            {
              $note->delete();
            }
          }

          $relation->save();
        }
      }
    }

    // Delete the old relations if they don't appear in the table (removed by multiRow.js)
    foreach ($this->occupations as $item)
    {
      if (false === array_search($item->id, $finalOccupations))
      {
        $item->delete();
      }
    }
  }
}
