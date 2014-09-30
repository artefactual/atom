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

$t = new lime_test(2, new lime_output_color);

$t->diag('Initializing configuration.');
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

$t->diag('Loading database settings.');
sfConfig::add(QubitSetting::getSettingsArray());

$title1 = uniqid('Description ');
$title2 = uniqid('Description ');
$actor1 = uniqid('Actor ');

// Create information object
$io = new QubitInformationObject;
$io->parentId = QubitInformationObject::ROOT_ID;
$io->title = $title1;
# $io->setActorByName($actor1, array('event_type_id' => QubitTerm::PUBLICATION_ID));
$io->save();
$id = $io->id;
$t->diag('The id is '.$id.'');

// Check if the property is still available after save()
$t->diag('Check the title equals to "'.$title1.'"');
$t->is($io->title, $title1);

// Update the title of the same object and save()
$io->title = $title2;
$io->save();
$t->diag('Change title to "'.$title2.'"');

// Populate a new object, same ID
$io2 = QubitInformationObject::getById($id);
$t->diag('Check that QubitInformationObject title="'.$title2.'"');
$t->is($io2->title, $title2);
