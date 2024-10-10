<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$browser = new sfTestFunctional(new sfBrowser());

$informationObject = new QubitInformationObject();
$informationObject->save();

$browser
    ->post(';digitalobject/create', ['file' => sfConfig::get('sf_test_dir').'/fixtures/echo.jpg', 'informationObject' => $informationObject->id.';isad'])
    ->with('request')->begin()
    ->isParameter('module', 'digitalobject')
    ->isParameter('action', 'edit')
    ->end()
    ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('body', '/Untitled/')
    ->end();
