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
require 'lexer/xfLexer.interface.php';
require 'lexer/xfLexeme.class.php';
require 'lexer/xfLexerTester.class.php';
require 'lexer/xfLexemeBuilder.class.php';
require 'lexer/xfLexemeBuilderActionCommon.class.php';
require 'lexer/xfLexemeBuilderAddChar.class.php';
require 'lexer/xfLexemeBuilderAddLexeme.class.php';
require 'lexer/lucene/xfLexerLucene.class.php';
require 'lexer/lucene/xfLexemeLucene.class.php';
require 'lexer/lucene/xfLexemeBuilderLuceneAddSyntax.class.php';
require 'parser/xfParserFSMError.class.php';
require 'parser/xfParserException.class.php';

$t = new xfLexerTester(new lime_test(182, new lime_output_color), new xfLexerLucene);

$t->pass('', array());

$t->pass("   \n \t \r    ", array());

$t->pass('foo', array(array('foo', xfLexemeLucene::WORD)));

$t->pass('123', array(array('123', xfLexemeLucene::WORD)));

$t->pass('123.45', array(array('123.45', xfLexemeLucene::WORD)));

$t->pass('foo bar baz', array(array('foo', xfLexemeLucene::WORD),
                              array('bar', xfLexemeLucene::WORD),
                              array('baz', xfLexemeLucene::WORD)));

$t->pass('foo^3', array(array('foo', xfLexemeLucene::WORD),
                        array('^', xfLexemeLucene::SYNTAX),
                        array('3', xfLexemeLucene::NUMBER)));

$t->pass('foo^3.5', array(array('foo', xfLexemeLucene::WORD),
                          array('^', xfLexemeLucene::SYNTAX),
                          array('3.5', xfLexemeLucene::NUMBER)));

$t->fail('foo^3.5.6', 'Invalid number syntax: make sure the number only has one decimal point and contains only digits.');

$t->pass('"foo bar"', array(array('foo bar', xfLexemeLucene::PHRASE)));

$t->pass('symfony "foo bar" project', array(array('symfony', xfLexemeLucene::WORD),
                                            array('foo bar', xfLexemeLucene::PHRASE),
                                            array('project', xfLexemeLucene::WORD)));

$t->pass('"foo bar"~3', array(array('foo bar', xfLexemeLucene::PHRASE),
                              array('~', xfLexemeLucene::SYNTAX),
                              array('3', xfLexemeLucene::NUMBER)));

$t->pass('"foo bar"~3^4', array(array('foo bar', xfLexemeLucene::PHRASE),
                                array('~', xfLexemeLucene::SYNTAX),
                                array('3', xfLexemeLucene::NUMBER),
                                array('^', xfLexemeLucene::SYNTAX),
                                array('4', xfLexemeLucene::NUMBER)));

$t->pass('"and i was \\& born"', array(array('and i was & born', xfLexemeLucene::PHRASE)));

$t->fail('"foo', 'Unexpected end of query.');

$t->pass('foo: bar', array(array('foo', xfLexemeLucene::FIELD),
                           array(':', xfLexemeLucene::SYNTAX),
                           array('bar', xfLexemeLucene::WORD)));
$t->fail(': bar');

$t->fail('^: bar', 'The field must be a word.');

$t->pass('+bar', array(array('+', xfLexemeLucene::SYNTAX),
                       array('bar', xfLexemeLucene::WORD)));

$t->pass('-bar', array(array('-', xfLexemeLucene::SYNTAX),
                       array('bar', xfLexemeLucene::WORD)));

$t->pass('\\+boo', array(array('+boo', xfLexemeLucene::WORD)));


$t->pass('\\&', array(array('&', xfLexemeLucene::WORD)));

$t->pass('a && b', array(array('a', xfLexemeLucene::WORD),
                         array('&&', xfLexemeLucene::SYNTAX),
                         array('b', xfLexemeLucene::WORD)));

$t->fail('a & b', 'A & must follow a & character.');

$t->pass('a || b', array(array('a', xfLexemeLucene::WORD),
                         array('||', xfLexemeLucene::SYNTAX),
                         array('b', xfLexemeLucene::WORD)));

$t->fail('a | b', 'A | must follow a | character.');

$t->pass('[1 to 30]', array(array('[', xfLexemeLucene::RANGE_START_INCLUSIVE),
                            array('1', xfLexemeLucene::WORD),
                            array('to', xfLexemeLucene::RANGE_SEPARATOR),
                            array('30', xfLexemeLucene::WORD),
                            array(']', xfLexemeLucene::RANGE_END_INCLUSIVE)));

$t->pass('m*n', array(array('m*n', xfLexemeLucene::WILDCARD)));

$t->pass('m?n', array(array('m?n', xfLexemeLucene::WILDCARD)));

$t->pass('t*s?', array(array('t*s?', xfLexemeLucene::WILDCARD)));

$t->pass('(+a || \\b) && f: c && ("foo bar"~2 || (y && +x))^2.5', array(array('(', xfLexemeLucene::SYNTAX),
                                                                        array('+', xfLexemeLucene::SYNTAX),
                                                                        array('a', xfLexemeLucene::WORD),
                                                                        array('||', xfLexemeLucene::SYNTAX),
                                                                        array('b', xfLexemeLucene::WORD),
                                                                        array(')', xfLexemeLucene::SYNTAX),
                                                                        array('&&', xfLexemeLucene::SYNTAX),
                                                                        array('f', xfLexemeLucene::FIELD),
                                                                        array(':', xfLexemeLucene::SYNTAX),
                                                                        array('c', xfLexemeLucene::WORD),
                                                                        array('&&', xfLexemeLucene::SYNTAX),
                                                                        array('(', xfLexemeLucene::SYNTAX),
                                                                        array('foo bar', xfLexemeLucene::PHRASE),
                                                                        array('~', xfLexemeLucene::SYNTAX),
                                                                        array('2', xfLexemeLucene::NUMBER),
                                                                        array('||', xfLexemeLucene::SYNTAX),
                                                                        array('(', xfLexemeLucene::SYNTAX),
                                                                        array('y', xfLexemeLucene::WORD),
                                                                        array('&&', xfLexemeLucene::SYNTAX),
                                                                        array('+', xfLexemeLucene::SYNTAX),
                                                                        array('x', xfLexemeLucene::WORD),
                                                                        array(')', xfLexemeLucene::SYNTAX),
                                                                        array(')', xfLexemeLucene::SYNTAX),
                                                                        array('^', xfLexemeLucene::SYNTAX),
                                                                        array('2.5', xfLexemeLucene::NUMBER)));
