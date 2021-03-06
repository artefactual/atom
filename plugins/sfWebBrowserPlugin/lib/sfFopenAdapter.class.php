<?php

/*
 * This file is part of the sfWebBrowserPlugin package.
 * (c) 2004-2006 Francois Zaninotto <francois.zaninotto@symfony-project.com>
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com> for the click-related functions
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebBrowser provides a basic HTTP client.
 *
 * @author     Francois Zaninotto <francois.zaninotto@symfony-project.com>
 *
 * @version    0.9
 */
class sfFopenAdapter
{
    protected $options = [];
    protected $adapterErrorMessage;
    protected $browser;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    /**
     * Submits a request.
     *
     * @param string  The request uri
     * @param string  The request method
     * @param array   The request parameters (associative array)
     * @param array   The request headers (associative array)
     * @param bool To specify is the request changes the browser history
     * @param mixed $browser
     * @param mixed $uri
     * @param mixed $method
     * @param mixed $parameters
     * @param mixed $headers
     *
     * @return sfWebBrowser The current browser object
     */
    public function call($browser, $uri, $method = 'GET', $parameters = [], $headers = [])
    {
        $m_headers = array_merge(['Content-Type' => 'application/x-www-form-urlencoded'], $browser->getDefaultRequestHeaders(), $browser->initializeRequestHeaders($headers));
        $request_headers = $browser->prepareHeaders($m_headers);

        // Read the response from the server
        // FIXME: use sockets to avoid depending on allow_url_fopen
        $context = stream_context_create(['http' => array_merge(
            $this->options,
            ['method' => $method],
            ['content' => is_array($parameters) ? http_build_query($parameters) : $parameters],
            ['header' => $request_headers]
        )]);

        // Custom error handler
        // -- browser instance must be accessible from handleRuntimeError()
        $this->browser = $browser;
        set_error_handler([$this, 'handleRuntimeError'], E_WARNING);
        if ($handle = fopen($uri, 'r', false, $context)) {
            $response_headers = stream_get_meta_data($handle);
            $browser->setResponseCode(array_shift($response_headers['wrapper_data']));
            $browser->setResponseHeaders($response_headers['wrapper_data']);
            $browser->setResponseText(stream_get_contents($handle));
            fclose($handle);
        }

        restore_error_handler();

        if (true == $this->adapterErrorMessage) {
            $msg = $this->adapterErrorMessage;
            $this->adapterErrorMessage = null;

            throw new Exception($msg);
        }

        return $browser;
    }

    /**
     * Handles PHP runtime error.
     *
     * This handler is used to catch any warnigns sent by fopen($url) and reformat them to something
     * usable.
     *
     * @see  http://php.net/set_error_handler
     *
     * @param mixed      $errno
     * @param mixed      $errstr
     * @param null|mixed $errfile
     * @param null|mixed $errline
     * @param mixed      $errcontext
     */
    public function handleRuntimeError($errno, $errstr, $errfile = null, $errline = null, $errcontext = [])
    {
        $error_types = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parsing Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Runtime Notice',
            E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        ];

        $msg = sprintf(
            '%s : "%s" occured in %s on line %d',
            $error_types[$errno],
            $errstr,
            $errfile,
            $errline
        );

        $matches = [];
        if (preg_match('/HTTP\/\d\.\d (\d{3}) (.*)$/', $errstr, $matches)) {
            $this->browser->setResponseCode($matches[1]);
            $this->browser->setResponseMessage($matches[2]);
            $body = sprintf('The %s adapter cannot handle error responses body. Try using another adapter.', __CLASS__);
            $this->browser->setResponseText($body);
        } else {
            $this->adapterErrorMessage = $msg;
        }
    }
}
