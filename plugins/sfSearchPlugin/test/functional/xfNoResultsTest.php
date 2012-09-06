<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../bootstrap/functional.php';
require 'mock/engine/xfMockEngine.class.php';
bootstrap('frontend');

$index = xfIndexManager::get('TestSearch');
$index->setEngine(new xfMockEngine);

$b = new xfTestBrowser();

$b->
  get('search/search', array('do' => 'search', 'search' => array('query' => 'foobar')))->
  isStatusCode(200)->
  isRequestParameter('module', 'search')->
  isRequestParameter('action', 'search')->
  checkResponseElement('title', 'No Search Results')->
  checkResponseElement('h2', 'No Search Results', array('position' => 0))->
  checkResponseElement('form[method="get"]', true)->
  checkResponseElement('form label[for="search_query"]', 'Query')->
  checkResponseElement('form input[id="search_query"]', true)->
  checkResponseElement('form input[id="search_query"][value="foobar"]', true)->
  checkResponseElement('form input[type="submit"]', true)
  ;
