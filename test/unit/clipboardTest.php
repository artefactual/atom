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

$t = new lime_test(10, new lime_output_color);

$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

$storage = new sfSessionStorage(array('auto_start' => false));
$clipboard = new QubitClipboard($storage);

$t->is($clipboard->count(), 0, '->count() returns zero items in a new clipboard');

$t->is($clipboard->toggle('slug'), true, '->toggle() a non added slug returns true');
$t->is($clipboard->count(), 1, '->count() returns one item');

$t->is($clipboard->toggle('slug'), false, '->toggle() an added slug returns false');
$t->is($clipboard->count(), 0, '->count() returns zero items');

$t->is($clipboard->has('slug'), false, '->has() returns false when the slug can\'t be found');

$clipboard->toggle('slug');
$t->is($clipboard->has('slug'), true, '->has() returns true when the slug can be found');

$clipboard->toggle('slug-1');
$clipboard->toggle('slug-2');
$clipboard->toggle('slug-3');
$t->is($clipboard->getAll(), array('slug', 'slug-1', 'slug-2', 'slug-3'), '->getAll() returns an array with all the items');

$clipboard->clear();
$t->is($clipboard->getAll(), array(), '->getAll() returns an empty array after ->clear()');
$t->is($clipboard->count(), 0, '->count() returns zero after ->clear()');
