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

class sfDcPluginDcNamesComponent extends InformationObjectEventComponent
{
  public static
    $NAMES = array(
      'actor',
      'type');

  public function processForm()
  {
    $params = array();
    if (isset($this->request->editNames))
    {
      $params = $this->request->editNames;
    }

    $dontDeleteIds = array();
    foreach ($params as $item)
    {
      if (isset($item['id']))
      {
        $dontDeleteIds[] = $item['id'];
      }
      // Continue only if user typed something
      if (1 > strlen($item['actor']))
      {
        continue;
      }

      $this->form->bind($item);
      if ($this->form->isValid())
      {
        $this->event = null;
        if (isset($item['id']))
        {
          $this->event = QubitEvent::getById($item['id']);
        }
        if (is_null($this->event))
        {
          $this->resource->eventsRelatedByobjectId[] = $this->event = new QubitEvent;
        }

        $dontDeleteIds[] = $this->event->id;

        foreach ($this->form as $field)
        {
          if (isset($item[$field->getName()]))
          {
            $this->processField($field);
          }
        }
      }
    }

    foreach ($this->resource->getActorEvents() as $item)
    {
      if (isset($item->actor) && false === array_search($item->id, $dontDeleteIds))
      {
        $item->delete();
      }
    }
  }
}
