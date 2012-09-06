<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'util/xfException.class.php';
require 'service/xfService.class.php';
require 'service/xfServiceRegistry.class.php';
require 'service/xfServiceException.class.php';
require 'service/xfServiceNotFoundException.class.php';
require 'service/xfServiceIgnoredException.class.php';
require 'document/xfField.class.php';
require 'document/xfFieldValue.class.php';
require 'document/xfDocument.class.php';
require 'mock/service/xfMockIdentifier.class.php';

$t = new lime_test(8, new lime_output_color);

$registry = new xfServiceRegistry;
$t->is($registry->getServices(), array(), '->getServices() is empty initially');
try {
  $msg = '->getService() fails if service does not exist';
  $registry->getService('foo');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}
try {
  $msg = '->locate() fails if service does not exist';
  $registry->locate('foo');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$identifier = new xfMockIdentifier;
$identifier->match = xfIdentifier::MATCH_NO;
$service = new xfService($identifier);
$registry->register($service);
try {
  $msg = '->locate() fails if service does not match';
  $registry->locate('foo');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$identifier->match = xfIdentifier::MATCH_YES;
$t->is($registry->getServices(), array('foobar' => $service), '->getServices() returns an array of the services');
$t->is($registry->getService('foobar'), $service, '->getService() returns the requested service');
$t->is($registry->locate(42), $service, '->locate() returns the matching service');

$identifier->match = xfIdentifier::MATCH_IGNORED;
try {
  $msg = '->locate() throws exception if servic is ignored';
  $registry->locate(42);
  $t->fail($msg);
} catch (xfServiceIgnoredException $e) {
  $t->pass($msg);
} catch (Exception $e) {
  $t->fail($msg);
}
