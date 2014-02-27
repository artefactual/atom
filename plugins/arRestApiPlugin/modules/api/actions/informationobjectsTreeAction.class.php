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

class APIInformationObjectsTreeAction extends QubitAPIAction
{
  protected function get($request)
  {
    $data = $this->getTree();

    return $data;
  }

  protected function getTree()
  {
    // Temporary
    return array(
      array('id' => 1, 'title' => 'Play Dead; Real Time', 'children' => array(
        array('id' => 2, 'title' => 'MoMA 2012', 'children' => array(
          array('id' => 3, 'title' => 'Installation documentation'),
          array('id' => 4, 'title' => 'Exhibition files', 'children' => array(
            array('id' => 5, 'title' => '1098.2005.a.AV'),
            array('id' => 6, 'title' => '1098.2005.b.AV'),
            array('id' => 7, 'title' => '1098.2005.c.AV'))))),
        array('id' => 8, 'title' => 'Supplied by artist', 'children' => array(
          array('id' => 9, 'title' => '1098.2005.a.x1', 'children' => array(
            array('id' => 10, 'title' => '1098.2005.a.x2'),
            array('id' => 11, 'title' => '1098.2005.a.x3'))),
          array('id' => 12, 'title' => '1098.2005.b.x1', 'children' => array(
            array('id' => 13, 'title' => '1098.2005.b.x2'),
            array('id' => 14, 'title' => '1098.2005.b.x3'))),
          array('id' => 15, 'title' => '1098.2005.c.x1', 'children' => array(
            array('id' => 16, 'title' => '1098.2005.c.x2'),
            array('id' => 17, 'title' => '1098.2005.c.x3'))))),
        array('id' => 30, 'title' => 'Digital archival masters', 'children' => array(
          array('id' => 31, 'title' => '1098.2005.a.x4'),
          array('id' => 32, 'title' => '1098.2005.b.x4'),
          array('id' => 33, 'title' => '1098.2005.c.x4'))))));
  }
}
