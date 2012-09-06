<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';

require 'form/sfForm.class.php';
require 'widget/sfWidget.class.php';
require 'widget/sfWidgetForm.class.php';
require 'widget/sfWidgetFormSchema.class.php';
require 'widget/sfWidgetFormInput.class.php';
require 'validator/sfValidatorError.class.php';
require 'validator/sfValidatorErrorSchema.class.php';
require 'validator/sfValidatorBase.class.php';
require 'validator/sfValidatorSchema.class.php';
require 'validator/sfValidatorString.class.php';
require 'validator/sfValidatorInteger.class.php';
require 'form/xfForm.class.php';
require 'form/xfSimpleFormBase.class.php';
require 'form/xfSimpleForm.class.php';
require 'util/xfException.class.php';

$t = new lime_test(7, new lime_output_color);

$form = new xfSimpleForm;

$t->isa_ok($form->getWidgetSchema()->offsetGet('query'), 'sfWidgetFormInput', '->configure() creates a query widget');
$t->isa_ok($form->getValidatorSchema()->offsetGet('query'), 'sfValidatorString', '->configure() validates "query" by "sfValidatorString"');
$t->isa_ok($form->getValidatorSchema()->offsetGet('page'), 'sfValidatorInteger', '->configure() validates "page" by "sfValidatorInteger"');

foreach (array('getPageNumber', 'getUrlFormat') as $method) {
  try {
    $msg = '->' . $method . '() fails if form is not bound';
    $form->$method();
    $t->fail($msg);
  } catch (Exception $e) {
    $t->pass($msg);
  }
}

$form->bind(array(
  'query' => 'foobar',
  'page' => 5
));

$t->is($form->getPageNumber(), 5, '->getPageNumber() returns the page number.');

$t->is($form->getUrlFormat(), '?query=foobar&amp;page=%page%', '->getUrlFormat() returns the URL format');
