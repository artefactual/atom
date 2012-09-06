<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'util/xfException.class.php';
require 'util/xfLuceneZendManager.class.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfLuceneCriterionTranslator.class.php';
require 'util/xfLuceneException.class.php';

xfLuceneZendManager::load();

$t = new lime_test(7, new lime_output_color);

$trans = new xfLuceneCriterionTranslator;
$trans->setNextBoost(2);
$trans->openBoolean();
$trans->setNextField('baz');
$trans->createPhrase('foo bar', 1);
$trans->createRange(1, 10, true, true);
$trans->closeBoolean();
$t->is($trans->toString(), '(baz:"foo bar"~1) ([1 TO 10])', 'simple boolean query');

$trans = new xfLuceneCriterionTranslator;
$trans->openBoolean();
$trans->createPhrase('baz', 2);
$trans->createPhrase('bazz zzz', 0);
$trans->openBoolean();
$trans->setNextField('a');
$trans->createRange(1, 10, false, false);
$trans->openBoolean();
$trans->createWildcard('a*');
$trans->closeBoolean();
$trans->closeBoolean();
$trans->closeBoolean();
$t->is($trans->toString(), '("baz"~2) ("bazz zzz") ((a:{1 TO 10}) ((a*)))', 'nested boolean query');

$trans = new xfLuceneCriterionTranslator;
$trans->setNextField('foo');
$trans->createTerm('baz');
$t->is($trans->toString(), 'foo:baz', 'non boolean master');

$trans = new xfLuceneCriterionTranslator;
$trans->setNextRequired();
$trans->createTerm('foo');
$t->is($trans->toString(), '+(foo)', 'required query');

$trans = new xfLuceneCriterionTranslator;
$trans->setNextProhibited();
$trans->createTerm('foo');
$t->is($trans->toString(), '-(foo)', 'prohibited query');

$trans = new xfLuceneCriterionTranslator;
$trans->createRange(1, 10, false, false);
$t->is($trans->toString(), '{1 TO 10}', 'exclusive range');

$trans = new xfLuceneCriterionTranslator;
$trans->createTerm('foo');

try {
  $msg = 'fails if multiple not in boolean query';
  $trans->createTerm('bar');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

