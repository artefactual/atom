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

$t = new lime_test(20, new lime_output_color());

// QubitSlug::slugify
 $testsRestrictive = [
     // GIVEN - EXPECTED
     ['test slug', 'test-slug'],
     ['test-slug', 'test-slug'],
     ['test----slug', 'test-slug'],
     ['Test Slug', 'test-slug'],
     ['Test Slug 123', 'test-slug-123'],

     ['Test \'Slug\'', 'test-slug'],
     ['test ~\'`!@#$%^&*()_{}[]+=-;:"<>,.\\/? slug', 'test-slug'],
     ['Tést Slug', 'test-slug'],
     ['Tést SLÜG', 'test-slug'],
     ['TEST АБВ абв', 'test'],
 ];

$testsPermissive = [
    // GIVEN - EXPECTED
    ['test slug', 'test-slug'],
    ['test-slug', 'test-slug'],
    ['test----slug', 'test-slug'],
    ['Test Slug', 'Test-Slug'],
    ['Test Slug 123', 'Test-Slug-123'],

    ['Test \'Slug\'', 'Test-Slug'],
    ['test ~\'`|!@#$%^&*()_{}[]+=-;:"<>,.\\/? slug', 'test-~-@-*-_-=-;:-,-slug'],
    ['Tést Slug', 'Tést-Slug'],
    ['Tést SLÜG', 'Tést-SLÜG'],
    ['TEST АБВ абв', 'TEST-АБВ-абв'],
];

// Test Restrictive mode slug creation.
foreach ($testsRestrictive as $item) {
    list($given, $expected) = $item;

    $t->is(QubitSlug::slugify($given, QubitSlug::SLUG_RESTRICTIVE), $expected, "slugify(SLUG_RESTRICTIVE) creates slug from text: {$given} as {$expected}");
}

// Test Permissive mode slug creation.
foreach ($testsPermissive as $item) {
    list($given, $expected) = $item;

    $t->is(QubitSlug::slugify($given, QubitSlug::SLUG_PERMISSIVE), $expected, "slugify(SLUG_PERMISSIVE) creates slug from text: {$given} as {$expected}");
}
