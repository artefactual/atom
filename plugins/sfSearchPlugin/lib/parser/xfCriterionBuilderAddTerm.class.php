<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A builder action to add a lexeme as a search term.
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfCriterionBuilderAddTerm extends xfCriterionBuilderActionCommon
{
  /**
   * @see xfFiniteStateMachineAction
   */
  public function execute()
  {
    $c = new xfCriterionTerm($this->builder->getLexeme()->getLexeme());
    $this->builder->add($c);
  }
}
