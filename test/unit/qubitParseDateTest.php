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

$t = new lime_test(10, new lime_output_color());

$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

$testCases = [
    [
        'input' => '2018', 'output' => '2018',
        'description' => 'Only year date shouldn\'t be modified.',
    ],
    [
        'input' => '769', 'output' => '769',
        'description' => 'Only year date under 1000 shouldn\'t be modified.',
    ],
    [
        'input' => '2018-05', 'output' => '2018-05',
        'description' => 'Proper year and month date shouldn\'t be modified.',
    ],
    [
        'input' => '2018/05', 'output' => '2018-05',
        'description' => 'Proper year and month date with slash should work the same as with dash.',
    ],
    [
        'input' => '2018-05-21 12:05:45', 'output' => '2018-05-21',
        'description' => 'Time should be removed from the date.',
    ],
    [
        'input' => 'Not parseable string', 'output' => null,
        'description' => 'Non parseable strings should return \'null\'.',
    ],
    [
        'input' => '2018-13', 'output' => '2018-01-01',
        'description' => 'Bad year and month date should be processed by PHP\'s date_parse and formatted.',
    ],
    [
        'input' => '20181127', 'output' => '2018-11-27',
        'description' => 'Everything else should be processed by PHP\'s date_parse and formatted.',
    ],
    [
        'input' => '2018/12/31', 'output' => '2018-12-31',
        'description' => 'Everything else should be processed by PHP\'s date_parse and formatted.',
    ],
    [
        'input' => 'January 7, 1975', 'output' => '1975-01-07',
        'description' => 'Everything else should be processed by PHP\'s date_parse and formatted.',
    ],
];

foreach ($testCases as $testCase) {
    $t->is(Qubit::parseDate($testCase['input']), $testCase['output'], $testCase['description']);
}
