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
require 'result/xfRetortField.class.php';
require 'result/xfDocumentHit.class.php';
require 'document/xfDocument.class.php';
require 'document/xfField.class.php';
require 'document/xfFieldValue.class.php';
require 'util/xfToolkit.class.php';

$doc = new xfDocument('guid');
$doc->addField(new xfFieldValue(new xfField('name', xfField::KEYWORD), 'carl'));
$doc->addField(new xfFieldValue(new xfField('cat_name', xfField::KEYWORD), 'earl'));
$hit = new xfDocumentHit($doc);

$t = new lime_test(5, new lime_output_color);

$retort = new xfRetortField;

$t->diag('->can()');
$t->ok($retort->can($hit, 'getName'), '->can() returns true if method matches a field in the document');
$t->ok(!$retort->can($hit, 'getFoo'), '->can() returns false if method does not match a field in the document');
$t->ok(!$retort->can($hit, 'fetchName'), '->can() returns false if method is invalid syntax');

$t->diag('->respond()');
$t->is($retort->respond($hit, 'getName'), 'carl', '->respond() returns the field response');
$t->is($retort->respond($hit, 'getCatName'), 'earl', '->respond() uses camel case');
