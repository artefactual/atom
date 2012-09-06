<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$browser = new sfTestFunctional(new sfBrowser);

$email = rand().'@example.com';
$password = rand();

$user = new QubitUser;
$user->email = $email;
$user->setPassword($password);
$user->save();

$relation = new QubitUserRoleRelation;
$relation->userId = $user->id;
$relation->roleId = QubitRole::ADMINISTRATOR_ID;
$relation->save();

$browser->post(';user/login', array('login' => array('email' => $email, 'password' => $password)));

$scopeAndContent = rand();
$identifier = rand();
$title = rand();

$informationObject = new QubitInformationObject;
$informationObject->parentId = QubitInformationObject::ROOT_ID;
$informationObject->scopeAndContent = $scopeAndContent;
$informationObject->identifier = $identifier;
$informationObject->title = $title;
$informationObject->save();

$browser->get('/'.$informationObject->id.';dc?sf_format=xml');

$user->delete();
$informationObject->delete();

$doc = new DOMDocument;
$doc->loadXML($browser->getResponse()->getContent());

$xpath = new DOMXPath($doc);
$xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');

$browser->test()->is($xpath->evaluate('string(/*/dc:description)', $doc->documentElement), $scopeAndContent, 'description');
$browser->test()->is($xpath->evaluate('string(/*/dc:identifier)', $doc->documentElement), $identifier, 'identifier');
$browser->test()->is($xpath->evaluate('string(/*/dc:title)', $doc->documentElement), $title, 'title');
