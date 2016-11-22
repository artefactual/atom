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

class menuClipboardMenuComponent extends sfComponent
{
  public function execute($request)
  {
    $this->menu = QubitMenu::getByName('clipboard');
    if (null === $this->menu || !$this->menu->hasChildren())
    {
      return sfView::NONE;
    }

    $this->countByType = $this->context->user->getClipboard()->countByType();
    $this->count = array_sum($this->countByType);

    $this->objectTypes = array(
      'QubitInformationObject' => sfConfig::get('app_ui_label_informationobject'),
      'QubitActor' => sfConfig::get('app_ui_label_actor'),
      'QubitRepository' => sfConfig::get('app_ui_label_repository'));
  }
}
