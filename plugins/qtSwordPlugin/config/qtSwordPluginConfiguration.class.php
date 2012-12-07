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

class qtSwordPluginConfiguration extends sfPluginConfiguration
{
  public static
    $summary = 'SWORD plugin.',
    $version = '1.0.0',

    // See http://www.swordapp.org/docs/sword-type-1.0.html
    $packaging = array(
      // '1.0' => 'http://www.loc.gov/METS/'), // METS
      '1.0' => 'http://purl.org/net/sword-types/METSArchivematicaDIP'), // METS

    $mediaRanges = array(
      'application/zip');

  public function initialize()
  {
    $enabledModules = sfConfig::get('sf_enabled_modules');
    $enabledModules[] = 'qtSwordPlugin';
    sfConfig::set('sf_enabled_modules', $enabledModules);
  }
}
