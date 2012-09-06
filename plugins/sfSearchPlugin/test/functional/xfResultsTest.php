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
require 'mock/service/xfMockIdentifier.class.php';
require 'mock/builder/xfMockBuilder.class.php';
bootstrap('frontend');

class TitleRetort implements xfRetort
{
  public function can(xfDocumentHit $hit, $method, array $args = array())
  {
    return $method == 'getTitle';
  }

  public function respond(xfDocumentHit $hit, $method, array $args = array())
  {
    return 'Title' . $hit->getDocument()->getField('input')->getValue();
  }
}

class DescriptionRetort implements xfRetort
{
  public function can(xfDocumentHit $hit, $method, array $args = array())
  {
    return $method == 'getDescription';
  }

  public function respond(xfDocumentHit $hit, $method, array $args = array())
  {
    return 'Description' . $hit->getDocument()->getField('input')->getValue();
  }
}

$index = xfIndexManager::get('TestSearch');
$index->setEngine(new xfMockEngine);

$service = new xfService(new xfMockIdentifier);
$service->addBuilder(new xfMockBuilder);
$service->addRetort(new TitleRetort);
$service->addRetort(new DescriptionRetort);
$service->addRetort(new xfRetortRoute('module/action?input=$input$'));

$index->getServiceRegistry()->register($service);

for ($x = 0; $x < 245; $x++)
{
  $index->insert($x);
}

$b = new xfTestBrowser();

$b->
  get('search/search', array('do' => 'search', 'search' => array('query' => 'foobar')))->
  isStatusCode(200)->
  isRequestParameter('module', 'search')->
  isRequestParameter('action', 'search')->
  checkResponseElement('title', 'Results 1 to 10')->
  checkResponseElement('h2', 'Search Results', array('position' => 0))->

  checkResponseElement('ol[start="1"]', true)->
  checkResponseElement('ol li', 10)->
  checkResponseElement('ol li a', 10)->
  checkResponseElement('ol li a[href="/index.php/module/action/input/0"]', 'Title0', array('count' => 1))->
  checkResponseElement('ol li', '/Description0/', array('position' => 0))->

  checkResponseElement('form[method="get"]', true)->
  checkResponseElement('form label[for="search_query"]', 'Query')->
  checkResponseElement('form input[id="search_query"]', true)->
  checkResponseElement('form input[id="search_query"][value="foobar"]', true)->
  checkResponseElement('form input[type="submit"]', true)->

  checkResponseElement('div.pager', true)->
  checkResponseElement('div.pager strong', '1', array('position' => 0))->
  checkResponseElement('div.pager a', '2', array('position' => 0))->
  checkResponseElement('div.pager a', '3', array('position' => 1))->
  checkResponseElement('div.pager a', 'Next', array('position' => 3))->

  click('3')->
  isStatusCode(200)->
  isRequestParameter('module', 'search')->
  isRequestParameter('action', 'search')->
  isRequestParameter('search', array('query' => 'foobar', 'page' => 3))->
  checkResponseElement('title', 'Results 21 to 30')->
  checkResponseElement('h2', 'Search Results', array('position' => 0))->
  checkResponseElement('ol[start="21"]', true)->
  checkResponseElement('ol li', 10)->
  checkResponseElement('ol li', '/Description20/', array('position' => 0))->

  checkResponseElement('form[method="get"]', true)->
  checkResponseElement('form label[for="search_query"]', 'Query')->
  checkResponseElement('form input[id="search_query"]', true)->
  checkResponseElement('form input[id="search_query"][value="foobar"]', true)->
  checkResponseElement('form input[type="submit"]', true)->

  checkResponseElement('div.pager a', 'Prev', array('position' => 0))->
  checkResponseElement('div.pager a', '1', array('position' => 1))->
  checkResponseElement('div.pager a', '2', array('position' => 2))->
  checkResponseElement('div.pager a + a + a + strong', '3', array('position' => 0))->
  checkResponseElement('div.pager a', '4', array('position' => 3))->
  checkResponseElement('div.pager a', '5', array('position' => 4))->
  checkResponseElement('div.pager a', '6', array('position' => 5))->
  checkResponseElement('div.pager a', 'Next', array('position' => 6))->

  click('Next')->
  isStatusCode(200)->
  isRequestParameter('module', 'search')->
  isRequestParameter('action', 'search')->
  isRequestParameter('search', array('query' => 'foobar', 'page' => 4))->
  checkResponseElement('title', 'Results 31 to 40')->
  checkResponseElement('ol[start="31"]', true)->

  click('Prev')->
  isStatusCode(200)->
  isRequestParameter('module', 'search')->
  isRequestParameter('action', 'search')->
  isRequestParameter('search', array('query' => 'foobar', 'page' => 3))->

  get('search/search', array('do' => 'search', 'search' => array('query' => 'foobar', 'page' => 25)))->
  isStatusCode(200)->
  isRequestParameter('module', 'search')->
  isRequestParameter('action', 'search')->

  checkResponseElement('ol li', 5)->
  checkResponseElement('div.pager a', 'Prev', array('position' => 0))->
  checkResponseElement('div.pager a', '23', array('position' => 2))->
  checkResponseElement('div.pager a', '24', array('position' => 3))->
  checkResponseElement('div.pager a + a + a + strong', '25', array('position' => 0))->
  checkResponseElement('div.pager strong + a', 0)
;

