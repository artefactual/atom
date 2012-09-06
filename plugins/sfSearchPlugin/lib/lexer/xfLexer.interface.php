<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A lexer takes text and converts into lexemes.
 *
 * @package sfSearch
 * @subpackage Lexer
 * @author Carl Vondrick
 */
interface xfLexer
{
  /**
   * Tokenizes the input
   *
   * @param string $stream
   * @param string $encoding
   * @returns array of xfLexeme's
   */
  public function tokenize($stream, $encoding = 'utf8');
}
