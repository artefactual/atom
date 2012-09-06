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
require 'criteria/xfCriteria.class.php';
require 'criteria/xfCriterionTerm.class.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfCriterionTranslatorString.class.php';
require 'criteria/xfCriterionEmpty.class.php';
require 'util/xfException.class.php';

$t = new lime_test(11, new lime_output_color);

$c = new xfCriteria;
$c->add(new xfCriterionTerm('foobar'));
$c->add(new xfCriterionTerm('baz'));

$t->is(count($c->getCriterions()), 2, '->add() adds criterions to the boolean query');
$t->is($c->toString(), 'BOOLEAN {[foobar] AND [baz]}', '->toString() returns a string representation');

$t->is($c->getLast()->getTerm(), 'baz', '->getLast() returns the last criterion');
$c->replaceLast(new xfCriterionTerm('foo'));
$t->is($c->getLast()->getTerm(), 'foo', '->replaceLast() changes the last criterion');

$trans = new xfCriterionTranslatorString;
$c->translate($trans);

$t->is($trans->getString(), '{{ foobar foo }}', '->translate() translates the query');

$c = new xfCriteria;
try {
  $msg = '->getLast() fails if there is no last criterion.';
  $c->getLast();
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

try {
  $msg = '->replaceLast() fails if there is no last criterion';
  $c->replaceLast(new xfCriteria);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$c = new xfCriteria;
$t->is($c->optimize()->toString(), 'EMPTY', '->optimize() eliminates empty subqueries');
$c->add(new xfCriterionTerm('foo'));
$t->is($c->optimize()->toString(), 'foo', '->optimize() reduces itself to just the subquery if only one subquery');
$c->add(new xfCriterionTerm('bar'));
$t->is($c->optimize()->toString(), 'BOOLEAN {[foo] AND [bar]}', '->optimize() does nothing if it cannot be optimized');

$c1 = new xfCriteria;
$c1->add(new xfCriterionEmpty);
$c1->add(new xfCriterionTerm('foo'));
$c2 = new xfCriteria;
$c2->add(new xfCriterionTerm('bar'));
$c1->add($c2);
$t->is($c1->optimize()->toString(), 'BOOLEAN {[foo] AND [bar]}', '->optimize() recursively optimizes');
