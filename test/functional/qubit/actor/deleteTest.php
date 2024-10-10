<?php

$app = 'qubit';

include dirname(__FILE__).'/../../../bootstrap/functional.php';

$browser = new QubitTestFunctional(new sfBrowser());
$browser->disableSecurity();

$browser
    ->info('Actor without parent is 404')
    ->get(QubitActor::ROOT_ID.';actor/delete')
    ->with('request')->begin()
    ->isParameter('module', 'actor')
    ->isParameter('action', 'delete')
    ->end()
    ->with('response')->begin()
    ->isStatusCode(404)
    ->end();
