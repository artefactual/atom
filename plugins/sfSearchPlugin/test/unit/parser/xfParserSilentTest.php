<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'parser/xfParser.interface.php';
require 'parser/xfParserSimple.class.php';
require 'parser/xfParserSilent.class.php';
require 'criteria/xfCriterion.interface.php';
require 'criteria/xfCriterionTerm.class.php';
require 'criteria/xfCriteria.class.php';

$t = new lime_test(1, new lime_output_color);

class BadParser implements xfParser
{
  public function parse($q, $encoding = 'utf8')
  {
    throw new Exception('foo');
  }
}

$silent = new xfParserSilent(new BadParser);
$t->is($silent->parse('foo')->toString(), 'foo', '->parse() ignores the exception');
