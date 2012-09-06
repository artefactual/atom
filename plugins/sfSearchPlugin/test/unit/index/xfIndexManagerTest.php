<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'index/xfIndexManager.class.php';
require 'mock/index/xfMockIndex.class.php';
require 'log/xfLogger.interface.php';
require 'log/xfLoggerBlackhole.class.php';
require 'util/xfException.class.php';

$t = new lime_test(3, new lime_output_color);

$index = xfIndexManager::get('xfMockIndex');
$t->isa_ok($index, 'xfMockIndex', '->getIndex() retuns an instance of the index');
$t->ok($index === xfIndexManager::get('xfMockIndex'), '->getIndex() is a singleton method');

try {
  $msg = '->getIndex() fails if index does not inherit xfIndex';
  xfIndexManager::get('Exception');
  $t->fail($msg);
} catch (Exception $e)
{
  $t->pass($msg);
}
