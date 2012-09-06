<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Connects with xfLexemeBuilder and xfFiniteStateMachine to add a syntax 
 * lexeme to the builder.
 *
 * @package sfSearch
 * @subpackage Lexer
 * @author Carl Vondrick
 */
final class xfLexemeBuilderLuceneAddSyntax extends xfLexemeBuilderActionCommon
{
  /**
   * @see xfFiniteStateMachineAction
   */
  public function execute()
  {
    $this->builder->newLexeme();
    $this->builder->setType(xfLexemeLucene::SYNTAX);

    $char = $this->builder->getCharacter();

    // if we are dealing with a field
    if ($char == ':')
    {
      $previous = $this->builder->getLexeme(-1);

      if ($previous->getType() != xfLexemeLucene::WORD)
      {
        throw new xfParserException('The field must be a word.');
      }

      $previous->setType(xfLexemeLucene::FIELD);
    }
    // if we are dealing with a double
    elseif ($char == '&' || $char == '|')
    {
      $this->builder->advancePosition();

      if ($this->builder->getCharacter() != $char)
      {
        throw new xfParserException('A ' . $char . ' must follow a ' . $char . ' character.') ;
      }

      $char = $char . $char;
    }

    $this->builder->addToLexeme($char);
    $this->builder->commit();
  }
}
