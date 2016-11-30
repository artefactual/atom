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

class ObjectClipboardButtonComponent extends sfComponent
{
  public function execute($request)
  {
    $this->url = url_for(array('module' => 'user', 'action' => 'clipboardToggleSlug'));
    $this->class = 'clipboard';

    $i18n = $this->context->i18n;

    if ($this->wide)
    {
      $this->class .= '-wide';
      $this->tooltip = false;
      $title = $i18n->__('Add');
      $altTitle = $i18n->__('Remove');
    }
    else
    {
      $this->tooltip = true;
      $title = $i18n->__('Add to clipboard');
      $altTitle = $i18n->__('Remove from clipboard');
    }

    if ($this->context->user->getClipboard()->has($this->slug))
    {
      $this->class .= ' added';
      $this->title = $altTitle;
      $this->altTitle = $title;
    }
    else
    {
      $this->title = $title;
      $this->altTitle = $altTitle;
    }

    // Mix in repository page specific styles
    if (!empty($this->repositoryOrDigitalObjBrowse))
    {
      $this->class .= ' repository-or-digital-obj-browse';
    }
  }
}
