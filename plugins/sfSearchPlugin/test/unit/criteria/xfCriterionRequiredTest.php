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
require 'criteria/xfCriterionTerm.class.php';
require 'criteria/xfCriterionDecorator.class.php';
require 'criteria/xfCriterionRequired.class.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfCriterionTranslatorString.class.php';

$t = new lime_test(3, new lime_output_color);
$c = new xfCriterionRequired(new xfCriterionTerm('foo'));

$t->is($c->toString(), 'REQUIRED {foo}', '->toString() works');

$trans = new xfCriterionTranslatorString;
$c->translate($trans);

$t->is($trans->getString(), '+foo', '->translate() translates the query');

$t->is($c->optimize(), $c, '->optimize() does nothing');
