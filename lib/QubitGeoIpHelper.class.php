<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

use GeoIp2\Database\Reader;

class QubitGeoIpHelper
{
    protected $asnDbPath;
    protected $cityDbPath;

    public function __construct($asnDbPath = null, $cityDbPath = null)
    {
        $this->setAsnDbPath($asnDbPath ?: sfConfig::get('app_geoip_asn_db_path', ''));
        $this->setCityDbPath($cityDbPath ?: sfConfig::get('app_geoip_city_db_path', ''));
    }

    public function setAsnDbPath($path)
    {
        $this->asnDbPath = $path;
    }

    public function setCityDbPath($path)
    {
        $this->cityDbPath = $path;
    }

    /**
     * Check if the IP is private or reserved.
     *
     * Supports both IPv4 and IPv6.
     *
     * @param string $ip
     *
     * @return bool true if the IP is private/reserved, false otherwise
     */
    public function isPrivateIP($ip)
    {
        // Check IPv4 addresses.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);

            return ($long >= ip2long('10.0.0.0') && $long <= ip2long('10.255.255.255'))
                || ($long >= ip2long('172.16.0.0') && $long <= ip2long('172.31.255.255'))
                || ($long >= ip2long('192.168.0.0') && $long <= ip2long('192.168.255.255'))
                || ($long >= ip2long('127.0.0.0') && $long <= ip2long('127.255.255.255'));
        }
        // Check IPv6 addresses.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Loopback
            if ('::1' === $ip) {
                return true;
            }

            $ipBin = inet_pton($ip);
            if (false === $ipBin) {
                return false;
            }

            // Unique Local Addresses (ULA): fc00::/7
            // The first 7 bits of the address must be 1111 110 (0xfc) when masked with 0xfe.
            $firstByte = ord(substr($ipBin, 0, 1));
            if (($firstByte & 0xFE) === 0xFC) {
                return true;
            }

            // Link-local addresses: fe80::/10
            // Check that the first byte is 0xfe and the first two bits of the second byte are 10.
            if (0xFE === $firstByte) {
                $secondByte = ord(substr($ipBin, 1, 1));
                if (($secondByte & 0xC0) === 0x80) {
                    return true;
                }
            }

            return false;
        }

        // If IP is neither valid IPv4 nor IPv6, return false.
        return false;
    }

    /**
     * Check if an IP address is within a given CIDR block.
     *
     * Supports both IPv4 and IPv6.
     *
     * @param string $ip   the IP address to check
     * @param string $cidr The CIDR block, e.g. "192.168.1.0/24" or "2001:db8::/32".
     *
     * @return bool true if $ip is in the CIDR range, false otherwise
     */
    public function ipInCidr($ip, $cidr)
    {
        if (empty($ip) || empty($cidr)) {
            return null;
        }

        // Determine if CIDR is IPv4 or IPv6 based on presence of ":".
        if (false === strpos($cidr, ':')) {
            // IPv4 processing.
            list($subnet, $mask) = explode('/', $cidr);

            if (empty($subnet) || empty($mask)) {
                return null;
            }

            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - (int) $mask);
            $subnetLong &= $maskLong;

            return ($ipLong & $maskLong) === $subnetLong;
        }

        // IPv6 processing.
        list($subnet, $mask) = explode('/', $cidr);
        $mask = (int) $mask;
        $ipBin = inet_pton($ip);
        $subnetBin = inet_pton($subnet);

        if (false === $ipBin || false === $subnetBin) {
            return null;
        }

        // Calculate the number of full bytes and remaining bits.
        $fullBytes = floor($mask / 8);
        $remainingBits = $mask % 8;

        // Compare the full bytes.
        if (substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
            return false;
        }
        if (0 === $remainingBits) {
            return true;
        }

        // Compare the remaining bits.
        $ipByte = ord(substr($ipBin, $fullBytes, 1));
        $subnetByte = ord(substr($subnetBin, $fullBytes, 1));
        $maskByte = ~((1 << (8 - $remainingBits)) - 1) & 0xFF;

        return ($ipByte & $maskByte) === ($subnetByte & $maskByte);
    }

    /**
     * Returns the ASN for the given IP.
     *
     * @param string $ip
     *
     * @return null|int
     */
    public function getAsn($ip)
    {
        if ($this->isPrivateIP($ip)) {
            return null;
        }

        try {
            $reader = new Reader($this->asnDbPath);
            $record = $reader->asn($ip);

            return $record->autonomousSystemNumber;
        } catch (Exception $e) {
            sfContext::getInstance()->getLogger()->info(
                sprintf(
                    'GeoIP Error: can\'t find ASN. Ensure setting \'app_geoip_asn_db_path\' is set properly. %s', $e->getMessage()
                )
            );

            return null;
        }
    }

    /**
     * Returns the ISO country code for the given IP.
     *
     * @param string $ip
     *
     * @return null|string
     */
    public function getCountry($ip)
    {
        if ($this->isPrivateIP($ip)) {
            return null;
        }

        try {
            $reader = new Reader($this->cityDbPath);
            $record = $reader->city($ip);

            return $record->country->isoCode;
        } catch (Exception $e) {
            sfContext::getInstance()->getLogger()->info(
                sprintf(
                    'GeoIP Error: can\'t find Country. Ensure setting \'app_geoip_city_db_path\' is set properly. %s', $e->getMessage()
                )
            );

            return null;
        }
    }
}
