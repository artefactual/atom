<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A logger wrapper for the event dispatcher
 *
 * @package sfSearch
 * @subpackage Log
 * @author Carl Vondrick
 */
final class xfLoggerEventDispatcher implements xfLogger
{
  /**
   * The event dispatcher
   *
   * @var sfEventDispatcher
   */
  private $dispatcher;

  /**
   * The event to notify
   *
   * @var string
   */
  private $event = 'search.log';

  /**
   * Constructor to set event dispatcher
   *
   * @param sfEventDispatcher $dispatcher
   */
  public function __construct(sfEventDispatcher $dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }

  /**
   * Sets the event to notify
   *
   * @param string $event
   */
  public function setEventName($event)
  {
    $this->event = $event;
  }

  /**
   * @see xfLogger
   */
  public function log($message, $section = 'sfSearch')
  {
    $this->dispatcher->notify(new sfEvent($this, $this->event, array($message, 'section' => $section)));
  }
}
