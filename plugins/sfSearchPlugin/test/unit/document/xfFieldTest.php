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
require 'document/xfField.class.php';
require 'document/xfDocumentException.class.php';

$t = new lime_test(17, new lime_output_color);

$t->diag('->__construct()');
$validTypes = array(xfField::STORED, xfField::INDEXED, xfField::TOKENIZED, xfField::KEYWORD, xfField::TEXT, xfField::UNSTORED, xfField::UNINDEXED, xfField::BINARY);
$invalidTypes = array(0, '42', 'foobar');
foreach ($validTypes as $type) {
  $msg = '->__construct() accepts the valid type ' . $type;
  try {
    $field = new xfField('foobar', $type);
    $t->pass($msg);
  } catch (Exception $e) {
    $t->fail($constructorMsg);
    $t->skip($msg);
  }
}
foreach ($invalidTypes as $type) {
  $msg = '->__construct() rejects the invalid type ' . $type;
  try {
    $field = new xfField('foobar', $type);
    $t->fail($msg);
  } catch (Exception $e) {
    $t->pass($msg);
  }
}

$t->diag('->getName(), ->getType()');
$field = new xfField('foobar', xfField::KEYWORD);
$t->is($field->getName(), 'foobar', '->getName() returns the name');
$t->is($field->getType(), xfField::KEYWORD, '->getType() returns the type');

$t->diag('->registerCallback(), ->getCallbacks(), ->transformValue()');
$field = new xfField('foobar', xfField::KEYWORD);
$field->registerCallback('strtoupper');
$field->registerCallback('md5');
$t->is($field->transformValue('foobar'), md5(strtoupper('foobar')), '->transformValue() calls callbacks in registered order');

$t->diag('->setBoost(), ->getBoost()');
$field = new xfField('foobar', xfField::KEYWORD);
$t->is($field->getBoost(), 1.0, '->getBoost() is 1.0 initially');
$field->setBoost(M_PI);
$t->is($field->getBoost(), M_PI, '->setBoost() changes the boost');
$field->setBoost('42foobar');
$t->is($field->getBoost(), 42, '->setBoost() casts the input to a float');
