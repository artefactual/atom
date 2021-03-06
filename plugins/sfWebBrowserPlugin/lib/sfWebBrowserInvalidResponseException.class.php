<?php

/**
 * This exception is thrown when response sent to browser is not in a valid format.
 *
 * @author     Tristan Rivoallan <tristan@rivoallan.net>
 */
class sfWebBrowserInvalidResponseException extends sfException
{
    /**
     * Class constructor.
     *
     * @param string The error message
     * @param int    The error code
     * @param null|mixed $message
     * @param mixed      $code
     */
    public function __construct($message = null, $code = 0)
    {
        if (method_exists($this, 'setName')) {
            $this->setName('sfWebBrowserInvalidResponseException');
        }
        parent::__construct($message, $code);
    }
}
