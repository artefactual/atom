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
require 'criteria/xfCriterionWildcard.class.php';
require 'criteria/xfCriterionTerm.class.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfCriterionTranslatorString.class.php';

$t = new lime_test(4, new lime_output_color);
$c = new xfCriterionWildcard('f?o b*r');

$t->is($c->toString(), 'WILDCARD {f?o b*r}', '->toString() works');

$trans = new xfCriterionTranslatorString;
$c->translate($trans);

$t->is($trans->getString(), '/f?o b*r/', '->translate() translates the query');

$t->is($c->optimize(), $c, '->optimize() does nothing if there is a wildcard');

$c = new xfCriterionWildCard('foo');
$t->is($c->optimize()->toString(), 'foo', '->optimize() removes the wildcard if there are no tokens');
