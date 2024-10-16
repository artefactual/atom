<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$browser = new sfTestFunctional(new sfBrowser());

$informationObject = new QubitInformationObject();

$informationObject->title = 'testtitle';
$informationObject->save();

$browser
    ->get(';search?query=testtitle')
    ->with('response')->begin()
    ->checkElement('body', '/testtitle/')
    ->end();

$informationObject->title = 'TesTTItLe';
$informationObject->save();

$browser
    ->get(';search?query=TEsTtiTLE')
    ->with('response')->begin()
    ->checkElement('body', '/TesTTItLe/')
    ->end();

// Issue 849
$informationObject->title = 'testtitlé';
$informationObject->save();

$browser
    ->get(';search?query=testtitle')
    ->with('response')->begin()
    ->checkElement('body', '/testtitlé/')
    ->end();

// Issue 848
$informationObject->title = 'tEStTitLÉ';
$informationObject->save();

$browser
    ->get(';search?query=teSTtiTle')
    ->with('response')->begin()
    ->checkElement('body', '/tEStTitLÉ/')
    ->end();

$informationObject->delete();

$browser
    ->get(';search?query=testtitle')
    ->with('response')->begin()
    ->checkElement('body', '!/testtitle/')
    ->end();
