<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An action for the finite state machine.
 *
 * An action can be executed in many scenarios:
 *  * entry action
 *  * exit action
 *  * input action
 *  * transition action
 * 
 * @see xfFiniteStateMachine
 * @package sfSearch
 * @subpackage FSM
 * @author Carl Vondrick
 */
interface xfFiniteStateMachineAction
{
  /**
   * Executes the action.
   */
  public function execute();
}
