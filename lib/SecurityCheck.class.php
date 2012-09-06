<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

class SecurityCheck
{
  public static function hasPermission($sfUser, array $options = array())
  {
    $qubitUser = QubitUser::getById($sfUser->getUserId());
    if (!$qubitUser)
      {
        return false;
      }

    switch($options['module'])
      {
        case 'informationobject':
          if ($sfUser->hasCredential(array('administrator', 'editor', 'contributor'), false))
            {
              return true;
            }
          else
            {
              return false;
            }
        case 'actor':
          if ($sfUser->hasCredential(array('administrator', 'editor', 'contributor'), false))
            {
              return true;
            }
          else
            {
              return false;
            }
         case 'repository':
          if ($sfUser->hasCredential(array('administrator', 'editor', 'contributor'), false))
            {
              return true;
            }
          else
            {
              return false;
            }
         case 'term':
          if ($sfUser->hasCredential(array('administrator', 'editor'), false))
            {
              return true;
            }
          else
            {
              return false;
            }
         case 'staticpage':
          if ($sfUser->hasCredential(array('administrator', 'translator'), false))
            {
              return true;
            }
          else
            {
              return false;
            }
         case 'user':
          if ($sfUser->hasCredential(array('administrator'), false))
            {
              return true;
            }
          else if ($options['action'] == 'show')
            {
              return true;
            }
          else
            {
              return false;
            }
      }

  return false;
  }
}
