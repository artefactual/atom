<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__).'/../bootstrap.php';
require_once dirname(__FILE__).'/../../lib/widget/sfWidgetFormSelectUSState.class.php';

$t = new lime_test(3, new lime_output_color());

// ->__construct()
$t->diag('->__construct()');

try
{
  new sfWidgetFormSelectUSState();
  $t->pass('->__construct() does not expect a "choices" option');
}
catch (RuntimeException $e)
{
  $t->fail('->__construct() does not expect a "choices" option');
}

// ->render()
$t->diag('->render()');

$w = new sfWidgetFormSelectUSState();
$t->is(substr_count($w->render('state'), '<option'), 51, '->render() returns 51 select options');

$w = new sfWidgetFormSelectUSState(array('add_empty' => true));
$t->is(substr_count($w->render('state'), '<option'), 52, '->render() returns 52 select options when "add_empty" option is used');
