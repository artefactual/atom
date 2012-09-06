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
require 'criteria/xfCriterionField.class.php';
require 'criteria/xfCriterionTerm.class.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfCriterionTranslatorString.class.php';

$t = new lime_test(5, new lime_output_color);

$term = new xfCriterionTerm('foo');
$c = new xfCriterionField($term, 'bar');

$t->is($c->getField(), 'bar', '->getField() returns the fields');
$t->is($c->getCriterion(), $term, '->getCriterion() returns the criterion');
$t->is($c->toString(), 'FIELD {bar IS foo}', '->toString() works on single fields');

$trans = new xfCriterionTranslatorString;
$c->translate($trans);

$t->is($trans->getString(), 'bar:foo', '->translate() translates the query');

$t->is($c->optimize(), $c, '->optimize() does nothing');
