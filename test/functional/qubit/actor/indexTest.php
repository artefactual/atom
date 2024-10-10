<?php

$app = 'qubit';

include dirname(__FILE__).'/../../../bootstrap/functional.php';

$browser = new QubitTestFunctional(new sfBrowser());

$browser
    ->info('Actor without parent is 404')
    ->get(QubitActor::ROOT_ID.';isaar')
    ->with('request')->begin()
    ->isParameter('module', 'actor')
    ->isParameter('action', 'indexIsaar')
    ->end()
    ->with('response')->begin()
    ->isStatusCode(404)
    ->end();
