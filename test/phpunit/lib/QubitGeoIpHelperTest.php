<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class QubitGeoIpHelperTest extends TestCase
{
    protected $geoIpHelper;

    protected function setUp(): void
    {
        $this->geoIpHelper = new QubitGeoIpHelper();
    }

    public function testSetAsnDbPath()
    {
        $this->geoIpHelper->setAsnDbPath('/new/asn/db/path');
        $reflection = new ReflectionClass($this->geoIpHelper);
        $property = $reflection->getProperty('asnDbPath');
        $property->setAccessible(true);

        $this->assertEquals('/new/asn/db/path', $property->getValue($this->geoIpHelper), 'ASN DB path should be updated.');
    }

    public function testSetCityDbPath()
    {
        $this->geoIpHelper->setCityDbPath('/new/city/db/path');
        $reflection = new ReflectionClass($this->geoIpHelper);
        $property = $reflection->getProperty('cityDbPath');
        $property->setAccessible(true);

        $this->assertEquals('/new/city/db/path', $property->getValue($this->geoIpHelper), 'City DB path should be updated.');
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

    public function testIsPrivateIPWithInvalidIPv4()
    {
        $this->assertFalse($this->geoIpHelper->isPrivateIP('999.999.999.999'), 'Invalid IPv4 should return false.');
        $this->assertFalse($this->geoIpHelper->isPrivateIP(''), 'Empty IPv4 should return false.');
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
        $this->geoIpHelper->setCityDbPath('/new/city/db/path');
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
        $this->geoIpHelper->setAsnDbPath('/new/asn/db/path');
        $this->assertNull($this->geoIpHelper->getAsn('invalid-ip'), 'Invalid IP should return null.');
        $this->assertNull($this->geoIpHelper->getAsn(''), 'Empty IP should return null.');
    }
}
