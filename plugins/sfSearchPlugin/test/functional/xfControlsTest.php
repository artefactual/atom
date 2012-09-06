<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../bootstrap/functional.php';
bootstrap('frontend');

$b = new xfTestBrowser();
$b->
  getAndCheck('search', 'search')->
  checkResponseElement('title', 'Search')->
  checkResponseElement('h2', 'Search', array('position' => 0))->
  checkResponseElement('form[method="get"]', true)->
  checkResponseElement('form label[for="search_query"]', 'Query')->
  checkResponseElement('form input[id="search_query"]', true)->
  checkResponseElement('form input[type="submit"]', true)
  ;

