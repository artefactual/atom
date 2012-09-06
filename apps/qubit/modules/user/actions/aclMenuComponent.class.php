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

/**
 * Display "aclMenu" navigation menu
 *
 * @package    qubit
 * @subpackage user
 * @version    svn: $Id: aclMenuComponent.class.php 10288 2011-11-08 21:25:05Z mj $
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
