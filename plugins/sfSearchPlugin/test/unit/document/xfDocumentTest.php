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
require 'document/xfDocument.class.php';
require 'document/xfFieldValue.class.php';
require 'document/xfField.class.php';
require 'document/xfDocumentException.class.php';

$t = new lime_test(15, new lime_output_color);

$t->diag('->__construct()');
$doc = new xfDocument('guid');
$t->is($doc->getGuid(), 'guid', '->getGuid() returns the document GUID');
$t->is($doc->getFields(), array(), '->getFields() returns an empty array initially');

$t->diag('->addField()');
$value = new xfFieldValue(new xfField('field1', xfField::KEYWORD), 'value');
$doc->addField($value);
$t->is($doc->getField('field1'), $value, '->getField() returns the registered field');
$t->is($doc->getFields(), array('field1' => $value), '->getFields() returns all the fields');
try {
  $msg = '->getField() fails if field name does not exist';
  $doc->getField('foobar');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->diag('->hasField()');
$t->ok($doc->hasField('field1'), '->hasField() returns true if the field exists');
$t->ok(!$doc->hasField('field99'), '->hasField() returns false if the field does not exist');

$t->diag('->setBoost(), ->getBoost()');
$doc = new xfDocument('guid');
$t->is($doc->getBoost(), 1.0, '->getBoost() is 1.0 initially');
$doc->setBoost(M_PI);
$t->is($doc->getBoost(), M_PI, '->setBoost() changes the boost');
$doc->setBoost('42foobar');
$t->is($doc->getBoost(), 42, '->setBoost() casts the input to a float');

$t->diag('->addChild(), ->getChildren()');
$parent = new xfDocument('parent');
$child = new xfDocument('child');
$parent->addChild($child);
$t->is($parent->getChildren(), array('child' => $child), '->addChild() adds a child');
$parent->addChild($child);
$t->is($parent->getChildren(), array('child' => $child), '->addChild() does not add a child twice');
try {
  $msg = '->addChild() rejects circular children';
  $child->addChild($parent);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}
$grandparent = new xfDocument('grandparent');
$parent = new xfDocument('parent');
$child = new xfDocument('child');
$grandchild = new xfDocument('grandchild');
$grandparent->addChild($parent);
$parent->addChild($child);
try {
  $msg = '->addChild() accepts long linear children';
  $child->addChild($grandchild);
  $t->pass($msg);
} catch (Exception $e) {
  $t->fail($msg);
}
try {
  $msg = '->addChild() rejects long circular children';
  $grandchild->addChild($grandparent);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}
