<?php

include(dirname(__FILE__).'/../../../../test/bootstrap/unit.php');
if(!isset($sf_symfony_lib_dir))
{
  $sf_symfony_lib_dir = $configuration->getSymfonyLibDir();
}
require_once(dirname(__FILE__).'/../../lib/sfWebBrowser.class.php');
require_once(dirname(__FILE__).'/../../lib/sfFopenAdapter.class.php');
require_once(dirname(__FILE__).'/../../lib/sfCurlAdapter.class.php');
require_once(dirname(__FILE__).'/../../lib/sfSocketsAdapter.class.php');
require_once($sf_symfony_lib_dir.'/exception/sfException.class.php');
require_once(dirname(__FILE__).'/../../lib/sfWebBrowserInvalidResponseException.class.php');
require_once($sf_symfony_lib_dir.'/config/sfConfig.class.php');
require_once($sf_symfony_lib_dir.'/util/sfDomCssSelector.class.php');
require_once($sf_symfony_lib_dir.'/util/sfToolkit.class.php');

// Configuration
// -- this script is needed for some tests. It is located in plugin's test/unit/utils folder
$dump_headers_url = 'http://localhost/dumpheaders.php';

// tests
$nb_test_orig = 73;
$adapter_list = array('sfCurlAdapter', 'sfFopenAdapter', 'sfSocketsAdapter');

// -- sites used for testing requests
$example_site_url = 'http://www.google.com';
$askeet_params = array(
  'url'         => 'http://www.askeet.com',
  'login'       => 'francois',
  'password'    => 'llactnevda2',
);

// -- cookies, file and directory automatically created
$cookies_dir = dirname(__FILE__).'/../data/sfCurlAdapter';
$cookies_file = $cookies_dir.'/cookies.txt';

/**
 * stub class
 *
 **/
class myTestWebBrowser extends sfWebBrowser
{
  protected $requestMethod;
  public function call($uri, $method = 'GET', $parameters = array(), $headers = array(), $changeStack = true)
  {
    parent::call($uri, $method, $parameters, $headers, $changeStack);
    $this->requestMethod = $this->stack[$this->stackPosition]['method'];
  }
  public function getRequestMethod()
  {
    return $this->requestMethod;
  }
}

$t = new lime_test($nb_test_orig * count($adapter_list), new lime_output_color());
foreach($adapter_list as $adapter)
{
  $t->diag('Testing '.$adapter);
  $t->diag('');
  
  /******************/
  /* Initialization */
  /******************/

  $t->diag('Initialization');
  $b = new sfWebBrowser(array(), $adapter);
  
  $t->is($b->getUserAgent(), '', 'a new browser has an empty user agent');
  $t->is($b->getResponseText(), '', 'a new browser has an empty response');
  $t->is($b->getResponseCode(), '', 'a new browser has an empty response code');
  $t->is($b->getResponseHeaders(), array(), 'a new browser has empty reponse headers');
  
  /*******************/
  /* Utility methods */
  /*******************/
  
  $t->diag('Utility methods');
  $b = new sfWebBrowser(array(), $adapter);
  $t->is($b->setUserAgent('foo bar')->getUserAgent(), 'foo bar', 'setUserAgent() sets the user agent');
  $t->is($b->setResponseText('foo bar')->getResponseText(), 'foo bar', 'setResponseText() extracts the response');
  $t->is($b->setResponseCode('foo 123 bar')->getResponseCode(), '123', 'setResponseCode() extracts the three-digits code');
  $t->is($b->setResponseCode('foo 12 bar')->getResponseCode(), '', 'setResponseCode() fails silently when response status is incorrect');
  $t->is_deeply($b->setResponseHeaders(array('HTTP1.1 200 OK', 'foo: bar', 'bar: baz'))->getResponseHeaders(), array('Foo' => 'bar', 'Bar' => 'baz'), 'setResponseHeaders() extracts the headers array');
  $t->is_deeply($b->setResponseHeaders(array('ETag: "535a8-9fb-44ff4a13"', 'WWW-Authenticate: Basic realm="Myself"'))->getResponseHeaders(), array('ETag' => '"535a8-9fb-44ff4a13"', 'WWW-Authenticate' => 'Basic realm="Myself"'), 'setResponseHeaders() extracts the headers array and accepts response headers with several uppercase characters');
  $t->is_deeply($b->setResponseHeaders(array('HTTP1.1 200 OK', 'foo: bar', 'bar:baz', 'baz:bar'))->getResponseHeaders(), array('Foo' => 'bar'), 'setResponseHeaders() ignores malformed headers');
  
  /**************/
  /* Exceptions */
  /**************/
  
  $t->diag('Exceptions');
  $b = new sfWebBrowser(array(), $adapter);
  try
  {
    $b->get('htp://askeet');
    $t->fail('get() throws an exception when passed an uri which is neither http nor https');
  }
  catch (Exception $e)
  {
    $t->pass('get() throws an exception when passed an uri which is neither http nor https');
  }
  
  /**********************/
  /* Simple GET request */
  /**********************/
  
  $t->diag('Simple GET request');
  $t->like($b->get($dump_headers_url)->getResponseText(), '/\[REQUEST_METHOD\] => GET/', 'get() performs a GET request');
  $t->isa_ok($b, 'sfWebBrowser', 'get() make a web request and returns a browser object');
  $t->is($b->getResponseCode(), 200, 'get() fills up the browser status code with the response');
  $t->like($b->get($example_site_url)->getResponseHeader('Content-Type'), '/text\/html/', 'get() populates the header array');
  $t->like(strtolower($b->getResponseText()), '/<head>/', 'get() populates the HTML of the response');
  
  /***********************/
  /* Simple HEAD request */
  /***********************/
  
  $t->diag('Simple HEAD request');
  $t->like($b->head($dump_headers_url)->getResponseHeader('Content-Type'), '/text\/html/', 'head() populates the header array');
  $t->is($b->getResponseText(), '', 'HEAD requests do not return a response body');
  
  /***********************/
  /* Simple POST request */
  /***********************/
  
  $t->diag('Simple POST request');
  $t->like($b->post($dump_headers_url)->getResponseText(), '/\[REQUEST_METHOD\] => POST/', 'post() performs a POST request');
  $t->like($b->post($dump_headers_url, array('post body'))->getResponseText(), '/post body/', 'post() sends body to server');
  
  /**********************/
  /* Simple PUT request */
  /**********************/
  
  $t->diag('Simple PUT request');
  $t->like($b->put($dump_headers_url)->getResponseText(), '/\[REQUEST_METHOD\] => PUT/', 'put() performs a PUT request');
  $t->like($b->put($dump_headers_url, array('PUT body'))->getResponseText(), '/PUT body/', 'put() sends body to server');
  
  /*************************/
  /* Simple DELETE request */
  /*************************/
  
  $t->diag('Simple DELETE request');
  $t->like($b->delete($dump_headers_url)->getResponseText(), '/\[REQUEST_METHOD\] => DELETE/', 'delete() performs a DELETE request');
  
  /*********************/
  /* Arbitrary request */
  /*********************/
  
  $t->diag('Arbitrary request');
  $t->like($b->call($dump_headers_url, 'MICHEL')->getResponseText(), '/\[REQUEST_METHOD\] => MICHEL/', 'call() supports any HTTP methods');
  
  /****************************/
  /* Response formats methods */
  /****************************/
  
  $t->diag('Response formats methods');
  $b = new sfWebBrowser(array(), $adapter);
  $b->get($example_site_url);
  $t->like($b->getResponseText(), '/<body .*>/', 'getResponseText() returns the response text');
  $t->unlike($b->getResponseBody(), '/<body>/', 'getResponseBody() returns the response body');
  $t->isa_ok($b->getResponseDom(), 'DOMDocument', 'getResponseDom() returns the response Dom');
  $t->isa_ok($b->getResponseDomCssSelector(), 'sfDomCssSelector', 'getResponseDomCssSelector() returns a CSS selector on the response Dom');
  $b->get('http://rss.cnn.com/rss/cnn_topstories.rss');
  $t->isa_ok($b->getResponseXml(), 'SimpleXMLElement', 'getResponseXml() returns the response as a SimpleXML Element');
  $b->get('http://www.w3.org/StyleSheets/home.css');
  try
  {
    $b->getResponseXml();
    $t->fail('Incorrect XML throws an exception');
  }
  catch (Exception $e)
  {
    $t->pass('Incorrect XML throws an exception');
  }
  
  try
  {
    /******************************/
    /* Absolute and relative URls */
    /******************************/
    
    $t->diag('Absolute and relative URls');
    $b = new sfWebBrowser(array(), $adapter);
    $t->like($b->get($askeet_params['url'])->getResponseText(), '/<h1>featured questions<\/h1>/', 'get() understands absolute urls');
    $t->like($b->get($askeet_params['url'].'/index/1')->getResponseText(), '/<h1>popular questions<\/h1>/', 'get() understands absolute urls');
    $t->like($b->get('/recent/1')->getResponseText(), '/<h1>recent questions<\/h1>/', 'get() understands relative urls with a trailing slash');
    $t->like($b->get('/')->get('recent/1')->getResponseText(), '/<h1>recent questions<\/h1>/', 'get() understands relative urls without a trailing slash');
    
    /***********************/
    /* Interaction methods */
    /***********************/
    
    $t->diag('Interaction methods');
    $b = new sfWebBrowser(array(), $adapter);
    $t->like($b->get($askeet_params['url'])->click('activities')->getResponseText(), '/tag "activities"/', 'click() clicks on a link and executes the related request');
    $t->like($b->get($askeet_params['url'])->click('/tag/activities')->getResponseText(), '/tag "activities"/', 'click() clicks on a link and executes the related request');
    $t->like($b->click('askeet')->getResponseText(), '/<h1>featured questions<\/h1>/', 'click() clicks on an image if it finds the argument in the alt');
    $t->like($b->click('search it', array('search' => 'foo'))->getResponseText(), '/<h1>questions matching "foo"<\/h1>/', 'click() clicks on a form input');
    $t->like($b->setField('search', 'bar')->click('search it')->getResponseText(), '/<h1>questions matching "bar"<\/h1>/', 'setField() fills a form input');
  }
  catch (Exception $e)
  {
    $t->fail(sprintf('%s : skipping askeet related tests', $e->getMessage()));  
  }
  
  /*******************************/
  /* GET request with parameters */
  /*******************************/
  
  $t->diag('GET request with parameters');
  $b = new sfWebBrowser(array(), $adapter);
  $test_params = array('foo' => 'bar', 'baz' => '1');
  $t->like($b->get($dump_headers_url, $test_params)->getResponseText(), '/\?foo=bar&baz=1/', 'get() can pass parameters with the second argument');
  $t->like($b->get($dump_headers_url.'?'.http_build_query($test_params))->getResponseText(), '/\?foo=bar&baz=1/', 'get() can pass parameters concatenated to the URI as a query string');
  $t->unlike($b->get($dump_headers_url.'?'.http_build_query($test_params))->getResponseText(), '/\?foo=bar&baz=1\&/', 'get() with an URL already containing request parameters doesn\'t add an extra &');
  $t->like($b->get($dump_headers_url.'?'.http_build_query($test_params), array('biz' => 'bil'))->getResponseText(), '/\?foo=bar&baz=1&biz=bil/', 'get() can pass parameters concatenated to the URI as a query string and other parameters as a second argument');

  $b = new sfWebBrowser(array(), $adapter);
  $b->get($dump_headers_url);
  
  /***************************/
  /* Default request headers */
  /***************************/
  
  $t->diag('Default request headers');
  $headers = array('Accept-language' => 'fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3');
  $b = new sfWebBrowser($headers, $adapter);
  $t->like(
    $b->get($dump_headers_url)->getResponseText(),
    "/fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3/",
    'sfWebBrowser constructor accepts default request headers as first parameter');
  $headers = array('Accept-language' => 'en-gb;q=0.8,en-us;q=0.5,en;q=0.3');
  $t->like(
    $b->get($dump_headers_url, array(), $headers)->getResponseText(),
    "/en-gb;q=0.8,en-us;q=0.5,en;q=0.3/",
    'Default request headers are overriden by request specific headers');

  /***************************/
  /* Request headers support */
  /***************************/
  
  $t->diag('Request headers support');
  $b = new sfWebBrowser(array(), $adapter);
  $headers = array(
    'Accept-language' => 'fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3',
    'Accept'          => 'text/xml');
  $t->like(
    $b->get($dump_headers_url, array(), $headers)->getResponseText(),
    "/fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3/",
    'get() can pass request headers with the third argument');
  $t->like(
    $b->post($dump_headers_url, array(), $headers)->getResponseText(),
    "/fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3/",
    'post() can pass request headers with the third argument');
  $msg = "get() can pass request headers not common that are defined uppercase in RFC 2616";
  try
  {
    $t->like(
      $b->get($dump_headers_url, array(), array('TE' => 'trailers, deflate;q=0.5'))->getResponseText(),
      "/\[TE\] => trailers, deflate;q=0.5/",
      $msg);
  }
  catch (Exception $e)
  {
    $t->fail($msg);
  }
  
  $msg = 'get() can pass request headers not common that are IE7 dependent: see http://www.w3.org/2006/http-header, now: ';
  $field = '';
  try
  {
    $headers = array('UA-CPU'=>'x86', 'UA-OS'=>'MacOS', 'UA-Color'=>'color16', 'UA-Pixels'=>'240x320');
    $resp = $b->get($dump_headers_url, array(), $headers)->getResponseText();
    foreach ($headers as $field => $value)
    {
      $t->like($resp, "/\[$field\] => $value/", $msg.$field);
    }
  }
  catch (Exception $e)
  {
    $t->fail($msg.field.' - header refused');
  }
  
  /*********************************/
  /* Encoded response body support */
  /*********************************/
  
  $t->diag('Encoded response body support');
  
  $headers = array('Accept-Encoding' => 'gzip');
  $t->like(
    $b->get($dump_headers_url, array(), $headers)->getResponseText(),
    "/gzip/",
    'getResponseText() can decode gzip encoded response body');
  $headers = array('Accept-Encoding' => 'deflate');
  $t->like(
    $b->get($dump_headers_url, array(), $headers)->getResponseText(),
    "/deflate/",
    'getResponseText() can decode deflate encoded response body');
  
  $encodings = array();
  if (function_exists('gzuncompress'))
  {
    $encodings[] = 'deflate';
  }
  if (function_exists('gzinflate'))
  {
    $encodings[] = 'gzip';
  }
  $target_headers = implode(',', $encodings);
  $t->like(
    $b->get($dump_headers_url, array(), $headers)->getResponseText(),
    "/$target_headers/", 
    'sfWebBrowser autosets accept-encoding headers depending on php capabilities');

  $encodings = array();
  if (function_exists('gzinflate'))
  {
    $encodings[] = 'gzip';
  }
  if (function_exists('gzuncompress'))
  {
    $encodings[] = 'deflate';
  }
  $headers = array('accept-encoding' => 'bzip2');
  array_unshift($encodings, 'bzip2');
  $target_headers = implode(',', $encodings);
  $t->like(
    $b->get($dump_headers_url, array(), $headers)->getResponseText(),
    "/$target_headers/",
    'it is possible to set supplementary encodings');
  
  /*******************/
  /* History methods */
  /*******************/
  
  $t->diag('History methods');
  $b = new sfWebBrowser(array(), $adapter);
  $b->get($dump_headers_url);
  $b->get($dump_headers_url.'?foo=bar');
  $b->back();
  $t->unlike($b->getResponseText(), '/foo=bar/', 'back() executes again the previous request in the history');
  $b->forward();
  $t->like($b->getResponseText(), '/foo=bar/', 'forward() executes again the next request in the history');
  $b->reload();
  $t->like($b->getResponseText(), '/foo=bar/', 'reload() executes again the current request in the history');
  
  /********************/
  /* Error management */
  /********************/
  
  $t->diag('Error management');
  try
  {
    $b->get('http://nonexistent');
    $t->fail('an exception is thrown when an adapter error occurs');
  }
  catch (Exception $e)
  {
    $t->pass('an exception is thrown when an adapter error occurs');
  }
  
  $t->is($b->get($example_site_url . '/nonexistentpage.html')->responseIsError(), true, 'responseIsError() returns true when response is an error');
  $t->is($b->get($example_site_url)->responseIsError(), false, 'responseIsError() returns false when response is not an error');
  
  /*******************/
  /* Browser restart */
  /*******************/
  
  $t->diag('Browser restart');
  $b->restart();
  try
  {
    $b->reload();  
    $t->fail('restart() reinitializes the browser history');
  } 
  catch (Exception $e)
  {
    $t->pass('restart() reinitializes the browser history');  
  }
  $t->is($b->getResponseText(), '', 'restart() reinitializes the response');
  
  /*************/
  /* Redirects */
  /*************/
  
  $t->diag('Redirects');
  $b = new sfWebBrowser(array(), $adapter);
  $b->get('http://www.symfony-project.com/trac/wiki/sfUJSPlugin');
  $t->like($b->getResponseText(), '/learn more about the unobtrusive approach/', 'follows 302 redirect after a GET');
  
  $b = new myTestWebBrowser(array(), $adapter);
  $b->call($askeet_params['url'].'/index.php/login', 'POST', array('nickname' => $askeet_params['login'], 'password' => $askeet_params['password']));
  //$t->like($b->getResponseText(), '/url='.preg_quote($askeet_params['url'], '/').'\/index\.php/', 'does NOT follow a 302 redirect after a POST');
  $t->like($b->getResponseText(), '/featured questions/', 'follows 302 redirect after POST ****** DESPITE THE HTTP SPEC ******');
  $t->is($b->getRequestMethod(), 'GET', 'request method is changed to GET after POST for 302 redirect ***** DESPITE THE HTTP SPEC *****');
  $t->todo('request method is changed to GET after POST for 303 redirect');
  
  /***********/
  /* Cookies */
  /***********/
  
  $t->diag('Cookies');
  if ($adapter == 'sfCurlAdapter')
  {
    $b = new sfWebBrowser(array(), $adapter, array(
      'cookies'      => true,
      'cookies_file' => $cookies_file,
      'cookies_dir'  => $cookies_dir,
    ));
    $b->call($askeet_params['url'].'/login', 'POST', array(
      'nickname' => $askeet_params['login'],
      'password' => $askeet_params['password'],
    ));
    $t->like($b->getResponseBody(), '/'.$askeet_params['login'].' profile/', 'Cookies can be added to the request');
  
    rmdir($cookies_dir);
    rmdir(dirname(__FILE__).'/../data');
  }
  else
  {
    $t->todo('Cookies can be added to the request (sfCurlAdapter only for now)');
  }
  
  /****************/
  /* File Uploads */
  /****************/
  
  $t->diag('File uploads');
  if ($adapter == 'sfCurlAdapter')
  {
    $b->post($dump_headers_url, array(
      'test_file' => realpath(__FILE__),
    ));
    $t->like($b->getResponseText(), '/\[test_file\]/', 'The request can upload a file');
  }
  else
  {
    $t->todo('The request can upload a file (sfCurlAdapter only for now)');
  }

  /*****************/
  /* Soap requests */
  /*****************/
  
  $t->diag('Soap requests');
  $url = 'http://www.abundanttech.com/WebServices/Population/population.asmx';
  $headers = array(
    'Soapaction'      => 'http://www.abundanttech.com/WebServices/Population/getWorldPopulation',
    'Content-Type'    => 'text/xml'
  );
  $requestBody = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <getWorldPopulation xmlns="http://www.abundanttech.com/WebServices/Population" />
  </soap:Body>
</soap:Envelope>
EOT;
  $b = new sfWebBrowser(array(), $adapter);
  $b->post($url, $requestBody, $headers);
  $t->like($b->getResponseText(), '/<Country>World<\/Country>/', 'sfWebBrowser can make a low-level SOAP call without parameter');

  $url = 'http://www.abundanttech.com/WebServices/Population/population.asmx';
  $headers = array(
    'Soapaction'      => 'http://www.abundanttech.com/WebServices/Population/getPopulation',
    'Content-Type'    => 'text/xml'
  );
  $requestBody = <<<EOT
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:pop="http://www.abundanttech.com/WebServices/Population">
  <soapenv:Header/>
  <soapenv:Body>
    <pop:getPopulation>
      <pop:strCountry>Comoros</pop:strCountry>
    </pop:getPopulation>
  </soapenv:Body>
</soapenv:Envelope>
EOT;
  $b = new sfWebBrowser(array(), $adapter);
  $b->post($url, $requestBody, $headers);
  $t->like($b->getResponseText(), '/<Country>Comoros<\/Country>/', 'sfWebBrowser can make a low-level SOAP call with parameter');
  
  $t->diag('');
}
