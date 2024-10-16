<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$browser = new sfTestFunctional(new sfBrowser());

// Based in the search scenario described by Glenn Dingwall
// See https://projects.artefactual.com/issues/4654

$rand = rand(1, 9999999);

// Search dates
$t1 = '1100-04-05';
$t2 = '1700-09-18';
$t3 = '2015-02-27';
$t4 = '2019-11-08';

// Information objects
// Properties: title, startDate, endDate, shoudlMatchSearch, filter
$cases = [
    ['Foobar1-'.$rand, '0500-02-03', '1100-04-04', false, ['t1', 't2']],  // Case 1
    ['Foobar2-'.$rand, '1125-08-02', '1229-11-17', true, ['t1', 't2']],  // Case 2
    ['Foobar3-'.$rand, '1712-01-02', '1992-04-22', false, ['t1', 't2']],  // Case 3
    ['Foobar4-'.$rand, '0500-02-03', '1125-08-02', true, ['t1', 't2']],  // Case 4
    ['Foobar5-'.$rand, '0500-02-03', '1992-04-22', true, ['t1', 't2']],  // Case 5
    ['Foobar6-'.$rand, '1229-11-17', '1992-04-22', true, ['t1', 't2']],  // Case 6
    ['Foobar7-'.$rand, '0500-02-03', '1100-04-04', false, ['t1']],        // Case 7
    ['Foobar8-'.$rand, '0500-02-03', '1125-08-02', true, ['t1']],        // Case 8
    ['Foobar9-'.$rand, '1125-08-02', '1229-11-17', true, ['t1']],        // Case 9
    ['Foobar10-'.$rand, '1125-08-02', '1229-11-17', true, ['t2']],        // Case 10
    ['Foobar11-'.$rand, '1229-11-17', '1992-04-22', true, ['t2']],        // Case 11
    ['Foobar12-'.$rand, '1712-01-02', '1992-04-22', false, ['t2']],      // Case 12
    ['Foobar13-'.$rand, '2015-00-00', '2020-00-00', true, ['t3', 't4']],    // Case 13
    ['Foobar14-'.$rand, '2010-01-01', '2010-12-31', false, ['t3', 't4']],  // Case 14
    ['Foobar15-'.$rand, '2010-01-01', '2015-02-28', true, ['t3', 't4']],    // Case 15
    ['Foobar16-'.$rand, '2015-03-03', '2020-00-00', true, ['t3', 't4']],   // Case 16
    ['Foobar17-'.$rand, '2020-00-00', '2025-00-00', false, ['t3', 't4']],  // Case 17
    ['Foobar18-'.$rand, '2010-01-01', '2015-02-26', false, ['t3']],        // Case 18
    ['Foobar19-'.$rand, '2020-00-00', '2025-00-00', true, ['t3']],         // Case 19
    ['Foobar20-'.$rand, '2010-01-01', '2020-00-00', true, ['t3']],         // Case 20
    ['Foobar21-'.$rand, '2015-03-03', '2020-00-00', true, ['t4']],        // Case 21
    ['Foobar22-'.$rand, '2020-00-00', '2025-00-00', false, ['t4']],        // Case 22
    ['Foobar23-'.$rand, '2010-01-01', '2010-12-31', true, ['t4']],        // Case 23
];

// Create information objects
foreach ($cases as $item) {
    $title = $item[0];
    $informationObject = new QubitInformationObject();
    $informationObject->title = $title;
    $informationObject->parentId = QubitInformationObject::ROOT_ID;
    $informationObject->setPublicationStatus(QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID);
    $event = new QubitEvent();
    $event->typeId = QubitTerm::CREATION_ID;
    $event->startDate = $item[1];
    $event->endDate = $item[2];
    $informationObject->eventsRelatedByobjectId[] = $event;
    $informationObject->save();
}

// Search and test
foreach ($cases as $key => $item) {
    $tmp1 = (false !== array_search('t1', $item[4])) ? $t1 : null;
    $tmp2 = (false !== array_search('t2', $item[4])) ? $t2 : null;

    $tmp1 = (false !== array_search('t3', $item[4])) ? $t3 : $tmp1;
    $tmp2 = (false !== array_search('t4', $item[4])) ? $t4 : $tmp2;

    $browser
        ->get(getUrl($tmp1, $tmp2, $item[0]))
        ->with('response')->begin()
        ->checkElement('#search-stats', $item[3] ? 1 : 0)
        ->end();
}

function getUrl($startDate, $endDate, $title)
{
    return sprintf(
        '%s?searchFields[0][query]=%s&searchFields[0][operator]=and&searchFields[0][match]=phrase&searchFields[0][field]=title%s%s',
        ';search/advanced',
        $title,
        null === $startDate ? '' : '&startDate='.preg_replace('/[\/-]/', '', $startDate),
        null === $endDate ? '' : '&endDate='.preg_replace('/[\/-]/', '', $endDate)
    );
}
