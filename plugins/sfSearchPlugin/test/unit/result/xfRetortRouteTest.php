<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'result/xfRetort.interface.php';
require 'result/xfRetortRoute.class.php';
require 'result/xfDocumentHit.class.php';
require 'document/xfDocument.class.php';
require 'document/xfField.class.php';
require 'document/xfFieldValue.class.php';

$doc = new xfDocument('guid');
$doc->addField(new xfFieldValue(new xfField('isbn', xfField::KEYWORD), '1234567890'));
$doc->addField(new xfFieldValue(new xfField('id', xfField::KEYWORD), '42'));
$hit = new xfDocumentHit($doc);

$t = new lime_test(5, new lime_output_color);

$retort = new xfRetortRoute('show/book?id=$id$');

$t->diag('->can()');
$t->ok(!$retort->can($hit, 'getFoo'), '->can() returns false if method does not match');
$t->ok($retort->can($hit, 'getRoute'), '->can() returns true if method does match');

$t->diag('->respond()');
$t->is($retort->respond($hit, 'getRoute'), 'show/book?id=42', '->respond() can do a single replacement');

$retort = new xfRetortRoute('show/book?id=$id$&isbn=$isbn$');
$t->is($retort->respond($hit, 'getRoute'), 'show/book?id=42&isbn=1234567890', '->respond() can do a double replacement');

$t->diag('->setMethod()');
$retort->setMethod('getIt');
$t->ok($retort->can($hit, 'getIt'), '->setMethod() changes the matching method');
