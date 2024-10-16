<?php

$app = 'qubit';

include dirname(__FILE__).'/../../../bootstrap/functional.php';

$browser = new QubitTestFunctional(new sfBrowser());
$browser->disableSecurity();

$browser
    ->post(';create/isad', ['title' => 'Example fonds'])
    ->with('request')->begin()
    ->isParameter('module', 'sfIsadPlugin')
    ->isParameter('action', 'edit')
    ->end()
    ->isRedirected()
    ->followRedirect()
    ->with('request')->begin()
    ->isParameter('module', 'sfIsadPlugin')
    ->isParameter('action', 'index')
    ->end()
    ->with('response')->begin()
    ->checkElement('body', '/Example fonds/')
    ->end();

$object = QubitObject::getById($browser->getRequest()->id);

$browser
    ->test()->ok(
        isset($object->parent),
        'Never create an information object without a parent'
    );

$object->delete();
