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

class sfIsadPluginEventComponent extends InformationObjectEventComponent
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'date',
      'endDate',
      'startDate',
      'type');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'type':

        if ('arDacsPlugin' == $this->request->module)
        {
          $eventTypes = arDacsPlugin::eventTypes();
        }
        else
        {
          $eventTypes = sfIsadPlugin::eventTypes();
        }

        foreach ($eventTypes as $item)
        {
          // Default event type is creation
          if (QubitTerm::CREATION_ID == $item->id)
          {
            $this->form->setDefault('type', $this->context->routing->generate(null, array($item, 'module' => 'term')));
          }

          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item->__toString();
        }

        $this->form->setValidator('type', new sfValidatorString);
        $this->form->setWidget('type', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      default:

        return parent::addField($name);
    }
  }

  // TODO Refactor with parent::processForm()
  public function processForm()
  {
    $params = array($this->request->editEvent);
    if (isset($this->request->editEvents))
    {
      // If dialog JavaScript did it's work, then use array of parameters
      $params = $this->request->editEvents;
    }

    $finalEventIds = array();

    foreach ($params as $item)
    {
      // Continue only if user typed something
      if (1 > strlen($item['date'])
          && 1 > strlen($item['endDate'])
          && 1 > strlen($item['startDate']))
      {
        continue;
      }

      $this->form->bind($item);
      if ($this->form->isValid())
      {
        if (!isset($this->request->sourceId) && isset($item['id']))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item['id']));

          // Do not add exiting events to the eventsRelatedByobjectId
          // array, as they could be deleted before saving the resource
          $this->event = $params['_sf_route']->resource;
          array_push($finalEventIds, $this->event->id);
        }
        else
        {
          $this->resource->eventsRelatedByobjectId[] = $this->event = new QubitEvent;
        }

        foreach ($this->form as $field)
        {
          if (isset($item[$field->getName()]))
          {
            $this->processField($field);
          }
        }

        // Save existing events as they are not attached
        // to the eventsRelatedByobjectId array
        if (isset($this->event->id))
        {
          $this->event->save();
        }
      }
    }

    // Delete the old events if they don't appear in the table (removed by multiRow.js)
    // Check date events as they are the only ones added in this table
    foreach ($this->resource->getDates() as $item)
    {
      if (isset($item->id) && false === array_search($item->id, $finalEventIds))
      {
        $item->delete();
      }
    }
  }
}
