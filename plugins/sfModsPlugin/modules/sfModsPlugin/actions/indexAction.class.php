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
 * Information Object - showMods
 *
 * @package    AccesstoMemory
 * @subpackage informationObject - initialize a showMods template for displaying an information object
 * @author     Peter Van Garderen <peter@artefactual.com>
 */

class sfModsPluginIndexAction extends InformationObjectIndexAction
{
  public function execute($request)
  {
    if ('xml' === $request->getRequestFormat())
    {
      sfConfig::set('sf_escaping_strategy', false);
    }

    parent::execute($request);

    $this->mods = new sfModsPlugin($this->resource);

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");
  }
}
