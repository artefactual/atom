<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A logger that interacts with the task system to present the results.
 *
 * @package sfSearch
 * @subpackage Log
 * @author Carl Vondrick
 */
final class xfLoggerTask implements xfLogger
{
  /**
   * The event dispatcher
   *
   * @var sfEventDispatcher
   */
  private $dispatcher;

  /**
   * The formatter
   *
   * @var sfFormatter
   */
  private $formatter;

  /**
   * Constructor to set dispatcher and formatter.
   *
   * @param sfEventDispatcher $dispatcher
   * @param sfFormatter $formatter
   */
  public function __construct(sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->dispatcher = $dispatcher;
    $this->formatter = $formatter;
  }

  /**
   * @see xfLogger
   */
  public function log($message, $section = 'sfSearch')
  {
    $message = preg_replace('/"(.+?)"/e', '$this->formatter->format("\\1", array("fg" => "blue", "bold" => true));', $message);
    $message = preg_replace('/\.{3}$/e', '$this->formatter->format("...", array("fg" => "red", "bold" => true));', $message);
    $message = preg_replace('/(Warning|Error)!/e', '$this->formatter->format("\\1!", array("fg" => "red", "bold" => true));', $message);

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format($section, array('fg' => 'green', 'bold' => true)) . ' >> ' . $message)));
  }
}
