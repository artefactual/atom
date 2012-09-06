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
require 'parser/xfCriterionBuilder.class.php';
require 'parser/xfParserException.class.php';
require 'lexer/xfLexeme.class.php';
require 'criteria/xfCriterion.interface.php';
require 'criteria/xfCriteria.class.php';
require 'criteria/xfCriterionTerm.class.php';
require 'criteria/xfCriterionDecorator.class.php';
require 'criteria/xfCriterionRequired.class.php';
require 'criteria/xfCriterionBoost.class.php';
require 'criteria/xfCriterionEmpty.class.php';

$t = new lime_test(17, new lime_output_color);

$b = new xfCriterionBuilder(array(new xfLexeme('foo', 1, 10), new xfLexeme('bar', 2, 50)));
$t->isa_ok($b->next(), 'xfLexeme', '->next() returns a lexeme');
$t->is($b->next()->getLexeme(), 'bar', '->next() advances the pointer');
$t->is($b->next(), null, '->next() returns null when the pointer is out of bounds');

$b = new xfCriterionBuilder(array(new xfLexeme('foo', 1, 10), new xfLexeme('bar', 2, 50)));
$b->next();
$t->is($b->getLexeme()->getLexeme(), 'foo', '->getLexeme() returns the current lexeme');
$t->is($b->getLexeme(1)->getLexeme(), 'bar', '->getLexeme() returns the next lexeme');
$b->next();
$t->is($b->getLexeme(-1)->getLexeme(), 'foo', '->getLexeme() returns the previous lexeme');
try {
  $msg = '->getLexeme() fails if it is out of bounds.';
  $b->getLexeme(-10);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->is($b->getMaster()->toString(), 'EMPTY', '->getMaster() returns the master query');
$b->openBoolean();
try {
  $msg = '->getMaster() fails with an open boolean query.';
  $b->getMaster();
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$b->add(new xfCriterionTerm('foo'));
$b->add(new xfCriterionTerm('baz'));
$b->closeBoolean();
$b->add(new xfCriterionTerm('bar'));

$t->is($b->getMaster()->toString(), 'BOOLEAN {[BOOLEAN {[foo] AND [baz]}] AND [bar]}', '->add(), ->openBoolean(), ->closeBoolean() adds queries');

try {
  $msg = '->closeBoolean() fails if no boolean query is open';
  $b->closeBoolean();
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->is($b->getLastBoolean(), $b->getMaster(), '->getLastBoolean() gets the last query in the stack');
$b->openBoolean();
$b->add(new xfCriterionTerm('foo2'));
$b->add(new xfCriterionTerm('bar2'));
$t->is($b->getLastBoolean()->toString(), 'BOOLEAN {[foo2] AND [bar2]}', '->getLastBoolean() gets the last query in the stack');

$b->addDecorator('xfCriterionBoost', array(2));
$b->add(new xfCriterionTerm('gar'));
$t->is($b->getLastBoolean()->getLast()->toString(), 'BOOST {2 ON gar}', '->addDecorator() adds a future decorator');
$b->addRetroDecorator('xfCriterionRequired');
$t->is($b->getLastBoolean()->getLast()->toString(), 'REQUIRED {BOOST {2 ON gar}}', '->addRetroDecorator() adds a decorator to the previous query');

$b->addRetroDecorator('xfCriterionBoost', array(3), true);
$b->closeBoolean();
$t->is($b->getMaster()->getLast()->toString(), 'BOOST {3 ON BOOLEAN {[foo2] AND [bar2] AND [REQUIRED {BOOST {2 ON gar}}]}}', '->addRetroDecorator() can add a decorator to the last boolean');

try {
  $msg = '->addRetroDecorator() fails if the decorator does not extend xfCriterionDecorator';
  $b->addRetroDecorator('xfCriteria');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}
