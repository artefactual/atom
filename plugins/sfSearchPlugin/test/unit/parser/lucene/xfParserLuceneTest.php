<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../../bootstrap/unit.php';
require 'util/xfException.class.php';
require 'fsm/xfFiniteStateMachine.class.php';
require 'fsm/xfFiniteStateMachineAction.interface.php';
require 'parser/xfParserTester.class.php';
require 'parser/xfParser.interface.php';
require 'parser/lucene/xfParserLucene.class.php';
require 'parser/xfParserFSMError.class.php';
require 'parser/xfParserException.class.php';
require 'parser/xfCriterionBuilder.class.php';
require 'parser/xfCriterionBuilderActionCommon.class.php';
require 'parser/xfCriterionBuilderAddTerm.class.php';
require 'parser/xfCriterionBuilderAddPhrase.class.php';
require 'parser/xfCriterionBuilderAddWildcard.class.php';
require 'parser/xfCriterionBuilderSetField.class.php';
require 'parser/lucene/xfCriterionBuilderLuceneHandleRange.class.php';
require 'parser/lucene/xfCriterionBuilderLuceneHandleSyntax.class.php';
require 'lexer/xfLexer.interface.php';
require 'lexer/xfLexeme.class.php';
require 'lexer/xfLexemeBuilder.class.php';
require 'lexer/xfLexemeBuilderActionCommon.class.php';
require 'lexer/xfLexemeBuilderAddChar.class.php';
require 'lexer/xfLexemeBuilderAddLexeme.class.php';
require 'lexer/lucene/xfLexerLucene.class.php';
require 'lexer/lucene/xfLexemeLucene.class.php';
require 'lexer/lucene/xfLexemeBuilderLuceneAddSyntax.class.php';
require 'criteria/xfCriterion.interface.php';
require 'criteria/xfCriterionEmpty.class.php';
require 'criteria/xfCriteria.class.php';
require 'criteria/xfCriterionTerm.class.php';
require 'criteria/xfCriterionPhrase.class.php';
require 'criteria/xfCriterionRange.class.php';
require 'criteria/xfCriterionWildcard.class.php';
require 'criteria/xfCriterionDecorator.class.php';
require 'criteria/xfCriterionField.class.php';
require 'criteria/xfCriterionProhibited.class.php';
require 'criteria/xfCriterionRequired.class.php';
require 'criteria/xfCriterionBoost.class.php';

$t = new lime_test(50, new lime_output_color);
$parser = new xfParserLucene;
$p = new xfParserTester($t, $parser);

$t->diag('Empty');
$p->pass(''                      , 'EMPTY');
$p->pass('            '          , 'EMPTY');

$t->diag('Term');
$p->pass('foo'                   , 'foo');

$t->diag('Boolean');
$p->pass('foo bar baz'           , 'BOOLEAN {[foo] AND [bar] AND [baz]}');
$p->pass('foo and bar'           , 'BOOLEAN {[REQUIRED {foo}] AND [REQUIRED {bar}]}');
$p->pass('foo or bar'            , 'BOOLEAN {[foo] AND [bar]}');

$t->diag('Phrase');
$p->pass('"foo bar baz"'         , 'PHRASE {"foo bar baz"}');
$p->pass('"foo bar" "baz gab"'   , 'BOOLEAN {[PHRASE {"foo bar"}] AND [PHRASE {"baz gab"}]}');
$p->pass('"foo bar"~10'          , 'PHRASE {"foo bar" SLOP 10}');
$p->pass('+"foo bar"~2'          , 'REQUIRED {PHRASE {"foo bar" SLOP 2}}');
$p->fail('foo~2');

$t->diag('Required');
$p->pass('+foo'                  , 'REQUIRED {foo}');
$p->pass('+"foo bar"'            , 'REQUIRED {PHRASE {"foo bar"}}');

$t->diag('Not');
$p->pass('NOT foo'               , 'PROHIBITED {foo}');
$p->pass('NOT "foo bar"'         , 'PROHIBITED {PHRASE {"foo bar"}}');
$p->pass('NOT foobar foo'        , 'BOOLEAN {[PROHIBITED {foobar}] AND [foo]}');
$p->pass('foo NOT foobar'        , 'BOOLEAN {[foo] AND [PROHIBITED {foobar}]}');
$p->pass('-foo'                  , 'PROHIBITED {foo}');
$p->pass('-"foo bar" +baz'       , 'BOOLEAN {[PROHIBITED {PHRASE {"foo bar"}}] AND [REQUIRED {baz}]}');

$t->diag('Boost');
$p->pass('foo^5'                 , 'BOOST {5 ON foo}');
$p->pass('"foo bar"^3'           , 'BOOST {3 ON PHRASE {"foo bar"}}');
$p->pass('foo^0.5 +baz'          , 'BOOLEAN {[BOOST {0.5 ON foo}] AND [REQUIRED {baz}]}');

$t->diag('Field');
$p->pass('title: baz'            , 'FIELD {title IS baz}');
$p->pass('title: "foo bar"'      , 'FIELD {title IS PHRASE {"foo bar"}}');
$p->pass('a: foo b'              , 'BOOLEAN {[FIELD {a IS foo}] AND [b]}');

$t->diag('Grouping');
$p->pass('(a b) c'               , 'BOOLEAN {[BOOLEAN {[a] AND [b]}] AND [c]}');
$p->pass('+(a b)'                , 'REQUIRED {BOOLEAN {[a] AND [b]}}');
$p->pass('a:(+baz +"foo bar")'   , 'FIELD {a IS BOOLEAN {[REQUIRED {baz}] AND [REQUIRED {PHRASE {"foo bar"}}]}}');
$p->fail('(foo');
$p->fail('foo)))');

// This parts gets a little confusing because of the different notations.
// xfCriterionRange uses the mathematical: [] is inclusive, () is exclusive
// Lucene syntax uses its own: [] is inclusive, {} is exclusive
$t->diag('Range');
$p->pass('{1 to 3}'             , 'RANGE {(1,3)}');
$p->pass('[1 to 3]'             , 'RANGE {[1,3]}');
$p->pass('{1 to 3]'             , 'RANGE {(1,3]}');
$p->pass('[1 to 3}'             , 'RANGE {[1,3)}');
$p->pass('foo: [1 to 40]'       , 'FIELD {foo IS RANGE {[1,40]}}');
$p->pass('foo [1 to 40] bar'    , 'BOOLEAN {[foo] AND [RANGE {[1,40]}] AND [bar]}');
$p->fail('[');
$p->fail('[1');
$p->fail('[1 to');
$p->fail('[1 to 3');

$t->diag('Wildcard');
$p->pass('m*n'                  , 'WILDCARD {m*n}');
$p->pass('m?n'                  , 'WILDCARD {m?n}');
$p->pass('t*s?'                 , 'WILDCARD {t*s?}');

$t->diag('Special Characters');
$p->pass('\+foo'                 , '+foo');
$p->pass('\-foo'                 , '-foo');

$t->diag('Default Slop');
$t->is($parser->getPhraseSlop(), 0, '->getPhraseSlop() returns the default phrase slop');
$parser->setPhraseSlop(2);
$t->is($parser->getPhraseSlop(), 2, '->setPhraseSlop() changes the default phrase slop');
$p->pass('"foo bar"'            , 'PHRASE {"foo bar" SLOP 2}');
$p->pass('"foo bar"~1'          , 'PHRASE {"foo bar" SLOP 1}');
$p->pass('"foo bar"~0'          , 'PHRASE {"foo bar"}');

$p->stats();
