<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'service/xfService.class.php';
require 'mock/service/xfMockIdentifier.class.php';
require 'mock/builder/xfMockBuilder.class.php';
require 'mock/result/xfMockRetort.class.php';

$t = new lime_test(12, new lime_output_color);

$identifier = new xfMockIdentifier;
$service = new xfService($identifier);

$t->diag('->getIdentifier()');
$t->is($service->getIdentifier(), $identifier, '->getIdentifier() returns the identifier');

$t->diag('->addBuilder(), ->buildDocument()');
$doc = $service->buildDocument(42);
$t->is($doc->getGuid(), $identifier->getGuid(42), '->buildDocument() builds a document with the correct GUID');
$t->is(count($doc->getFields()), 1, '->buildDocument() builds a document with one field only with no builders');
$t->is($doc->getField('_service')->getValue(), 'foobar', '->buildDocument() builds a document with a field "_service" as the name of the service');
$service->addBuilder(new xfMockBuilder);
$doc = $service->buildDocument(42);
$t->is($doc->getField('foobar')->getValue(), 'bar', '->buildDocument() acknowledges registered builders');

$t->diag('->addRetort(), ->getRetorts()');
$retort = new xfMockRetort;
$service->addRetort($retort);
$t->is($service->getRetorts(), array($retort), '->getRetorts() returns the retorts registered');

$t->diag('->setOption(), ->getOption(), ->hasOption()');
$t->ok(!$service->hasOption('foobar'), '->hasOption() returns false for unset options');
$t->is($service->getOption('foobar'), null, '->getOption() returns null for unset options');
$t->is($service->getOption('foobar', 42), 42, '->getOption() returns the default response for unset options');
$service->setOption('foobar', 82);
$t->ok($service->hasOption('foobar'), '->hasOption() returns true for set options');
$t->is($service->getOption('foobar', 42), 82, '->getOption() returns the option value');

$t->diag('->configure()');
class FooService extends xfService
{
  public $configured = false;

  public function configure()
  {
    $this->configured = true;
  }
}

$service = new FooService($identifier);
$t->ok($service->configured, '->configure() is called during object construction');
