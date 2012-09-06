<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../../bootstrap/unit.php';
require 'lexer/xfLexeme.class.php';
require 'lexer/lucene/xfLexemeLucene.class.php';

class xfLexemeLuceneTester
{
  public $lime;

  public function __construct(lime_test $t)
  {
    $this->lime = $t;
  }

  public function test($lexeme, $type, $expected, $position = 1)
  {
    $l = new xfLexemeLucene($lexeme, $type, $position);

    $this->lime->is($l->getType(), $expected, $lexeme . ' of ' . $type . ' is ' . $expected);
  }
}

$t = new lime_test(10, new lime_output_color);
$l = new xfLexemeLuceneTester($t);

$l->test('[', xfLexemeLucene::SYNTAX, xfLexemeLucene::RANGE_START_INCLUSIVE);
$l->test('{', xfLexemeLucene::SYNTAX, xfLexemeLucene::RANGE_START_EXCLUSIVE);
$l->test(']', xfLexemeLucene::SYNTAX, xfLexemeLucene::RANGE_END_INCLUSIVE);
$l->test('}', xfLexemeLucene::SYNTAX, xfLexemeLucene::RANGE_END_EXCLUSIVE);
$l->test('to', xfLexemeLucene::WORD, xfLexemeLucene::RANGE_SEPARATOR);
$l->test('and', xfLexemeLucene::WORD, xfLexemeLucene::SYNTAX);
$l->test('or', xfLexemeLucene::WORD, xfLexemeLucene::SYNTAX);
$l->test('not', xfLexemeLucene::WORD, xfLexemeLucene::SYNTAX);
$l->test('fo?', xfLexemeLucene::WORD, xfLexemeLucene::WILDCARD);
$l->test('fo*', xfLexemeLucene::WORD, xfLexemeLucene::WILDCARD);
