<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'log/xfLogger.interface.php';
require 'log/xfLoggerTask.class.php';
require 'event/sfEvent.class.php';
require 'event/sfEventDispatcher.class.php';
require 'command/sfFormatter.class.php';

$t = new lime_test(3, new lime_output_color);

$formatter = new sfFormatter;
$dispatcher = new sfEventDispatcher;

function handle(sfEvent $event = null)
{
  static $got;

  if ($event)
  {
    $got = $event;
  }
  else
  {
    return $got;
  }
}

$dispatcher->connect('command.log', 'handle');

$logger = new xfLoggerTask($dispatcher, $formatter);
$logger->log('Did something', 'MySearch');

$t->is(handle()->getSubject(), $logger, '->log() logs with the logger as the subject');
$t->is(handle()->getParameters(), array('MySearch >> Did something'), '->log() formats the message');
$t->is(handle()->getName(), 'command.log', '->log() notifies the correct event');
