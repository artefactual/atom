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

/*
 * Update links in static page content.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0190
{
  const
    VERSION = 190, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    $pages = ['about', 'home'];

    $replacements = [
      'https://www.ica-atom.org/doc/Main_Page/fr' => 'https://www.accesstomemory.org/fr/docs',
      'http://ica-atom.org/docs'                  => 'https://www.accesstomemory.org/en/docs',
      'http://ica-atom.org/about#partners'        => 'https://wiki.accesstomemory.org/wiki/Community'
    ];

    // Cycle through each static page and replace old links with newer ones in the DB
    foreach ($pages as $slug)
    {
      $page = QubitStaticPage::getBySlug($slug);

      foreach ($replacements as $target => $replacement)
      {
        $sql = "UPDATE static_page_i18n
          SET content=REPLACE(content, ?, ?)
          WHERE id=?";

        QubitPdo::modify($sql, array($target, $replacement, $page->id));
      }
    }

    return true;
  }
}
