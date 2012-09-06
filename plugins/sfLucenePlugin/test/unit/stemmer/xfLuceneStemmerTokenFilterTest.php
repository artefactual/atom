<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'util/xfLuceneZendManager.class.php';
require 'stemmer/xfLuceneStemmerTokenFilter.class.php';
require 'stemmer/xfLuceneStemmer.interface.php';
require 'stemmer/xfLuceneStemmerPorter.class.php';
require 'vendor/PorterStemmer/PorterStemmer.class.php';

$t = new lime_test(2, new lime_output_color);

$s = new xfLuceneStemmerPorter;
$filter = new xfLuceneStemmerTokenFilter($s);

$token = new Zend_Search_Lucene_Analysis_Token('nationalize', 10, 21);
$token->setPositionIncrement(0);

$response = $filter->normalize($token);

$t->isa_ok($response, 'Zend_Search_Lucene_Analysis_Token', '->normalize() returns a Zend_Search_Lucene_Analysis_Token');
$t->is($response->getTermText(), 'nation', '->normalize() consults the stemmer');
