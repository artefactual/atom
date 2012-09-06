<?php

/**
 * Symfony_Sniffs_Whitespace_ScopeIndentSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Jack Bates <ms419@freezone.co.uk>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: ScopeIndentSniff.php 1728 2008-12-22 18:28:06Z jablko $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 * @link      http://trac.symfony-project.com/trac/wiki/CodingStandards
 */

if (class_exists('Generic_Sniffs_WhiteSpace_ScopeIndentSniff', true) === false) {
    $error = 'Class Generic_Sniffs_WhiteSpace_ScopeIndentSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * Symfony_Sniffs_Whitespace_ScopeIndentSniff.
 *
 * Checks that control structures are structured correctly, and their content
 * is indented correctly.
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
class Symfony_Sniffs_Whitespace_ScopeIndentSniff extends Generic_Sniffs_WhiteSpace_ScopeIndentSniff
{
    /**
     * The number of spaces code should be indented.
     *
     * @var int
     */
    protected $indent = 2;
}
