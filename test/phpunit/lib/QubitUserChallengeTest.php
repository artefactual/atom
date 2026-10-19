<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class QubitUserChallengeTest extends TestCase
{
    protected $geoIpHelper;

    protected function setUp(): void
    {
        // Use a mock GeoIpHelper for all tests
        $this->geoIpHelper = $this->createMock(QubitGeoIpHelper::class);
    }

    public function testCheckHeadlessReturnsTrueWhenTestIsDisabled()
    {
        $config = [];
        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertTrue($userChallenge->checkHeadless('test_key'), 'Should return true if headless test is disabled.');
    }

    public function testCheckHeadlessReturnsTrueWhenCookieMatches()
    {
        $_COOKIE['headless_cookie'] = base64_encode('test_key:false');
        $config = [];
        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertTrue($userChallenge->checkHeadless('test_key'), 'Should return true if cookie matches and is not headless.');
    }

    public function testCheckHeadlessReturnsFalseWhenCookieDoesNotMatch()
    {
        $_COOKIE['headless_cookie'] = base64_encode('wrong_key:true');
        $config = ['test_headless' => true];
        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertFalse($userChallenge->checkHeadless('test_key'), 'Should return false if cookie does not match.');
    }

    public function testCheckHeadlessReturnsFalseWhenCookieIsMissing()
    {
        unset($_COOKIE['headless_cookie']);
        $config = ['test_headless' => true];
        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertFalse($userChallenge->checkHeadless('test_key'), 'Should return false if cookie is missing.');
    }

    public function testShouldBypassChallengeByCountry()
    {
        $config = [
            'country_exceptions' => [
                'canada' => ['country' => 'CA'],
            ],
        ];
        $this->geoIpHelper->method('getCountry')->willReturn('CA');
        $this->geoIpHelper->method('getAsn')->willReturn(12345);

        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertTrue($userChallenge->shouldBypassChallenge('1.2.3.4', 'UA'), 'Should bypass if country is in exceptions.');
    }

    /**
     * @dataProvider noCountryExceptionsProvider
     */
    public function testSkipsCountryLookupWithoutCountryExceptions(array $config)
    {
        $this->geoIpHelper->expects($this->never())->method('getCountry');
        $this->geoIpHelper->expects($this->once())->method('getAsn')->willReturn(12345);
        $config['asn_exceptions'] = [12345];

        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertTrue($userChallenge->shouldBypassChallenge('192.0.2.1', 'UA'));
    }

    public function noCountryExceptionsProvider()
    {
        return [
            'missing' => [[]],
            'empty' => [['country_exceptions' => []]],
        ];
    }

    /**
     * @dataProvider noAsnExceptionsProvider
     */
    public function testSkipsAsnLookupWithoutAsnExceptions(array $config)
    {
        $this->geoIpHelper->expects($this->never())->method('getAsn');
        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);

        $this->assertFalse($userChallenge->shouldBypassChallenge('192.0.2.1', 'UA'));
    }

    public function noAsnExceptionsProvider()
    {
        return [
            'missing' => [[]],
            'empty' => [['asn_exceptions' => [], 'asn_user_agent_exceptions' => []]],
            'country only' => [['country_exceptions' => [['country' => 'CA']]]],
        ];
    }

    public function testShouldBypassChallengeByAsnUserAgent()
    {
        $config = [
            'asn_user_agent_exceptions' => [
                'Test' => [
                    'asn' => 12345,
                    'user_agent' => '.*TestUA.*',
                ],
            ],
        ];
        $this->geoIpHelper->method('getCountry')->willReturn(null);
        $this->geoIpHelper->method('getAsn')->willReturn(12345);

        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertTrue($userChallenge->shouldBypassChallenge('1.2.3.4', 'TestUA'), 'Should bypass if ASN and UA match exception.');
    }

    public function testShouldBypassChallengeByNetworkUserAgent()
    {
        $config = [
            'network_user_agent_exceptions' => [
                'test' => [
                    'src_net' => '192.168.1.0/24',
                    'user_agent' => '.*TestUA.*',
                ],
            ],
        ];
        $config['country_exceptions'] = [['country' => 'CA']];
        $config['asn_exceptions'] = [12345];
        $this->geoIpHelper->expects($this->never())->method('getCountry');
        $this->geoIpHelper->expects($this->never())->method('getAsn');
        $this->geoIpHelper->method('ipInCidr')->willReturn(true);

        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertTrue($userChallenge->shouldBypassChallenge('192.168.1.5', 'TestUA'), 'Should bypass if network and UA match exception.');
    }

    public function testShouldBypassChallengeByAsnOnly()
    {
        $config = ['asn_exceptions' => [12345]];
        $this->geoIpHelper->method('getCountry')->willReturn(null);
        $this->geoIpHelper->method('getAsn')->willReturn(12345);

        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertTrue($userChallenge->shouldBypassChallenge('1.2.3.4', 'UA'), 'Should bypass if ASN is in exceptions.');
    }

    public function testShouldBypassChallengeByCidrOnly()
    {
        $config = [
            'cidr_exceptions' => ['192.168.1.0/24'],
            'country_exceptions' => [['country' => 'CA']],
            'asn_exceptions' => [12345],
        ];
        $this->geoIpHelper->expects($this->never())->method('getCountry');
        $this->geoIpHelper->expects($this->never())->method('getAsn');
        $this->geoIpHelper->method('ipInCidr')->willReturn(true);

        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertTrue($userChallenge->shouldBypassChallenge('192.168.1.5', 'UA'), 'Should bypass if IP is in CIDR exception.');
    }

    public function testShouldNotBypassChallengeIfNoExceptionsMatch()
    {
        $config = [];
        $this->geoIpHelper->method('getCountry')->willReturn('US');
        $this->geoIpHelper->method('getAsn')->willReturn(54321);
        $this->geoIpHelper->method('ipInCidr')->willReturn(false);

        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper);
        $this->assertFalse($userChallenge->shouldBypassChallenge('8.8.8.8', 'SomeUA'), 'Should not bypass if no exceptions match.');
    }

    /**
     * @dataProvider proxyGeoIpBypassProvider
     */
    public function testBypassWithProxyGeoIp(array $config, array $values, string $userAgent, bool $expected)
    {
        $geo = new QubitGeoIpHelper('', '', null, $values);
        $userChallenge = new QubitUserChallenge($config, $geo);

        $this->assertSame($expected, $userChallenge->shouldBypassChallenge('192.0.2.1', $userAgent));
    }

    public function proxyGeoIpBypassProvider()
    {
        $botConfig = ['asn_user_agent_exceptions' => [
            'bingbot' => ['asn' => 8075, 'user_agent' => '.*bingbot.*'],
        ]];

        return [
            'ASN only' => [['asn_exceptions' => [16276]], ['asn' => '16276'], 'UA', true],
            'ASN and matching UA' => [$botConfig, ['asn' => '8075'], 'bingbot', true],
            'ASN and wrong UA' => [$botConfig, ['asn' => '8075'], 'UA', false],
            'wrong ASN and matching UA' => [$botConfig, ['asn' => '15169'], 'bingbot', false],
            'country' => [['country_exceptions' => [['country' => 'CA']]], ['country' => 'ca'], 'UA', true],
            'missing ASN' => [$botConfig, [], 'bingbot', false],
            'invalid ASN' => [$botConfig, ['asn' => 'AS8075'], 'bingbot', false],
            'missing country' => [['country_exceptions' => [['country' => 'CA']]], [], 'UA', false],
            'CIDR without GeoIP' => [['cidr_exceptions' => ['192.0.2.1/32']], [], 'UA', true],
        ];
    }

    public function testLogInfoFallsBackToErrorLogIfNoLogger()
    {
        $config = [];
        $userChallenge = new QubitUserChallenge($config, $this->geoIpHelper, null);

        $reflection = new ReflectionClass($userChallenge);
        $method = $reflection->getMethod('logInfo');
        $method->setAccessible(true);

        // Should not throw even if logger is not set
        $this->assertNull($method->invoke($userChallenge), 'logInfo should not throw if logger is not set.');
    }

    /**
     * @dataProvider endpointExceptionProvider
     */
    public function testMatchesEndpointException(string $requestUri, array $prefixes, bool $expected)
    {
        $this->assertSame(
            $expected,
            QubitUserChallenge::matchesEndpointException($requestUri, $prefixes)
        );
    }

    public function endpointExceptionProvider()
    {
        return [
            'oai exact path with query string' => [
                'requestUri' => '/;oai?verb=ListRecords&metadataPrefix=oai_dc&from=2026-03-14T03:03:00Z',
                'prefixes' => ['/;oai'],
                'expected' => true,
            ],
            'api exact path with query string' => [
                'requestUri' => '/api?foo=bar',
                'prefixes' => ['/api'],
                'expected' => true,
            ],
            'api subpath' => [
                'requestUri' => '/api/search?query=oai',
                'prefixes' => ['/api'],
                'expected' => true,
            ],
            'qt sword plugin exact path with query string' => [
                'requestUri' => '/qtSwordPlugin?x=1',
                'prefixes' => ['/qtSwordPlugin'],
                'expected' => true,
            ],
            'qt sword plugin subpath' => [
                'requestUri' => '/qtSwordPlugin/status',
                'prefixes' => ['/qtSwordPlugin'],
                'expected' => true,
            ],
            'similar api path does not match' => [
                'requestUri' => '/apix?foo=bar',
                'prefixes' => ['/api'],
                'expected' => false,
            ],
            'similar plugin path does not match' => [
                'requestUri' => '/qtSwordPlugins',
                'prefixes' => ['/qtSwordPlugin'],
                'expected' => false,
            ],
        ];
    }
}
