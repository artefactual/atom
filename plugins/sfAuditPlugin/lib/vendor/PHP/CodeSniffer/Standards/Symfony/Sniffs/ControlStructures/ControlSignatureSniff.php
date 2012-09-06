<?php

/**
 * Symfony_Sniffs_ControlStructures_ControlSignatureSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Jack Bates <ms419@freezone.co.uk>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: ControlSignatureSniff.php 68 2007-09-21 22:46:08Z jablko $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 * @link      http://trac.symfony-project.com/trac/wiki/CodingStandards
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractPatternSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractPatternSniff not found');
}

/**
 * Symfony_Sniffs_ControlStructures_ControlSignatureSniff.
 *
 * Checks that control statements conform to their coding standards.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Jack Bates <ms419@freezone.co.uk>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 * @link      http://trac.symfony-project.com/trac/wiki/CodingStandards
 */
class Symfony_Sniffs_ControlStructures_ControlSignatureSniff extends PHP_CodeSniffer_Standards_AbstractPatternSniff
{
    /**
     * Returns the patterns that this test wishes to verify.
     *
     * @return array(string)
     */
    protected function getPatterns()
    {
        return array(
            'tryEOL...catch (...)EOL',
            'doEOL...while (...);EOL',
            'while (...)EOL',
            'for (...)EOL',
            'if (...)EOL',
            'foreach (...)EOL',
            'else if (...)EOL',
            'elseEOL');

    }
}
