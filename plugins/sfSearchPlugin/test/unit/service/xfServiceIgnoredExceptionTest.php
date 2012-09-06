<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'service/xfService.class.php';
require 'mock/service/xfMockIdentifier.class.php';
require 'util/xfException.class.php';
require 'service/xfServiceException.class.php';
require 'service/xfServiceIgnoredException.class.php';

$t = new lime_test(1, new lime_output_color);

$service = new xfService(new xfMockIdentifier);
$e = new xfServiceIgnoredException($service);

$t->ok($e->getService() === $service, '->getService() returns the service');
