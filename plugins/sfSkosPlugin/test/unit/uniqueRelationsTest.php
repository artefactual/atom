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

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';

$t = new lime_test(6, new lime_output_color);

$t->diag('Initializing configuration.');
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);


$tests = array(

  array(
    'relations' => array(
      array(1, 2),
      array(1, 3),
      array(1, 4),
      array(2, 3),
      array(4, 1)
    ),
    'expected' => array(
      array(1, 2),
      array(1, 3),
      array(1, 4),
      array(2, 3)
    )
  ),

  array(
    'relations' => array(
      array(10, 20),
      array(3, 3),
      array(3, 3)
    ),
    'expected' => array(
      array(10, 20),
      array(3, 3)
    )
  )

);

foreach ($tests as $item)
{
  // Build
  $uniquer = new sfSkosUniqueRelations;
  foreach ($item['relations'] as $rel)
  {
    $uniquer->insert($rel[0], $rel[1]);
  }

  // Test that the duplicates are not included (is() uses the === operator, sort is also checked!)
  $result = $uniquer->getAll();
  $t->is($result, $item['expected']);

  // Test that the object is countable
  $t->is(count($uniquer), count($item['expected']));

  // Test that the object is iterable
  $index = 0;
  foreach ($uniquer as $unique)
  {
    $index += 1;
  }
  $t->is($index, count($item['expected']));

  unset($uniquer);
}



// Test if sfSkosUniqueRelations is working with the UNESCO Thesaurus
die(0);  // DISABLED! You can download the data from: http://vocabularies.unesco.org/exports/thesaurus/latest/unesco-thesaurus.rdf
$unescoThesaurus = realpath(dirname(__FILE__)).'/data/unesco-thesaurus.rdf';
$graph = new EasyRdf_Graph;
$graph->parseFile($unescoThesaurus);

// Populate relationships
$relations = new sfSkosUniqueRelations;
$prefix = 'http://vocabularies.unesco.org/thesaurus/concept';
$prefixLen = strlen($prefix);
foreach ($graph->allOfType('skos:Concept') as $x)
{
  foreach ($x->allResources('skos:related') as $y)
  {
    $idX = substr($x->getUri(), $prefixLen);
    $idY = substr($y->getUri(), $prefixLen);

    $relations->insert((int)$idX, (int)$idY);
  }
}

$results = $relations->getAll();

$tests = array(
  9345 => array(7050, 10321, 81, 8646, 9403, 13294, 9842, 83, 2073),
  2340 => array(2327, 2328),
  249  => array(1927, 219, 619, 8188, 11757, 12189),
  1518 => array(1480, 3602, 11264, 1516, 17061)
);

foreach ($tests as $s => $p)
{
  foreach ($p as $pX)
  {
    $rel = array($s, $pX);
    $matched = $relations->exists($rel[0], $rel[1]);
    $t->is($matched, true, sprintf('Relation %s => %s should be found', $rel[0], $rel[1]));
  }
}
