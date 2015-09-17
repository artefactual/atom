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

class InformationObjectClipboardButtonComponent extends sfComponent
{
  public function execute($request)
  {
    $this->url = url_for(array('module' => 'user', 'action' => 'clipboardToggleSlug'));
    $this->class = 'clipboard';

    if ($this->wide)
    {
      $this->class .= '-wide';
      $this->tooltip = false;
    }
    else
    {
      $this->tooltip = true;
    }

    $i18n = $this->context->i18n;

    if ($this->context->user->getClipboard()->has($this->slug))
    {
      $this->class .= ' added';
      $this->title = $i18n->__('Remove from clipboard');
      $this->altTitle = $i18n->__('Add to clipboard');
    }
    else
    {
      $this->title = $i18n->__('Add to clipboard');
      $this->altTitle = $i18n->__('Remove from clipboard');
    }
  }
}
