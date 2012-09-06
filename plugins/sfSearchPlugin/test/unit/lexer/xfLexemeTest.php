<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'lexer/xfLexeme.class.php';

$t = new lime_test(4, new lime_output_color);

$lexeme = new xfLexeme('lexeme', 'type', 42);

$t->is($lexeme->getType(), 'type', '->getType() returns the type');
$t->is($lexeme->getLexeme(), 'lexeme', '->getLexeme() returns the lexeme');
$t->is($lexeme->getPosition(), 42, '->getPosition() returns the position');
$lexeme->setType('typer');
$t->is($lexeme->getType(), 'typer', '->setType() changes the type');
