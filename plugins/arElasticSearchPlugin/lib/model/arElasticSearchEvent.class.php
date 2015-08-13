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

class arElasticSearchEvent extends arElasticSearchModelBase
{
  public static function serialize($event)
  {
    $serialized = array();

    if (isset($event->start_date))
    {
      $serialized['startDate'] = arElasticSearchPluginUtil::normalizeDateWithoutMonthOrDay($event->start_date);
      $serialized['startDateString'] = Qubit::renderDate($event->start_date);
    }

    if (isset($event->end_date))
    {
     $serialized['endDate'] = arElasticSearchPluginUtil::normalizeDateWithoutMonthOrDay($event->end_date, true);
     $serialized['endDateString'] = Qubit::renderDate($event->end_date);
    }

    if (isset($event->actor_id))
    {
      $serialized['actorId'] = $event->actor_id;
    }

    $serialized['typeId'] = $event->type_id;
    $serialized['sourceCulture'] = $event->source_culture;

    $serialized['i18n'] = self::serializeI18ns($event->id, array('QubitEvent'));

    return $serialized;
  }
}
