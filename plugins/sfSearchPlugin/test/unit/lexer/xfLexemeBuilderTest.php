<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'lexer/xfLexemeBuilder.class.php';
require 'lexer/xfLexeme.class.php';
require 'util/xfException.class.php';

$t = new lime_test(20, new lime_output_color);

$b = new xfLexemeBuilder('symfony pròject');

$t->is($b->getPosition(), -1, '->getPosition() is negative when processing has not started.');
$t->is($b->getLexemes(), array(), '->getLexemes() is empty when processing has not started.');
$t->is($b->getCharacter(), null, '->getCharacter() is null when processing has not started.');

$b->next();

$t->is($b->getPosition(), 0, '->next() advances the pointer');
$t->is($b->getCharacter(), 's', '->getCharacter() returns the current pointer.');

for ($x = 0; $x < 4; $x++) {
  $b->addToLexeme($b->getCharacter());
  $b->next();
}

$b->setType('foobar');
$b->commit();

$t->is($b->count(), 1, '->addToLexeme(), ->commit() create lexemes');
$t->is($b->getLexeme(0)->getLexeme(), 'symf', '->getLexeme() returns the lexeme by index.');
$t->is($b->getLexeme(0)->getType(), 'foobar', '->commit() sets the appropriate type');
$t->is($b->getLexeme(0)->getPosition(), 4, '->commit() sets the appropriate position');

for ($x = 0; $x < 3; $x++) {
  $b->addToLexeme($b->getCharacter());
  $b->next();
}

$b->setType('baz');
$b->commit();
$t->is($b->next(), 'p', '->next() returns the next character.');

for ($x = 0; $x < 4; $x++) {
  $b->addToLexeme($b->getCharacter());
  $b->next();
}

$b->setType('gab');
$t->is($b->getType(), 'gab', '->getType() is returns the type');
$b->commit();

$b->advancePosition(3);
$t->is($b->getPosition(), 15, '->advancePosition() moves the position up');
$t->is($b->next(), false, '->next() returns false when at the end');

$t->is($b->getLexeme(2)->getLexeme(), 'pròj', '->getLexeme() looks up in the index.');
$t->is($b->getLexeme(-1)->getLexeme(), 'pròj', '->getLexeme() with negative goes backwards in index');

try {
  $msg = '->getLexeme() fails if the commit is out of range';
  $b->getLexeme(-10);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$b->commit();
$t->is($b->count(), 3, '->commit() does not create a new lexeme if the text is empty');

$b = new xfLexemeBuilder('102');
$t->is($b->next(), 1, '->next() works on numbers');
$t->is($b->next(), 0, '->next() works on numbers');
$t->is($b->next(), 2, '->next() works on numbers');
