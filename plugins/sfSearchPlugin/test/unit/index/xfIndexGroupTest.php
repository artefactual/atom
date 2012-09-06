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
require 'index/xfIndexGroup.class.php';
require 'log/xfLogger.interface.php';
require 'log/xfLoggerBlackhole.class.php';
require 'log/xfLoggerEventDispatcher.class.php';
require 'service/xfService.class.php';
require 'service/xfServiceRegistry.class.php';
require 'mock/service/xfMockIdentifier.class.php';
require 'mock/engine/xfMockEngine.class.php';
require 'document/xfDocument.class.php';
require 'document/xfField.class.php';
require 'document/xfFieldValue.class.php';
require 'criteria/xfCriterion.interface.php';
require 'criteria/xfCriteria.class.php';
require 'util/xfException.class.php';
require 'event/sfEvent.class.php';
require 'event/sfEventDispatcher.class.php';

class TestChildOne extends xfIndexSingle
{
  protected function configure()
  {
    $this->setEngine(new xfMockEngine);
  }
}

class TestChildTwo extends xfIndexSingle
{
  protected function configure()
  {
    $this->setEngine(new xfMockEngine);

    $this->getServiceRegistry()->register(new xfService(new xfMockIdentifier));
  }
}

class TestGroup extends xfIndexGroup
{
  public $one, $two, $service;

  protected function configure()
  {
    $this->addIndex('child1', $this->one);
    $this->addIndex('child2', $this->two);

    $this->getServiceRegistry()->register($this->service);
  }
}

$one = new TestChildOne;
$two = new TestChildTwo;
$two->setServiceRegistry(new xfServiceRegistry);
$two->describe(); // forces ->setup()

$dispatcher = new sfEventDispatcher;
$logger = new xfLoggerEventDispatcher($dispatcher);
$service = new xfService(new xfMockIdentifier);
$group = new TestGroup;
$group->setLogger($logger);
$group->one = $one;
$group->two = $two;
$group->service = $service;

$t = new lime_test(11, new lime_output_color);

$t->ok($group->getIndex('child1') === $one, '->getIndex() returns the index');
try {
  $msg = '->getIndex() fails if index does not exist';
  $group->getIndex('foobar');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->ok($one->getLogger() === $logger, '->setup() configures the logger of the child');
$t->ok($one->getServiceRegistry() === $group->getServiceRegistry(), '->search() passes on the service registry');
$t->ok($two->getServiceRegistry() !== $group->getServiceRegistry(), '->setup() does not override a service registry');

$group->insert('foo');
$engine = $one->getEngine();
$t->is($engine->count(), 1, '->insert() adds to the child indices');

$group->remove('foo');
$t->is($engine->count(), 0, '->remove() removes from the child indices');

$group->populate();
$t->is($engine->count(), 3, '->populate() populates the child indices');

$group->optimize();
$t->is($engine->optimized, 1, '->optimize() optimizes the child indices');

$description = $group->describe();
$t->is(count($description), 2, '->describe() returns information about the child indices');

try {
  $msg = '->find() fails always';
  $group->find(new xfCriteria);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}
