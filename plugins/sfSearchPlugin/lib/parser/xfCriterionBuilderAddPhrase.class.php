<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A builder to add phrase to the criterion builder.
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfCriterionBuilderAddPhrase extends xfCriterionBuilderActionCommon
{
  /**
   * The default slop
   *
   * @var int
   */
  private $slop;

  /**
   * @see xfCriterionBuilderAddPhrase
   */
  public function __construct(xfCriterionBuilder $b, $slop = 0)
  {
    parent::__construct($b);

    $this->slop = $slop;
  }

  /**
   * @see xfFiniteStateMachineAction
   */
  public function execute()
  {
    $c = new xfCriterionPhrase($this->builder->getLexeme()->getLexeme(), $this->slop);
    $this->builder->add($c);
  }
}
