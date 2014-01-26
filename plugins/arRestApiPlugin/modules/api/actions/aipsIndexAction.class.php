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

class APIAIPSIndexAction extends QubitAPIAction
{
  protected function getData($request)
  {
    return array(

      /*
       * Overview
       */
      'overview' => array(
        'total' => array(
          'size' => 1062650070958,
          'count' => 16),
        'artwork' => array(
          'size' => 332430468710,
          'count' => 8),
        'software' => array(
          'size' => 214823526727,
          'count' => 4),
        'documentation' => array(
          'size' => 85899345920,
          'count' => 4),
        'unclassified' => array(
          'size' => 418115066265,
          'count' => 2)),

      /*
       * AIPs
       */
      'aips' => array(
        'results' => array(
          array(
            'name' => 'SymCity_Box_scan_1-1-F9506513-0A19-41B4-B44B-D1A9F86ABEEA',
            'uuid' => 'f9506513-0a19-41b4-b44b-d1a9f86abeea',
            'size' => 15762529976,
            'created_at' => '2013-08-21 11:45:06 EST',
            'class' => 'Unclassified',
            'parent' => array(
              'id' => 1,
              'title' => 'SimCity 2000'),
            'part_of' => array(
              'id' => 1,
              'title' => 'SimCity 2000')),
          array(
            'name' => 'SymCity_Box_scan_2-1-F9506513-0A19-41B4-B44B-D1A9F86ABEEA',
            'uuid' => 'f9506513-0a19-41b4-b44b-d1a9f86abeea',
            'size' => 25762529976,
            'created_at' => '2013-08-21 11:45:06 EST',
            'class' => 'Unclassified',
            'parent' => array(
              'id' => 1,
              'title' => 'SimCity 2000'),
            'part_of' => array(
              'id' => 1,
              'title' => 'SimCity 2000')),
          array(
            'name' => 'SymCity_Box_scan_3-1-F9506513-0A19-41B4-B44B-D1A9F86ABEEA',
            'uuid' => 'f9506513-0a19-41b4-b44b-d1a9f86abeea',
            'size' => 35762529976,
            'created_at' => '2013-08-21 11:45:06 EST',
            'class' => 'Unclassified',
            'parent' => array(
              'id' => 1,
              'title' => 'SimCity 2000'),
            'part_of' => array(
              'id' => 1,
              'title' => 'SimCity 2000')),
        ),

      /*
       * Facets
       */
      'facets' => array(
        'class' => array(
          'terms' => array(
            array(
              'term' => 'Artwork',
              'count' => 10),
            array(
              'term' => 'Software',
              'count' => 4),
            array(
              'term' => 'Documentation',
              'count' => 3),
            array(
              'term' => 'Unclassified',
              'count' => 3))),
        'object_type' => array(
          'terms' => array(
            array(
              'term' => 'Image',
              'count' => 10),
            array(
              'term' => 'Audio',
              'count' => 3),
            array(
              'term' => 'Video',
              'count' => 2),
            array(
              'term' => 'Text',
              'count' => 4),
            array(
              'term' => 'Other',
              'count' => 32))))),

      /*
       * TMS Work MetaData
       */
      'tms_metadata' => array(
        'accession_id' => '1098.2005.a-c',
        'object_id' => '100620',
        'title' => 'Play Dead; Real time',
        'date' => '2003',
        'artist' => 'Douglas Gordon',
        'medium' => 'Three-channel video',
        'dimensions' => '19:11 min, 14:44 min. (on larger screens), 21:58 min. (on monitor). Minimum Room Size: 24.8m x 13.07m',
        'description' => 'Exhibition materials: 3 DVD and players, 2 projectors, 3 monitor, 2 screens. The complete work is a three-screen piece, consisting of one retro projection, one front projection and one monitor. See file for installation instructions. One monitor and two projections on screens 19.69 X 11.38 feet. Viewer must be able to walk around screens.'),

      /*
       * Digital Objects
       */
      'digital_objects' => array(
        'storage_total' => '10776432223432',
        'related_total' => array(
          'digital_objects' => 1,
          'aips' => 12),
        'objects' => array(
          'artwork' => array(
            'total' => 1,
            'total_size' => '262453654232'),
          'documentation' => array(
            'total' => 0,
            'total_size' => '0'),
          'unclassified' => array(
            'total' => 0,
            'total_size' => '0')))
      );

  }
}
