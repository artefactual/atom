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

class InformationObjectTranslationLinksComponent extends sfComponent
{
  public function execute($request)
  {
    $currentCulture = $this->getUser()->getCulture();

    // Return nothing if the resource only has the current culture
    if (count($this->resource->informationObjectI18ns) == 1
      && $this->resource->informationObjectI18ns[0]->culture == $currentCulture)
    {
      return sfView::NONE;
    }

    // Get other cultures available
    $this->translations = array();
    foreach ($this->resource->informationObjectI18ns as $i18n)
    {
      if ($i18n->culture !== $currentCulture)
      {
        $this->translations[ucfirst(format_language($i18n->culture, $i18n->culture))] = isset($i18n->title) ? $i18n->title : $this->resource->getTitle(array('sourceCulture' => true));
      }
    }
  }
}
