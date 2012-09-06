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
require 'criteria/xfCriterionPhrase.class.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfCriterionTranslatorString.class.php';

$t = new lime_test(6, new lime_output_color);

$c = new xfCriterionPhrase('foo bar baz', 1);

$t->is($c->getPhrase(), 'foo bar baz', '->getPhrase() returns the phrase');
$t->is($c->getSlop(), 1, '->getSlop() returns the slop');
$c->setSlop(2);
$t->is($c->getSlop(), 2, '->setSlop() changes the slop');
$t->is($c->toString(), 'PHRASE {"foo bar baz" SLOP 2}', '->toString() produces the string representation');


$trans = new xfCriterionTranslatorString;
$c->translate($trans);

$t->is($trans->getString(), '"foo bar baz"~2', '->translate() translates the query');

$t->is($c->optimize(), $c, '->optimize() does nothing');
