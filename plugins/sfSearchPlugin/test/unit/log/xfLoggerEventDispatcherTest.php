<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'event/sfEventDispatcher.class.php';
require 'event/sfEvent.class.php';
require 'log/xfLogger.interface.php';
require 'log/xfLoggerEventDispatcher.class.php';

$t = new lime_test(3, new lime_output_color);

$dispatcher = new sfEventDispatcher;
$logger = new xfLoggerEventDispatcher($dispatcher);

$logger->setEventName('search.test');

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

$dispatcher->connect('search.test', 'handle');

$logger->log('foobar', 'MySearch');

$t->is(handle()->getName(), 'search.test', '->setEventName() changes the event name');
$t->is(handle()->getSubject(), $logger, '->log() sets the index name');
$t->is(handle()->getParameters(), array('foobar', 'section' => 'MySearch'), '->log() logs a message');
