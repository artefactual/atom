<?php

$app = 'qubit';

require_once dirname(__FILE__).'/../../../bootstrap/functional.php';

$browser = new QubitTestFunctional(new sfBrowser());
$browser->disableSecurity();

$browser
    ->get(';create/isaar')
    ->with('request')->begin()
    ->isParameter('module', 'actor')
    ->isParameter('action', 'editIsaar')
    ->end()
    ->click('Create')
    ->with('request')->begin()
    ->isParameter('module', 'actor')
    ->isParameter('action', 'editIsaar')
    ->end()
    ->isRedirected()
    ->followRedirect()
    ->with('request')->begin()
    ->isParameter('module', 'actor')
    ->isParameter('action', 'indexIsaar')
    ->end();

$object = QubitObject::getById($browser->getRequest()->id);

$browser->test()->ok(
    isset($object->parent),
    'Never create an actor without a parent'
);

$object->delete();
