<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'index/xfIndex.interface.php';
require 'index/xfIndexCommon.class.php';
require 'index/xfIndexSingle.class.php';
require 'log/xfLogger.interface.php';
require 'log/xfLoggerBlackhole.class.php';
require 'service/xfService.class.php';
require 'service/xfServiceRegistry.class.php';
require 'mock/service/xfMockIdentifier.class.php';
require 'mock/engine/xfMockEngine.class.php';
require 'criteria/xfCriterion.interface.php';
require 'criteria/xfCriteria.class.php';
require 'document/xfDocument.class.php';
require 'document/xfField.class.php';
require 'document/xfFieldValue.class.php';
require 'result/xfResultIterator.class.php';
require 'result/xfDocumentHit.class.php';
require 'util/xfException.class.php';

class TestIndex extends xfIndexSingle
{
}

$t = new lime_test(20, new lime_output_color);
$index = new TestIndex;
$invalid = new TestIndex;

$t->diag('->get*(), ->set*()');
$t->is($index->getName(), 'TestIndex', '->getName() is initially the name of the class');
$index->setName('foobar');
$t->is($index->getName(), 'foobar', '->setName() changes the name');
$t->isa_ok($index->getServiceRegistry(), 'xfServiceRegistry', '->getServiceRegistry() returns a service registry');
$registry = new xfServiceRegistry;
$index->setServiceRegistry($registry);
$t->is($index->getServiceRegistry(), $registry, '->setServiceRegistry() changes the service registry');
$engine = new xfMockEngine;
$index->setEngine($engine);
$t->is($index->getEngine(), $engine, '->setEngine() changes the engine');
$t->ok(!$index->isSetup(), '->isSetup() is false initially');

$index = new TestIndex;
$registry = new xfServiceRegistry;
$registry->register(new xfService(new xfMockIdentifier));
$engine = new xfMockEngine;
$index->setServiceRegistry($registry);
$index->setEngine($engine);

$t->diag('->insert(), ->remove()');
$index->insert('foo');
$t->ok($index->isSetup(), '->insert() automatically runs setup');
$t->is(count($engine->getDocuments()), 1, '->insert() adds a document');
$index->remove('foo');
$t->is(count($engine->getDocuments()), 0, '->remove() deletes a document');
try {
  $msg = '->insert() fails if an engine does not exist';
  $invalid->insert('foo');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}
try {
  $msg = '->remove() fails if an engine does not exist';
  $invalid->remove('foo');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->diag('->optimize()');
$index->optimize();
$t->is($engine->optimized, 1, '->optimize() optimizes the engine');
try {
  $msg = '->optimize() fails if an engine does not exist';
  $invalid->optimize();
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->diag('->populate()');
$engine->add(new xfDocument('foo'));
$index->populate();
$t->is(count($engine->getDocuments()), 3, '->populate() erases the index and populates all documents');
try {
  $msg = '->populate() fails if an engine does not exist';
  $invalid->populate();
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->diag('->find()');
$results = $index->find(new xfCriteria);
$t->isa_ok($results, 'xfResultIterator', '->find() returns an xfResultIterator');
$t->is($results->count(), 3, '->find() returns results that match');
try {
  $msg = '->find() fails if an engine does not exist';
  $invalid->find(new xfCriteria);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->diag('->describe()');
$t->is($index->describe(), array('Engine' => 'Mock vINF'), '->describe() returns the engine\'s description');
try {
  $msg = '->describe() fails if an engine does not exist';
  $invalid->describe();
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}
