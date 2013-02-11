<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$browser = new sfTestFunctional(new sfBrowser);

// Based in the search scenario described by Glenn Dingwall
// See https://projects.artefactual.com/issues/4654

$rand = rand(1, 9999999);

// Search dates
$t1 = '1100-04-05';
$t2 = '1700-09-18';

// Information objects
// Properties: title, startDate, endDate, shoudlMatchSearch, filter
$cases = array(
  array('Foobar1-'.$rand,  '0500-02-03', '1100-04-04', false, array('t1', 't2')),  // Case 1
  array('Foobar2-'.$rand,  '1125-08-02', '1229-11-17', true,  array('t1', 't2')),  // Case 2
  array('Foobar3-'.$rand,  '1712-01-02', '1992-04-22', false, array('t1', 't2')),  // Case 3
  array('Foobar4-'.$rand,  '0500-02-03', '1125-08-02', true,  array('t1', 't2')),  // Case 4
  array('Foobar5-'.$rand,  '0500-02-03', '1992-04-22', true,  array('t1', 't2')),  // Case 5
  array('Foobar6-'.$rand,  '1229-11-17', '1992-04-22', true,  array('t1', 't2')),  // Case 6
  array('Foobar7-'.$rand,  '0500-02-03', '1100-04-04', false, array('t1')),        // Case 7
  array('Foobar8-'.$rand,  '0500-02-03', '1125-08-02', true,  array('t1')),        // Case 8
  array('Foobar9-'.$rand,  '1125-08-02', '1229-11-17', false, array('t1')),        // Case 9
  array('Foobar10-'.$rand, '1125-08-02', '1229-11-17', false, array('t2')),        // Case 10
  array('Foobar11-'.$rand, '1229-11-17', '1992-04-22', true,  array('t2')),        // Case 11
  array('Foobar12-'.$rand, '1712-01-02', '1992-04-22', false, array('t2')));       // Case 12

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
foreach ($cases as $key => $item)
{
  $tmp1 = (false !== array_search('t1', $item[4])) ? $t1 : null;
  $tmp2 = (false !== array_search('t2', $item[4])) ? $t2 : null;

  $browser
    ->get(getUrl($tmp1, $tmp2, $item[0]))
    ->with('response')->begin()
      ->checkElement('#search-stats', $item[3] ? 1 : 0)
    ->end();
}

function getUrl($startDate, $endDate, $title)
{
  return sprintf('%s?searchFields[0][query]=%s&searchFields[0][operator]=and&searchFields[0][match]=phrase&searchFields[0][field]=title%s%s',
    ';search/advanced',
    $title,
    null === $startDate ? '' : '&startDate='.preg_replace('/[\/-]/', '', $startDate),
    null === $endDate ? '' : '&endDate='.preg_replace('/[\/-]/', '', $endDate));
}
