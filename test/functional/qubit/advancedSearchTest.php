<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$browser = new sfTestFunctional(new sfBrowser);

// Based in the search scenario described by Glenn Dingwall
// See https://projects.artefactual.com/issues/4654

$rand = rand(1, 9999999);

// Search
$t1 = '1100-04-05';
$t2 = '1700-09-18';

// Information objects
// Properties: title, startDate, endDate, shoudlMatchSearch
$cases = array(
  array('Foobar1-'.$rand, '0500-02-03', '1100-04-04', false),  // Case 1
  array('Foobar2-'.$rand, '1125-08-02', '1229-11-17', true),   // Case 2
  array('Foobar3-'.$rand, '1712-01-02', '1992-04-22', false),  // Case 3
  array('Foobar4-'.$rand, '0500-02-03', '1125-08-02', true),   // Case 4
  array('Foobar5-'.$rand, '0500-02-03', '1992-04-22', true),   // Case 5
  array('Foobar6-'.$rand, '1229-11-17', '1992-04-22', true));  // Case 6

// Create information objects
foreach ($cases as $item)
{
  $title = $item[0];
  $informationObject = new QubitInformationObject;
  $informationObject->title = $title;
  $informationObject->parentId = QubitInformationObject::ROOT_ID;
  $informationObject->setPublicationStatus(QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID);
  $event = new QubitEvent;
  $event->typeId = QubitTerm::CREATION_ID;
  $event->startDate = $item[1];
  $event->endDate = $item[2];
  $informationObject->events[] = $event;
  $informationObject->save();
}

// Search and test
foreach ($cases as $item)
{
  $browser
    ->get(getUrl($t1, $t2, $item[0]))
    ->with('response')->begin()
      ->checkElement('#search-stats', $item[3] ? 1 : 0)
    ->end();
}

function getUrl($startDate, $endDate, $title)
{
  return sprintf(';search/advanced?startDate=%s&endDate=%s&searchFields[0][query]=%s&searchFields[0][operator]=and&searchFields[0][match]=phrase&searchFields[0][field]=title',
    preg_replace('/[\/-]/', '', $startDate),
    preg_replace('/[\/-]/', '', $endDate),
    $title);
}
