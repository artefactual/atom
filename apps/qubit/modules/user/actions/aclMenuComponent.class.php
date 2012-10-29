<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Display "aclMenu" navigation menu
 *
 * @package    AtoM
 * @subpackage user
 * @author     David Juhasz <david@artefactual.com>
 */
class userAclMenuComponent extends sfComponent
{
  public function execute($request)
  {
    // Get menu
    $criteria = new Criteria;
    $criteria->add(QubitMenu::NAME, 'users');

    $this->userAclMenu = null;
    if (null !== $menu = QubitMenu::getOne($criteria))
    {
      $this->userAclMenu = $menu;
    }
  }
}
