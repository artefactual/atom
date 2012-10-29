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
 * Build browse menu as simple xhtml lists, relying on css styling to
 * format the display of the menus.
 *
 * @package    AtoM
 * @subpackage menu
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class MenuBrowseMenuComponent extends sfComponent
{
  public function execute($request)
  {
    // Get menu objects
    $this->browseMenu = QubitMenu::getById(QubitMenu::BROWSE_ID);

    if (!$this->browseMenu instanceof QubitMenu)
    {
      return sfView::NONE;
    }
  }
}
