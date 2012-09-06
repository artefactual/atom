<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'util/xfToolkit.class.php';

$t = new lime_test(2, new lime_output_color);
$t->is(xfToolkit::underscore('GetMyCatName'), 'get_my_cat_name', '::underscore() converts camel case to underscore case');
$t->is(xfToolkit::camelize('get_my_cat_name'), 'GetMyCatName', '::camelize() converts underscore case to camel case');
