<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'document/xfFieldValue.class.php';
require 'document/xfField.class.php';

$t = new lime_test(3, new lime_output_color);

$field = new xfField('name', xfField::KEYWORD);
$field->registerCallback('md5');

$value = new xfFieldValue($field, 'value', 'utf8');

$t->is($value->getField(), $field, '->getField() returns the field');
$t->is($value->getValue(), md5('value'), '->getValue() returns the transformed value');
$t->is($value->getEncoding(), 'utf8', '->getEncoding() returns the encoding');
