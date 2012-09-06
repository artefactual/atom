<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__).'/../bootstrap.php';
require_once dirname(__FILE__).'/../../lib/validator/sfValidatorDefault.class.php';

$t = new lime_test(4);

// ->clean()
$t->diag('->clean()');

$validator = new sfValidatorDefault(array(
  'validator' => new sfValidatorString(),
  'default'   => '==DEFAULT==',
));
$t->is($validator->clean('foo'), 'foo', '->clean() returns cleaned values');
$t->is($validator->clean(null), '==DEFAULT==', '->clean() returns the default on validation failure');

$validator = new sfValidatorDefault(array(
  'validator' => new sfValidatorString(array('empty_value' => '==EMPTY==')),
));
$t->is($validator->clean(null), '==EMPTY==', '->clean() returns the validator empty value if no default value is set');

$validator = new sfValidatorDefault(array(
  'validator' => new sfValidatorString(array('empty_value' => '==EMPTY==')),
  'default'   => '==DEFAULT==',
));
$t->is($validator->clean(null), '==DEFAULT==', '->clean() returns the default if both default and the embedded empty value are set');
