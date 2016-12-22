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

require_once dirname(__FILE__).'/../bootstrap/unit.php';

// Is this the best way to ship path info prefix into ::pathInfo()? I think
// using a real sfWebRequest means including some of its behavior in tests,
// which is possibly not what we want. Should we stub sfContext too? Or ship
// path info prefix into ::pathInfo() some other way?
class sfWebRequestStub
{
  public function getPathInfoPrefix()
  {
    return $this->pathInfoPrefix;
  }
}

$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration)->request = new sfWebRequestStub;

$t = new lime_test(19, new lime_output_color);

sfContext::getInstance()->request->pathInfoPrefix = '/aaa/bbb';

$t->is(Qubit::pathInfo('/aaa/bbb/ccc/ddd'), '/ccc/ddd',
  '"::pathInfo()" with prefix');

sfContext::getInstance()->request->pathInfoPrefix = null;

$t->is(Qubit::pathInfo('/aaa/bbb/ccc/ddd'), '/aaa/bbb/ccc/ddd',
  '"::pathInfo()" without prefix');


/*
 * Qubit::renderDate
 */

$tests = array(
  # GIVEN - EXPECTED
  array('1992-00-00', '1992'),
  array('1992-12-00', '1992-12'),
  array('1992-08-00', '1992-08'),
  array('1992-8-00', '1992-8'),
  array('1992-8-0', '1992-8'),

  array('1992-01-02', '1992-01-02'),
  array('1992-01-01', '1992-01-01'),
  array('1992-6-9', '1992-6-9'),
  array('1992-06-9', '1992-06-9'),
  array('1992-6-09', '1992-6-09'),
  array('1992-08-12', '1992-08-12'),
  array('1992-6-16', '1992-6-16'),
  array('1992-06-16', '1992-06-16'),

  array('1992-12-12', '1992-12-12'),
  array('1992-12-6', '1992-12-6'),
  array('1992-12-06', '1992-12-06'),
  array('1992-12-16', '1992-12-16'),
);

foreach ($tests as $item)
{
  list($given, $expected) = $item;

  $t->is(Qubit::renderDate($given), $expected, "renderDate() renders date $given as $expected");
}
