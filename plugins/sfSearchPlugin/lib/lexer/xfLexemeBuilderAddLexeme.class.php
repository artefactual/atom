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
final class xfLexemeBuilderAddLexeme extends xfLexemeBuilderActionCommon
{
  /**
   * The type of lexeme to create.
   *
   * @var scalar
   */
  private $type;

  /**
   * Constructor
   *
   * @param xfLexemeBuilder $builder
   * @param scalar $type The type of lexeme to create
   */
  public function __construct(xfLexemeBuilder $builder, $type)
  {
    parent::__construct($builder);

    $this->type = $type;
  }

  /**
   * @see xfFiniteStateMachineAction
   */
  public function execute()
  {
    $this->builder->setType($this->type);
    $this->builder->commit();
  }
}
