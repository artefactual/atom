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

class MenuChangeLanguageMenuComponent extends sfComponent
{
  public function execute($request)
  {
    // The following snippet populates a list of language codes based on the
    // languages that the administrator have made available via settings. We're
    // using sfConfig as it's a cached source. An alternative would be to cache
    // the component and use QubitSetting instead but we discarded that option
    // in the past for some reason that I can't remember now.

    $this->langCodes = array();

    $prefix = 'app_i18n_languages';
    $prefixLength = strlen($prefix);
    $suffix = '__source';
    $suffixLength = strlen($suffix);

    foreach (sfConfig::getAll() as $name => $value)
    {
      // Omit if prefix does not match
      if ($prefix !== substr($name, 0, $prefixLength))
      {
        continue;
      }

      // Omit if suffix does not match
      if (substr_compare($name, $suffix, strlen($name) - $suffixLength, $suffixLength) === 0)
      {
        continue;
      }

      $this->langCodes[] = $value;
    }
  }
}
