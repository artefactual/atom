<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A builder action to set the next field.
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfCriterionBuilderSetField extends xfCriterionBuilderActionCommon
{
  /**
   * @see xfFiniteStateMachineAction
   */
  public function execute()
  {
    $this->builder->addDecorator('xfCriterionField', array($this->builder->getLexeme()->getLexeme()));
  }
}
