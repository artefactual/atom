<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class QubitLimitIpTest extends TestCase
{
    protected $contextMock;
    protected $filter;

    protected function setUp(): void
    {
        $configurationClass = 'qubitConfiguration';
        if (!class_exists($configurationClass)) {
            $this->markTestSkipped("The application configuration class '{$configurationClass}' does not exist.");
        }

        // Create an instance of the application configuration
        $configuration = new $configurationClass('test', false);

        // Create a sfContext instance
        $this->contextMock = sfContext::createInstance($configuration);

        // Create the filter instance and pass the sfContext
        $this->filter = new QubitLimitIpFilter($this->contextMock);
    }

    public function testBypassLimitInDebugMode()
    {
        $debug = true;
        $limit = explode(';', '192.168.0.1');
        $firstCall = true;
        $module = 'settings';
        $action = 'global';

        // Mock sfConfig to enable limit ip setting
        sfConfig::set('app_limit_admin_ip', $limit);

        // Call the method
        $actual = $this->filter->shouldBypassLimit($debug, $limit, $firstCall, $module, $action);

        // Assert that the limit is bypassed
        $this->assertTrue($actual);
    }

    public function testBypassLimitEmptyLimit()
    {
        $debug = false;
        $limit = explode(';', '');
        $firstCall = true;
        $module = 'settings';
        $action = 'global';

        // Mock sfConfig to enable limit ip setting
        sfConfig::set('app_limit_admin_ip', $limit);

        // Call the method
        $actual = $this->filter->shouldBypassLimit($debug, $limit, $firstCall, $module, $action);

        // Assert that the limit is bypassed
        $this->assertTrue($actual);
    }

    public function testBypassLimitNotFirstCall()
    {
        $debug = false;
        $limit = explode(';', '192.168.0.1');
        $firstCall = false;
        $module = 'settings';
        $action = 'global';

        // Mock sfConfig to enable limit ip setting
        sfConfig::set('app_limit_admin_ip', $limit);

        // Call the method
        $actual = $this->filter->shouldBypassLimit($debug, $limit, $firstCall, $module, $action);

        // Assert that the limit is bypassed
        $this->assertTrue($actual);
    }

    public function testBypassLimitUserLogout()
    {
        $debug = false;
        $limit = explode(';', '192.168.0.1');
        $firstCall = true;
        $module = 'user';
        $action = 'logout';

        // Mock sfConfig to enable limit ip setting
        sfConfig::set('app_limit_admin_ip', $limit);

        // Call the method
        $actual = $this->filter->shouldBypassLimit($debug, $limit, $firstCall, $module, $action);

        // Assert that the limit is bypassed
        $this->assertTrue($actual);
    }

    public function testBypassLimitOidcLogout()
    {
        $debug = false;
        $limit = explode(';', '192.168.0.1');
        $firstCall = true;
        $module = 'oidc';
        $action = 'logout';

        // Mock sfConfig to enable limit ip setting
        sfConfig::set('app_limit_admin_ip', $limit);

        // Call the method
        $actual = $this->filter->shouldBypassLimit($debug, $limit, $firstCall, $module, $action);

        // Assert that the limit is bypassed
        $this->assertTrue($actual);
    }

    public function testBypassLimitCasLogout()
    {
        $debug = false;
        $limit = explode(';', '192.168.0.1');
        $firstCall = true;
        $module = 'cas';
        $action = 'logout';

        // Mock sfConfig to enable limit ip setting
        sfConfig::set('app_limit_admin_ip', $limit);

        // Call the method
        $actual = $this->filter->shouldBypassLimit($debug, $limit, $firstCall, $module, $action);

        // Assert that the limit is bypassed
        $this->assertTrue($actual);
    }

    /**
     * @dataProvider ipProvider
     */
    public function testIsAllowedWhenIpMatches(string $limit)
    {
        // Reset mock environment for foreach loop
        $this->setUp();
        $address = '192.168.0.1';

        // Mock sfConfig to enable limit ip setting
        sfConfig::set('app_limit_admin_ip', $limit);
        $this->filter->setLimit();

        // Call the method
        $actual = $this->filter->isAllowed($address);

        // Assert that the limit is bypassed
        $this->assertTrue($actual);
    }

    public function ipProvider()
    {
        return [
            ['192.168.0.1'],
            ['192.168.0.1;192.168.0.255'],
            ['192.168.0.1-192.168.0.255'],
        ];
    }

    /**
     * @dataProvider ipProvider
     */
    public function testIsAllowedWhenIpNotAllowed(string $limit)
    {
        // Reset mock environment for foreach loop
        $this->setUp();
        $address = '172.168.0.1';

        // Mock sfConfig to enable limit ip setting
        sfConfig::set('app_limit_admin_ip', $limit);
        $this->filter->setLimit();

        // Call the method
        $actual = $this->filter->isAllowed($address);

        // Assert that the limit is bypassed
        $this->assertFalse($actual);
    }
}
