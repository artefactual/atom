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
require 'result/xfResultIterator.class.php';
require 'result/xfDocumentHit.class.php';
require 'result/xfResultException.class.php';
require 'mock/result/xfMockRetort.class.php';
require 'document/xfDocument.class.php';
require 'document/xfField.class.php';
require 'document/xfFieldValue.class.php';
require 'service/xfServiceRegistry.class.php';
require 'service/xfService.class.php';
require 'mock/service/xfMockIdentifier.class.php';

$service = new xfService(new xfMockIdentifier);
$retort = new xfMockRetort;
$service->addRetort($retort);

$registry = new xfServiceRegistry;
$registry->register($service);

$document = new xfDocument('guid');
$document->addField(new xfFieldValue(new xfField('_service', xfField::KEYWORD), 'foobar'));

$hit = new xfDocumentHit($document);
$array = array($hit, 'foo');

$iterator = new xfResultIterator(new ArrayIterator($array), $registry);

$t = new lime_test(9, new lime_output_color);

$t->diag('->current()');
$response = $iterator->current();
$t->isa_ok($response, 'xfDocumentHit', '->current() returns an xfDocumentHit');
$t->is($response->getDocument(), $document, '->current() returns an xfDocumentHit linked to the original document');
$iterator->next();
try {
  $msg = '->current() throws exception if internal iterator does not return an xfDocumentHit';
  $iterator->current();
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->diag('->key(), ->next(), ->rewind(), ->valid(), ->seek()');
class TestIterator implements SeekableIterator, Countable {
  public $t;
  public function __construct($t) { $this->t = $t; }
  public function current() { }
  public function key() { $this->pass('key'); }
  public function next() { $this->pass('next'); }
  public function rewind() { $this->pass('rewind'); }
  public function valid() { $this->pass('valid'); }
  public function count() { $this->pass('count'); }
  public function seek($to) { $this->pass('seek'); }
  public function pass($what) { $this->t->pass('->'.$what.'() wraps the iterator\'s ->'.$what.'()'); }
}
$iterator = new xfResultIterator(new TestIterator($t), new xfServiceRegistry);
$iterator->key();
$iterator->next();
$iterator->rewind();
$iterator->valid();
$iterator->seek(10);
$iterator->count();
