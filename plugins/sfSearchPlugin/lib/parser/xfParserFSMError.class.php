<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An Finite State Machine Action parser error action.
 *
 * @package sfSearch
 * @subpackage Parser
 * @author Carl Vondrick
 */
final class xfParserFSMError implements xfFiniteStateMachineAction
{
  /**
   * The message
   *
   * @var string
   */
  private $message;
  
  /**
   * Constructor to set the message.
   *
   * @param string $message
   */
  public function __construct($message)
  {
    $this->message = $message;
  }

  /**
   * @see xfFiniteStateMachineAction
   */
  public function execute()
  {
    throw new xfParserException($this->message);
  }
}
