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

$t = new lime_test(8, new lime_output_color);

$t->diag('Initializing configuration.');
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

$informationObject = new QubitInformationObject;
$informationObject->title = 'test title';

$event = new QubitEvent;
$event->startDate = '2001-02-03';
$event->endDate = '2004-05-06';
$event->typeId = QubitTerm::CREATION_ID;
$informationObject->eventsRelatedByobjectId[] = $event;

$informationObject->save();

$events = $informationObject->getDates();

$t->diag('Count database results');
$t->is(count($events), 1);

$t->diag('Check that the date columns are strings');
$t->isa_ok($event->startDate, 'string', '"->startDate" returns string');
$t->is($event->startDate, '2001-02-03');
$t->isa_ok($event->endDate, 'string', '"->endDate" returns string');
$t->is($event->endDate, '2004-05-06');

$t->diag('Save as DateTime');
$event->startDate = new DateTime('2011-11-11');
$event->save();
$t->isa_ok($event->startDate, 'DateTime', '"->startDate" returns DateTime (is this correct?)');
$t->is($event->startDate->format('Y-m-d'), '2011-11-11');

$t->diag('Check that internally is still using string');
$event = QubitEvent::getById($event->id);
$t->isa_ok($event->startDate, 'string', '"->startDate" returns string');

// TODO: test inconsistencies with updatedAt and createdAt
// The types returned are different between QubitActor, QubitRepository and QubitUser

$informationObject->delete();
