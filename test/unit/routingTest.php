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

$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

$t = new lime_test(8);

$routing = sfContext::getInstance()->getRouting();
$routingOptions = $routing->getOptions();
$routingOptions['context']['prefix'] = '';
$routing->initialize(sfContext::getInstance()->getEventDispatcher(), $routing->getCache(), $routingOptions);

$io = new QubitInformationObject();
$io->slug = 'foobar';
$io->save();

// Test generation of routes

$uri = $routing->generate('homepage');
$t->is($uri, '/');

$uri = $routing->generate(null, array('module' => 'search'));
$t->is($uri, '/search');

$uri = $routing->generate(null, array('module' => 'informationobject', 'action' => 'add'));
$t->is($uri, '/informationobject/add');

$uri = $routing->generate(null, array($io, 'module' => 'informationobject'));
$t->is($uri, '/foobar');

// Test route parsing

$routing->parse('/home');
$t->is($routing->getCurrentRouteName(), 'slug_index');
$t->is($routing->getCurrentInternalUri(), 'staticpage/index?sf_culture=en&slug=home');

$routing->parse('/foobar/edit');
$t->is($routing->getCurrentRouteName(), 'edit');
$t->is($routing->getCurrentInternalUri(), 'sfIsadPlugin/edit?sf_culture=en&slug=foobar');



