<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfCriterionTranslatorString.class.php';

$t = new lime_test(1, new lime_output_color);

$trans = new xfCriterionTranslatorString;
$trans->openBoolean();
$trans->openBoolean();
$trans->setNextRequired();
$trans->setNextField('foo');
$trans->createPhrase('bar baz', 5);
$trans->closeBoolean();
$trans->setNextProhibited();
$trans->createRange(1, 2, true, true);
$trans->closeBoolean();
$trans->openBoolean();
$trans->createRange(2, 3, false, false);
$trans->setNextBoost(42);
$trans->createTerm('sf');
$trans->createWildcard('m?n');
$trans->closeBoolean();

$t->is($trans->getString(), '{{ {{ foo:+"bar baz"~5 }} -[1 ... 2] }} {{ (2 ... 3) 42^sf /m?n/ }}', 'xfCriterionTranslator works');
