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

$t = new lime_test(5, new lime_output_color);

$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

// Default setting
sfConfig::set('app_escape_queries', '');
$t->is(arElasticSearchPluginUtil::escapeTerm('FO1/23-BAR\\456'), 'FO1/23-BAR\\456', 'Default setting, doesn\'t escape');

// Setting with value
sfConfig::set('app_escape_queries', '\\,/');
$t->is(arElasticSearchPluginUtil::escapeTerm('FO1/23-BAR\\456'), 'FO1\\/23-BAR\\\\456', 'Setting with value, escapes the term');

// Un-ordered setting
sfConfig::set('app_escape_queries', '/,\\');
$t->is(arElasticSearchPluginUtil::escapeTerm('FO1/23-BAR\\456'), 'FO1\\/23-BAR\\\\456', 'Un-ordered setting, escapes \\ first');

// Malformed setting
sfConfig::set('app_escape_queries', '/, ,[,]');
$t->is(arElasticSearchPluginUtil::escapeTerm('FO1/23-BAR[456]'), 'FO1\\/23-BAR\\[456\\]', 'Malformed setting, ignores empty chars');
sfConfig::set('app_escape_queries', ' / , [ , ] ');
$t->is(arElasticSearchPluginUtil::escapeTerm('FO1/23-BAR[456]'), 'FO1\\/23-BAR\\[456\\]', 'Malformed setting, ignores white spaces');
