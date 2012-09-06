<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A Lexeme with Lucene types.
 *
 * @package sfSearch
 * @subpackage Lexer
 * @author Carl Vondrick
 */
final class xfLexemeLucene extends xfLexeme
{
  const WORD = 1;
  const SYNTAX = 2;
  const PHRASE = 3;
  const NUMBER = 4;
  const FIELD = 5;
  const WILDCARD = 6;
  const RANGE_START_INCLUSIVE = 100;
  const RANGE_START_EXCLUSIVE = 101;
  const RANGE_END_INCLUSIVE   = 102;
  const RANGE_END_EXCLUSIVE   = 103;
  const RANGE_SEPARATOR       = 104;

  /**
   * @see xfLexeme
   */
  public function setType($type)
  {
    $lexeme = strtolower($this->getLexeme());

    if ($type == self::SYNTAX)
    {
      if ($lexeme == '[')
      {
        $type = self::RANGE_START_INCLUSIVE;
      }
      elseif ($lexeme == '{')
      {
        $type = self::RANGE_START_EXCLUSIVE;
      }
      elseif ($lexeme == ']')
      {
        $type = self::RANGE_END_INCLUSIVE;
      }
      elseif ($lexeme == '}')
      {
        $type = self::RANGE_END_EXCLUSIVE;
      }
    }
    elseif ($type == self::WORD)
    {
      if ($lexeme == 'to')
      {
        $type = self::RANGE_SEPARATOR;
      }
      elseif ($lexeme == 'and')
      {
        $type = self::SYNTAX;
      }
      elseif ($lexeme == 'or')
      {
        $type = self::SYNTAX;
      }
      elseif ($lexeme == 'not')
      {
        $type = self::SYNTAX;
      }
      elseif (false !== strpos($lexeme, '*') || false !== strpos($lexeme, '?'))
      {
        $type = self::WILDCARD;
      }
    }

    parent::setType($type);
  }
}
