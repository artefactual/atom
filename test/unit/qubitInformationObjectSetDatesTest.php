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

$t = new lime_test(14, new lime_output_color);

$t->diag('Initializing configuration.');
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

function testGetDefaultDateValue($normalizedDates)
{
  $io = new QubitInformationObject;
  $io->setDates('foobar', array('normalized_dates' => $normalizedDates));

  $io->save();

  $io = QubitInformationObject::getById($io->id);

  $event = $io->eventsRelatedByobjectId[0];

  return array($event->startDate, $event->endDate, $io->id);
}

list($start, $end, $io) = testGetDefaultDateValue('1984-12-31/2056');
$t->is($start, '1984-12-31');
$t->is($end, '2056-00-00');

list($start, $end, $io) = testGetDefaultDateValue('198-12-30/205-10');
$t->is($start, '0198-12-30');
$t->is($end, '0205-10-00');

list($start, $end, $io) = testGetDefaultDateValue('1984-2/20-10-4');
$t->is($start, '1984-02-00');
$t->is($end, '0020-10-04');

list($start, $end, $io) = testGetDefaultDateValue('1984-1-1/2056-00-00');
$t->is($start, '1984-01-01');
$t->is($end, '2056-00-00');

list($start, $end, $io) = testGetDefaultDateValue('4-4-4/205-12');
$t->is($start, '0004-04-04');
$t->is($end, '0205-12-00');

list($start, $end, $io) = testGetDefaultDateValue('5/5-5');
$t->is($start, '0005-00-00');
$t->is($end, '0005-05-00');

list($start, $end, $io) = testGetDefaultDateValue('0-01-3/06-1');
$t->is($start, '0000-01-03');
$t->is($end, '0006-01-00');
