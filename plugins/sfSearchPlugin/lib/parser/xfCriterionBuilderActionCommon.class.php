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
 * @subpackage Parser
 * @author Carl Vondrick
 */
abstract class xfCriterionBuilderActionCommon implements xfFiniteStateMachineAction
{
  /**
   * The builder
   *
   * @var xfCriterionBuilder
   */
  protected $builder;

  /**
   * Constructor to set builder.
   *
   * @param xfCriterionBuilder $builder
   */
  public function __construct(xfCriterionBuilder $builder)
  {
    $this->builder = $builder;
  }
}
