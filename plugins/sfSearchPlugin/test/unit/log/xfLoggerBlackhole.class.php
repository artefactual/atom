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
require 'log/xfLoggerBlackhole.class.php';

$t = new lime_test(1, new lime_output_color);

$logger = new xfLoggerBlackhole;

$logger->log('foo');
$t->pass('->log() does nothing');
