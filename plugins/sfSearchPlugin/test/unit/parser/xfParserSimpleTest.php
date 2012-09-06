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
require 'criteria/xfCriterion.interface.php';
require 'criteria/xfCriterionTerm.class.php';
require 'criteria/xfCriteria.class.php';

$t = new lime_test(2, new lime_output_color);

$p = new xfParserSimple;
$c = $p->parse("   foo      bar\n\r\tbaz");

$t->isa_ok($c, 'xfCriteria', '->parse() returns a xfCriteria');
$t->is($c->toString(), 'BOOLEAN {[foo] AND [bar] AND [baz]}', '->parse() tokenizes correctly');
