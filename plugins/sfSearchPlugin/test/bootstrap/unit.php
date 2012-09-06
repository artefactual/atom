<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../../test/symfony.php';

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../../lib');
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../../test');

require 'vendor/lime/lime.php';
