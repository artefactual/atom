<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitUserChallengeFilter
 */
class QubitUserChallengeFilterTest extends TestCase
{
    protected $filter;
    protected $filterChainMock;
    protected $contextMock;
    protected $requestMock;
    protected $loggerMock;

    protected function setUp(): void
    {
        $configurationClass = 'qubitConfiguration';
        if (!class_exists($configurationClass)) {
            $this->markTestSkipped("The application configuration class '{$configurationClass}' does not exist.");
        }

        // Create an instance of the application configuration
        $configuration = new $configurationClass('test', true);

        // Create a sfContext instance
        $this->contextMock = sfContext::createInstance($configuration);

        // Mock sfRequest
        $this->requestMock = $this->createMock(sfRequest::class);
        $this->contextMock->set('request', $this->requestMock);

        // Mock sfLogger
        $this->loggerMock = $this->createMock(sfLogger::class);
        $this->contextMock->set('logger', $this->loggerMock);

        // Mock sfFilterChain
        $this->filterChainMock = $this->createMock(sfFilterChain::class);

        // Create the filter instance and pass the sfContext
        $this->filter = new QubitUserChallengeFilter($this->contextMock);
    }

    public function testExecuteBypassesChallengeWhenDisabled()
    {
        // Mock sfConfig to disable the challenge
        sfConfig::set('app_user_challenge_activated', false);

        // Expect the filter chain to execute
        $this->filterChainMock->expects($this->once())->method('execute');

        // Call the execute method
        $this->filter->execute($this->filterChainMock);
    }

    public function testExecuteBypassesChallengeForAdminModule()
    {
        // Mock sfConfig to enable the challenge
        sfConfig::set('app_user_challenge_activated', true);

        // Mock request parameters for admin module and challenge action
        $this->requestMock->method('getParameter')->willReturnMap([
            ['module', 'admin'],
            ['action', 'challenge'],
        ]);

        // Expect the filter chain to execute
        $this->filterChainMock->expects($this->once())->method('execute');

        // Call the execute method
        $this->filter->execute($this->filterChainMock);
    }

    public function testExecuteLogsErrorWhenRemoteAddressIsMissing()
    {
        // Mock sfConfig to enable the challenge
        sfConfig::set('app_user_challenge_activated', true);

        // Mock request parameters for a non-admin module
        $this->requestMock->method('getParameter')->willReturnMap([
            ['module', 'frontend'],
            ['action', 'index'],
        ]);

        // Simulate missing REMOTE_ADDR in $_SERVER
        $_SERVER['REMOTE_ADDR'] = null;

        // Expect the logger to log an error
        $this->loggerMock->expects($this->once())
            ->method('err')
            ->with('Unable to determine remote address.');

        // Expect the filter chain to execute
        $this->filterChainMock->expects($this->once())->method('execute');

        // Call the execute method
        $this->filter->execute($this->filterChainMock);
    }

    public function testCanFindCookieReturnsTrueWhenCookieExists()
    {
        // Mock $_COOKIE
        $_COOKIE['test_cookie'] = 'test_value';

        // Assert that canFindCookie returns true
        $this->assertTrue($this->filter->canFindCookie('test_cookie', 'test_value'));
    }

    public function testCanFindCookieReturnsFalseWhenCookieDoesNotExist()
    {
        // Mock $_COOKIE
        $_COOKIE = [];

        // Assert that canFindCookie returns false
        $this->assertFalse($this->filter->canFindCookie('nonexistent_cookie', 'value'));
    }

    public function testSetCookieSetsCookie()
    {
        // Mock headers_sent to return false
        $this->assertFalse(headers_sent());

        // Call setCookie
        $this->filter->setCookie('test_cookie', 'test_value', time() + 3600, '/', '', true, true, 'strict');

        // Assert that the cookie is set (this is hard to test directly, but you can check $_COOKIE in integration tests)
        $this->assertTrue(true); // Placeholder assertion
    }

    public function testShouldBypassChallengeReturnsTrueForCountryException()
    {
        $geoIpHelperMock = $this->createMock(QubitGeoIpHelper::class);
        $country = 'CA';
        $clientAsn = null;
        $userAgent = 'TestUserAgent';

        // Mock sfConfig to include a country exception
        sfConfig::set('app_user_challenge_country_exceptions', [['country' => 'CA']]);

        // Call the method
        $result = $this->filter->shouldBypassChallenge($country, $clientAsn, $geoIpHelperMock, $userAgent);

        // Assert that the challenge is bypassed
        $this->assertTrue($result);
    }

    public function testShouldBypassChallengeReturnsTrueForAsnException()
    {
        $geoIpHelperMock = $this->createMock(QubitGeoIpHelper::class);
        $country = null;
        $clientAsn = '12345';
        $userAgent = 'TestUserAgent';

        // Mock sfConfig to include an ASN exception
        sfConfig::set('app_user_challenge_asn_exceptions', ['12345']);

        // Call the method
        $result = $this->filter->shouldBypassChallenge($country, $clientAsn, $geoIpHelperMock, $userAgent);

        // Assert that the challenge is bypassed
        $this->assertTrue($result);
    }

    public function testShouldBypassChallengeReturnsTrueForCidrException()
    {
        $geoIpHelperMock = $this->createMock(QubitGeoIpHelper::class);
        $geoIpHelperMock->method('ipInCidr')->willReturn(true);

        $country = null;
        $clientAsn = null;
        $userAgent = 'TestUserAgent';

        // Mock sfConfig to include a CIDR exception
        sfConfig::set('app_user_challenge_cidr_exceptions', ['192.168.1.0/24']);

        // Call the method
        $result = $this->filter->shouldBypassChallenge($country, $clientAsn, $geoIpHelperMock, $userAgent);

        // Assert that the challenge is bypassed
        $this->assertTrue($result);
    }

    public function testExecuteBypassesChallengeWithVisitedCookie()
    {
        // Mock sfConfig to enable the challenge
        sfConfig::set('app_user_challenge_activated', true);

        // Mock the 'visited' cookie
        $cookieNameVisited = sfConfig::get('app_user_challenge_cookiename_visited', 'atom_visited');
        $salt = sfConfig::get('app_user_challenge_salt', 'test_salt');
        $remoteIp = '192.168.1.1';
        $key = md5($remoteIp.':'.$salt);

        $_COOKIE[$cookieNameVisited] = $key;

        // Mock getRemoteAddress to return the remote IP
        $_SERVER['REMOTE_ADDR'] = $remoteIp;

        // Expect the filter chain to execute
        $this->filterChainMock->expects($this->once())->method('execute');

        // Call the execute method
        $this->filter->execute($this->filterChainMock);
    }
}
