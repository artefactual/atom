<?php
/**
 * Master Unit Test Suite file for Net_Gearman
 * 
 * This top-level test suite file organizes 
 * all class test suite files, 
 * so that the full suite can be run 
 * by PhpUnit or via "pear run-tests -u". 
 *
 * PHP version 5
 *
 * @category   Net
 * @package    Net_Gearman
 * @subpackage Net_Gearman_AllTests
 * @author     Till Klampaeckel <till@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Net_Gearman
 * @since      0.2.4
 */


/**
 * Check PHP version... PhpUnit v3+ requires at least PHP v5.1.4
 */
if (version_compare(PHP_VERSION, "5.1.4") < 0) {
    // Cannnot run test suites
    echo 'Cannot run test suite via PhpUnit... requires at least PHP v5.1.4.' . PHP_EOL;
    echo 'Use "pear run-tests -p Net_Gearman" to run the PHPT tests directly.' . PHP_EOL;
    exit(1);
}

$path = ini_get('include_path');
ini_set('include_path', realpath('../') . PATH_SEPARATOR . $path);

/**
 * Derive the "main" method name
 * @internal PhpUnit would have to rename PHPUnit_MAIN_METHOD to PHPUNIT_MAIN_METHOD
 *           to make this usage meet the PEAR CS... we cannot rename it here.
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Net_Gearman_AllTests::main');
}


/*
 * Files needed by PhpUnit
 */
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Extensions/PhptTestSuite.php';

/**
 * directory where PHPT tests are located
 */
define('Net_Gearman_DIR_PHPT', dirname(__FILE__));

/**
 * Master Unit Test Suite class for Net_Gearman
 * 
 * This top-level test suite class organizes 
 * all class test suite files, 
 * so that the full suite can be run 
 * by PhpUnit or via "pear run-tests -up Net_Gearman". 
 *
 * @category   Net
 * @package    Net_Gearman
 * @subpackage Net_Gearman_AllTests
 * @author     Till Klampaeckel <till@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Net_Gearman
 * @since      0.2.4
 */
class Net_Gearman_AllTests
{

    /**
     * Launches the TextUI test runner
     *
     * @return void
     * @uses PHPUnit_TextUI_TestRunner
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }


    /**
     * Adds all class test suites into the master suite
     *
     * @return PHPUnit_Framework_TestSuite a master test suite
     *                                     containing all class test suites
     * @uses PHPUnit_Framework_TestSuite
     */ 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite(
            'Net_Gearman Full Suite of Unit Tests');

        /*
         * You must add each additional class-level test suite name here
         */
        $suite->addTestSuite('Net_Gearman_ConnectionTest');
        $suite->addTestSuite('Net_Gearman_TaskTest');

        /**
         * @desc add PHPT tests
         */
        $phpt = new PHPUnit_Extensions_PhptTestSuite(Net_Gearman_DIR_PHPT);
        $suite->addTestSuite($phpt);

        return $suite;
    }

    public static function autoload($className)
    {
        $file = str_replace('_', '/', $className) . '.php';
        return include $file;
    }
}

spl_autoload_register(array('Net_Gearman_AllTests', 'autoload'));

/**
 * Call the main method if this file is executed directly
 * @internal PhpUnit would have to rename PHPUnit_MAIN_METHOD to PHPUNIT_MAIN_METHOD
 *           to make this usage meet the PEAR CS... we cannot rename it here.
 */
if (PHPUnit_MAIN_METHOD == 'Net_Gearman_AllTests::main') {
    Net_Gearman_AllTests::main();
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
