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

class QubitValidatorDates extends sfValidatorBase
{
  protected function doClean($value)
  {
    foreach ($value->eventsRelatedByobjectId as $item)
    {
      $valid = true;

      // Only validate this event if it has start or end date
      if (isset($item->startDate) || isset($item->endDate))
      {
        // Find first ancestor with an event, with a start or end date, of same
        // type as event we're validating
        foreach ($value->ancestors->orderBy('rgt') as $ancestor)
        {
          foreach ($ancestor->getDates(array('type_id' => $item->type->id)) as $event)
          {
            // Found at least one such event, if event we're validating isn't
            // valid according to at least one of this ancestor's events, then
            // it's invalid
            $valid = false;

            // Valid according to this event?  Start date is greater than or
            // equal and end date is less than or equal, or start or end dates
            // are missing
            if ((!isset($item->startDate)
                  || ((!isset($event->startDate)
                      || new DateTime($item->startDate) >= new DateTime($event->startDate))
                    && (!isset($event->endDate)
                      || new DateTime($item->startDate) <= new DateTime($event->endDate))))
                && (!isset($item->endDate)
                  || ((!isset($event->startDate)
                      || new DateTime($item->endDate) >= new DateTime($event->startDate))
                    && (!isset($event->endDate)
                      || new DateTime($item->endDate) <= new DateTime($event->endDate)))))
            {
              // Valid!  Check next event
              continue 3;
            }
          }

          // If event isn't in at least one of the ranges for this ancestor,
          // then throw validation error
          if (!$valid)
          {
            throw new sfValidatorError($this, 'invalid', array('ancestor' => sfContext::getInstance()->routing->generate(null, array($ancestor, 'module' => 'informationobject'))));
          }
        }
      }
    }

    return $value;
  }
}
