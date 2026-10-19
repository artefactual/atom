<?php

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class QubitGeoIpHelperTest extends TestCase
{
    protected $geoIpHelper;
    protected $asnDbPath = '/tmp/fake-asn.mmdb';
    protected $cityDbPath = '/tmp/fake-city.mmdb';

    protected function setUp(): void
    {
        // Use a mock logger to avoid real logging
        $mockLogger = $this->createMock(LoggerInterface::class);
        $this->geoIpHelper = new QubitGeoIpHelper($this->asnDbPath, $this->cityDbPath, $mockLogger);
    }

    public function testConstructorSetsProperties()
    {
        $reflection = new ReflectionClass($this->geoIpHelper);

        $asnDbPath = $reflection->getProperty('asnDbPath');
        $asnDbPath->setAccessible(true);
        $cityDbPath = $reflection->getProperty('cityDbPath');
        $cityDbPath->setAccessible(true);
        $logger = $reflection->getProperty('logger');
        $logger->setAccessible(true);

        $this->assertEquals($this->asnDbPath, $asnDbPath->getValue($this->geoIpHelper), 'ASN DB path should be set by constructor.');
        $this->assertEquals($this->cityDbPath, $cityDbPath->getValue($this->geoIpHelper), 'City DB path should be set by constructor.');
        $this->assertInstanceOf(LoggerInterface::class, $logger->getValue($this->geoIpHelper), 'Logger should be set by constructor.');
    }

    // IPv4 Tests
    public function testIsPrivateIPWithPrivateIPv4()
    {
        $this->assertTrue($this->geoIpHelper->isPrivateIP('10.0.0.1'), '10.0.0.1 should be private.');
        $this->assertTrue($this->geoIpHelper->isPrivateIP('172.16.0.1'), '172.16.0.1 should be private.');
        $this->assertTrue($this->geoIpHelper->isPrivateIP('192.168.1.1'), '192.168.1.1 should be private.');
        $this->assertTrue($this->geoIpHelper->isPrivateIP('127.0.0.1'), '127.0.0.1 should be private (loopback).');
    }

    public function testIsPrivateIPWithPublicIPv4()
    {
        $this->assertFalse($this->geoIpHelper->isPrivateIP('8.8.8.8'), '8.8.8.8 should not be private.');
        $this->assertFalse($this->geoIpHelper->isPrivateIP('1.1.1.1'), '1.1.1.1 should not be private.');
    }

    /**
     * @dataProvider privateIPv4BoundaryProvider
     */
    public function testIsPrivateIPWithIPv4Boundaries(string $ip, bool $expected)
    {
        $this->assertSame($expected, $this->geoIpHelper->isPrivateIP($ip));
    }

    public function privateIPv4BoundaryProvider()
    {
        return [
            ['9.255.255.255', false],
            ['10.0.0.0', true],
            ['10.255.255.255', true],
            ['11.0.0.0', false],
            ['172.15.255.255', false],
            ['172.16.0.0', true],
            ['172.31.255.255', true],
            ['172.32.0.0', false],
            ['192.167.255.255', false],
            ['192.168.0.0', true],
            ['192.168.255.255', true],
            ['192.169.0.0', false],
            ['126.255.255.255', false],
            ['127.0.0.0', true],
            ['127.255.255.255', true],
            ['128.0.0.0', false],
            ['0.0.0.0', true],
            ['255.255.255.255', true],
        ];
    }

    public function testIsPrivateIPWithInvalidIPv4()
    {
        $this->assertFalse($this->geoIpHelper->isPrivateIP('999.999.999.999'), 'Invalid IPv4 should return false.');
        $this->assertFalse($this->geoIpHelper->isPrivateIP(''), 'Empty IPv4 should return false.');
    }

    /**
     * @dataProvider reservedAddressProvider
     */
    public function testIsPrivateIPWithReservedAddresses(string $ip)
    {
        $this->assertTrue($this->geoIpHelper->isPrivateIP($ip));
    }

    public function reservedAddressProvider()
    {
        return [
            ['0.1.2.3'],
            ['169.254.0.1'],
            ['240.0.0.1'],
            ['::'],
            ['0:0:0:0:0:0:0:1'],
            ['fe80::1'],
        ];
    }

    // IPv6 Tests
    public function testIsPrivateIPWithPrivateIPv6()
    {
        $this->assertTrue($this->geoIpHelper->isPrivateIP('::1'), '::1 should be private (loopback).');
        $this->assertTrue($this->geoIpHelper->isPrivateIP('fc00::1'), 'fc00::1 should be private (ULA).');
        $this->assertTrue($this->geoIpHelper->isPrivateIP('fe80::1'), 'fe80::1 should be private (link-local).');
    }

    public function testIsPrivateIPWithPublicIPv6()
    {
        $this->assertFalse($this->geoIpHelper->isPrivateIP('2001:4860:4860::8888'), '2001:4860:4860::8888 should not be private.');
        $this->assertFalse($this->geoIpHelper->isPrivateIP('2606:4700:4700::1111'), '2606:4700:4700::1111 should not be private.');
    }

    public function testIsPrivateIPWithInvalidIPv6()
    {
        $this->assertFalse($this->geoIpHelper->isPrivateIP('invalid-ipv6'), 'Invalid IPv6 should return false.');
        $this->assertFalse($this->geoIpHelper->isPrivateIP(''), 'Empty IPv6 should return false.');
    }

    // ipInCidr tests
    public function testIpInCidrWithIPv4()
    {
        $this->assertTrue($this->geoIpHelper->ipInCidr('192.168.1.5', '192.168.1.0/24'), '192.168.1.5 should be in 192.168.1.0/24.');
        $this->assertFalse($this->geoIpHelper->ipInCidr('192.168.2.5', '192.168.1.0/24'), '192.168.2.5 should not be in 192.168.1.0/24.');
        $this->assertNull($this->geoIpHelper->ipInCidr('', '192.168.1.0/24'), 'Empty IP should return null.');
        $this->assertNull($this->geoIpHelper->ipInCidr('192.168.1.5', ''), 'Empty CIDR should return null.');
        $this->assertNull($this->geoIpHelper->ipInCidr('192.168.1.5', 'invalid-cidr'), 'Invalid CIDR should return null.');
    }

    public function testIpInCidrWithIPv6()
    {
        $this->assertTrue($this->geoIpHelper->ipInCidr('2001:db8::1', '2001:db8::/32'), '2001:db8::1 should be in 2001:db8::/32.');
        $this->assertFalse($this->geoIpHelper->ipInCidr('2001:db9::1', '2001:db8::/32'), '2001:db9::1 should not be in 2001:db8::/32.');
        $this->assertNull($this->geoIpHelper->ipInCidr('', '2001:db8::/32'), 'Empty IP should return null.');
        $this->assertNull($this->geoIpHelper->ipInCidr('2001:db8::1', ''), 'Empty CIDR should return null.');
        $this->assertNull($this->geoIpHelper->ipInCidr('2001:db8::1', 'invalid-cidr'), 'Invalid CIDR should return null.');
    }

    // getCountry and getAsn tests (mocking Reader)
    public function testGetCountryReturnsNullForPrivateIPv4()
    {
        $this->assertNull($this->geoIpHelper->getCountry('192.168.1.1'), 'Private IPv4 should return null.');
    }

    public function testGetCountryReturnsNullForPrivateIPv6()
    {
        $this->assertNull($this->geoIpHelper->getCountry('fc00::1'), 'Private IPv6 should return null.');
    }

    public function testGetCountryReturnsNullForInvalidIP()
    {
        $this->assertNull($this->geoIpHelper->getCountry('invalid-ip'), 'Invalid IP should return null.');
        $this->assertNull($this->geoIpHelper->getCountry(''), 'Empty IP should return null.');
    }

    public function testGetAsnReturnsNullForPrivateIPv4()
    {
        $this->assertNull($this->geoIpHelper->getAsn('192.168.1.1'), 'Private IPv4 should return null.');
    }

    public function testGetAsnReturnsNullForPrivateIPv6()
    {
        $this->assertNull($this->geoIpHelper->getAsn('fc00::1'), 'Private IPv6 should return null.');
    }

    public function testGetAsnReturnsNullForInvalidIP()
    {
        $this->assertNull($this->geoIpHelper->getAsn('invalid-ip'), 'Invalid IP should return null.');
        $this->assertNull($this->geoIpHelper->getAsn(''), 'Empty IP should return null.');
    }

    /**
     * @dataProvider missingAddressProvider
     */
    public function testMissingAddressLogsOnlyAtDebug(string $method, string $ip)
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('debug')->with($this->stringContains($ip));
        $logger->expects($this->never())->method('info');
        $helper = new QubitGeoIpHelper(
            __DIR__.'/../../fixtures/geoip/GeoLite2-ASN-Test.mmdb',
            __DIR__.'/../../fixtures/geoip/GeoIP2-City-Test.mmdb',
            $logger
        );

        $this->assertNull($helper->{$method}($ip));
    }

    /**
     * @dataProvider missingAddressProvider
     */
    public function testMissingAddressDoesNotWriteToErrorLogWithoutLogger(string $method, string $ip)
    {
        $logFile = tempnam(sys_get_temp_dir(), 'geoip-log-');
        $previousErrorLog = ini_set('error_log', $logFile);

        try {
            $helper = new QubitGeoIpHelper(
                __DIR__.'/../../fixtures/geoip/GeoLite2-ASN-Test.mmdb',
                __DIR__.'/../../fixtures/geoip/GeoIP2-City-Test.mmdb'
            );

            $this->assertNull($helper->{$method}($ip));
            $this->assertSame('', file_get_contents($logFile));
        } finally {
            ini_set('error_log', $previousErrorLog);
            unlink($logFile);
        }
    }

    public function missingAddressProvider()
    {
        return [
            'ASN IPv4' => ['getAsn', '192.0.2.1'],
            'ASN IPv6' => ['getAsn', '2001:db8::1'],
            'country IPv4' => ['getCountry', '192.0.2.1'],
            'country IPv6' => ['getCountry', '2001:db8::1'],
        ];
    }

    public function testDatabaseFailuresStillLogAtInfo()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))->method('info')->with($this->stringContains('GeoIP Error:'));
        $logger->expects($this->never())->method('debug');
        $helper = new QubitGeoIpHelper(__DIR__, __DIR__, $logger);

        $this->assertNull($helper->getAsn('192.0.2.1'));
        $this->assertNull($helper->getCountry('192.0.2.1'));
    }

    /**
     * @dataProvider proxyGeoIpProvider
     */
    public function testProxyGeoIpDoesNotUseLocalDatabases(array $values, ?int $expectedAsn, ?string $expectedCountry)
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('info');
        $logger->expects($this->never())->method('debug');
        // These paths cannot be opened as databases. Any local lookup would log.
        $helper = new QubitGeoIpHelper(__DIR__, __DIR__, $logger, $values);

        foreach (['192.0.2.1', '2001:db8::1'] as $ip) {
            $this->assertSame($expectedAsn, $helper->getAsn($ip));
            $this->assertSame($expectedCountry, $helper->getCountry($ip));
        }
    }

    public function proxyGeoIpProvider()
    {
        return [
            'valid' => [['asn' => '8075', 'country' => 'CA'], 8075, 'CA'],
            'lowercase country' => [['asn' => '15169', 'country' => 'ca'], 15169, 'CA'],
            'maximum ASN' => [['asn' => '4294967295'], 4294967295, null],
            'missing' => [[], null, null],
            'empty' => [['asn' => '', 'country' => ''], null, null],
            'null' => [['asn' => null, 'country' => null], null, null],
            'zero ASN' => [['asn' => '0'], null, null],
            'negative ASN' => [['asn' => '-1'], null, null],
            'overflow ASN' => [['asn' => '4294967296'], null, null],
            'prefixed ASN' => [['asn' => 'AS8075'], null, null],
            'fractional ASN' => [['asn' => '8075.5'], null, null],
            'multiple values' => [['asn' => '8075,15169', 'country' => 'CA,US'], null, null],
            'invalid types' => [['asn' => true, 'country' => []], null, null],
            'long country' => [['country' => 'CAN'], null, null],
            'numeric country' => [['country' => '12'], null, null],
            'newline in country' => [['country' => "CA\n"], null, null],
            'valid country, invalid ASN' => [['asn' => 'invalid', 'country' => 'CA'], null, 'CA'],
            'valid ASN, invalid country' => [['asn' => '8075', 'country' => 'invalid'], 8075, null],
        ];
    }

    // Additional: test logging fallback to error_log if no logger is set
    public function testLogInfoFallsBackToErrorLogIfNoLogger()
    {
        $geoIpHelper = new QubitGeoIpHelper($this->asnDbPath, $this->cityDbPath, null);

        // Use reflection to access protected logInfo
        $reflection = new ReflectionClass($geoIpHelper);
        $method = $reflection->getMethod('logInfo');
        $method->setAccessible(true);

        // Can't easily test error_log output, but this ensures no exception is thrown
        $this->assertNull($method->invoke($geoIpHelper), 'logInfo should not throw if logger is not set.');
    }
}
