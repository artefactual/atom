<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'stemmer/xfLuceneStemmer.interface.php';
require 'stemmer/xfLuceneStemmerPorter.class.php';
require 'vendor/PorterStemmer/PorterStemmer.class.php';

$t = new lime_test(5, new lime_output_color);

$s = new xfLuceneStemmerPorter;

$try = array(
  'nationalize' => 'nation',
  'cats' => 'cat',
  'publicity' => 'public',
  'dog' => 'dog',
  'explained' => 'explain',
);

foreach ($try as $orig => $exp)
{
  $t->is($s->doStem($orig), $exp, '->doStem() stems "' . $orig . '"');
}
