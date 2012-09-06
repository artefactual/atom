<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Connects with xfLexemeBuilder and xfFiniteStateMachine to add a char
 * to the current lexeme.
 *
 * @package sfSearch
 * @subpackage Lexer
 * @author Carl Vondrick
 */
final class xfLexemeBuilderAddChar extends xfLexemeBuilderActionCommon
{
  /**
   * @see xfFiniteStateMachineAction
   */
  public function execute()
  {
    $this->builder->addToLexeme($this->builder->getCharacter());
  }
}
