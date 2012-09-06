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
require 'criteria/xfCriterionDecorator.class.php';
require 'criteria/xfCriterionBoost.class.php';
require 'criteria/xfCriterionTerm.class.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfCriterionTranslatorString.class.php';

$t = new lime_test(7, new lime_output_color);

$term = new xfCriterionTerm('foo');
$c = new xfCriterionBoost($term, 2);

$t->is($c->getCriterion(), $term, '->getCriterion() returns the wrapped criterion');
$t->is($c->getBoost(), 2, '->getBoost() returns the boost');
$c->setBoost(3);
$t->is($c->getBoost(), 3, '->setBoost() changes the boost');
$t->is($c->toString(), 'BOOST {3 ON foo}', '->toString() produces the string representation');

$trans = new xfCriterionTranslatorString;
$c->translate($trans);

$t->is($trans->getString(), '3^foo', '->translate() translates the query');

$t->is($c->optimize()->toString(), 'BOOST {3 ON foo}', '->optimize() does nothing if nothing can be optimized');

$c = new xfCriterionBoost($term, 1);
$t->is($c->optimize()->toString(), 'foo', '->optimize() reduces the query if the boost is 1');
