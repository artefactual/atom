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

class AclGroupTermAclFormComponent extends sfComponent
{
  public function execute($request)
  {
    // List of actions without read or translate
    $this->termActions = QubitAcl::$ACTIONS;
    unset($this->termActions['read']);
    unset($this->termActions['translate']);

    // Build separate list of permissions by taxonomy and by object
    $this->taxonomyPermissions = array();
    $this->rootPermissions = array();

    if (0 < count($this->permissions))
    {
      foreach ($this->permissions as $item)
      {
        if ('createTerm' == $item->action)
        {
          if (QubitTaxonomy::ROOT_ID == $item->objectId || null === $item->objectId)
          {
            $this->rootPermissions['create'] = $item;
          }
          else
          {
            $this->taxonomyPermissions[$item->objectId]['create'] = $item;
          }
        }
        else if (null === ($taxonomy = $item->getConstants(array('name' => 'taxonomy'))))
        {
          $this->rootPermissions[$item->action] = $item;
        }
        else
        {
          $this->taxonomyPermissions[$taxonomy][$item->action] = $item;
        }
      }
    }
  }
}
