<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A common base class for builder actions.
 *
 * @package sfSearch
 * @subpackage Lexer
 * @author Carl Vondrick
 */
abstract class xfLexemeBuilderActionCommon implements xfFiniteStateMachineAction
{
  /**
   * The builder
   *
   * @var xfLexemeBuilder
   */
  protected $builder;

  /**
   * Constructor to set builder.
   *
   * @param xfLexemeBuilder $builder
   */
  public function __construct(xfLexemeBuilder $builder)
  {
    $this->builder = $builder;
  }
}
