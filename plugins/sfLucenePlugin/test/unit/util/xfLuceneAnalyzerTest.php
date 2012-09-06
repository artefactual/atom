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
require 'util/xfLuceneAnalyzer.class.php';
require 'stemmer/xfLuceneStemmer.interface.php';
require 'stemmer/xfLuceneStemmerPorter.class.php';
require 'stemmer/xfLuceneStemmerTokenFilter.class.php';
require 'vendor/PorterStemmer/PorterStemmer.class.php';

$t = new lime_test(11, new lime_output_color);

$t->diag('->initialize()');

$values = array(
  xfLuceneAnalyzer::UTF8 => 'Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8',
  xfLuceneAnalyzer::UTF8 | xfLuceneAnalyzer::NUMBERS => 'Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num',
  xfLuceneAnalyzer::TEXT => 'Zend_Search_Lucene_Analysis_Analyzer_Common_Text',
  xfLuceneAnalyzer::TEXT | xfLuceneAnalyzer::NUMBERS => 'Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum'
);

ksort($values);

foreach ($values as $mode => $class)
{
  $a = new xfLuceneAnalyzer($mode);
  $t->isa_ok($a->getAnalyzer(), $class, '->configure() with mode "' . $mode . '" creates a "' . $class . '"');
}

$t->diag('->set*(), ->add*()');

$a = new xfLuceneAnalyzer(xfLuceneAnalyzer::UTF8);
$a->setCaseInsensitive();
$a->addStopWords(array('the', 'was', 'were'));
$a->addStopWordsFromFile(dirname(__FILE__) . '/stopwords.txt');
$a->setShortWordLength(2);
$a->setStemmer(new xfLuceneStemmerPorter);

$tokens = $a->tokenize('How am I today in this happyness worlds!');
$t->is(count($tokens), 6, '->tokenize() tokenizes the input');

$text = array('am', 'todai', 'in', 'thi', 'happy', 'world');

foreach ($text as $key => $expected)
{
  $t->is($tokens[$key]->getTermText(), $expected, '->tokenize() token "' . $key . '" has text "' . $expected . '"');
}

