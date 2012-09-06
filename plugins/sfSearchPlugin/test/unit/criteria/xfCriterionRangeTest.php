<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'criteria/xfCriterion.interface.php';
require 'criteria/xfCriterionRange.class.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfCriterionTranslatorString.class.php';
require 'util/xfException.class.php';

$t = new lime_test(7, new lime_output_color);

$c = new xfCriterionRange(1, 42, true, true);
$t->is($c->toString(), 'RANGE {[1,42]}', '->toString() works on closed intervals');

$c = new xfCriterionRange(1, 42, false, false);
$t->is($c->toString(), 'RANGE {(1,42)}', '->toString() works on opened intervals');

$c = new xfCriterionRange(null, 42, true, false);
$t->is($c->toString(), 'RANGE {[inf,42)}', '->toString() works on infinitely bounded');

$c = new xfCriterionRange(1, null, true, false);
$t->is($c->toString(), 'RANGE {[1,inf)}', '->toString() works on infinitely bounded');

try {
  $msg = '->__construct() fails if both are to infinity';
  $c = new xfCriterionRange(null, null);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$c = new xfCriterionRange(1, 100, false, true);

$trans = new xfCriterionTranslatorString;
$c->translate($trans);

$t->is($trans->getString(), '(1 ... 100]', '->translate() translates the query');

$t->is($c->optimize(), $c, '->optimize() does nothing');
