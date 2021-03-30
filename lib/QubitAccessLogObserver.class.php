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

/**
 * Observer of notificiation of access events.
 */
class QubitAccessLogObserver
{
    // access_log.view event listener
    public static function view(sfEvent $event)
    {
        $object = $event['object'];

        $access = new QubitAccessLog();
        $access->objectId = $object->id;
        $access->accessDate = date('Y-m-d H:i:s');

        $access->save();
    }
}
