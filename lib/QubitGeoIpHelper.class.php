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

require_once __DIR__.'/../vendor/composer/autoload.php';

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Psr\Log\LoggerInterface;

class QubitGeoIpHelper
{
    protected string $asnDbPath;
    protected string $cityDbPath;
    protected ?LoggerInterface $logger;
    protected ?array $proxyGeoIp;

    /**
     * @param string               $asnDbPath  Path to ASN GeoIP database
     * @param string               $cityDbPath Path to City GeoIP database
     * @param null|LoggerInterface $logger     Optional PSR-3 logger
     * @param null|array           $proxyGeoIp Trusted proxy values; null uses local databases
     */
    public function __construct(string $asnDbPath, string $cityDbPath, ?LoggerInterface $logger = null, ?array $proxyGeoIp = null)
    {
        $this->asnDbPath = $asnDbPath;
        $this->cityDbPath = $cityDbPath;
        $this->logger = $logger;
        $this->proxyGeoIp = $proxyGeoIp;
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
    public function isPrivateIP(string $ip): bool
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP)
            && false === filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
    }

    /**
     * Check if an IP address is within a given CIDR block.
     *
     * Supports both IPv4 and IPv6.
     *
     * @param string $ip   the IP address to check
     * @param string $cidr The CIDR block, e.g. "192.168.1.0/24" or "2001:db8::/32".
     *
     * @return null|bool true if in range, false if not, null if invalid input
     */
    public function ipInCidr(string $ip, string $cidr): ?bool
    {
        if (empty($ip) || empty($cidr)
        || (false === strpos($cidr, ':') && false === strpos($cidr, '/'))) {
            return null;
        }

        // Determine if CIDR is IPv4 or IPv6 based on presence of ":".
        if (false === strpos($cidr, ':')) {
            // IPv4 processing.
            [$subnet, $mask] = explode('/', $cidr);

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
        [$subnet, $mask] = explode('/', $cidr);
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
    public function getAsn(string $ip): ?int
    {
        if (null !== $this->proxyGeoIp) {
            $asn = $this->proxyGeoIp['asn'] ?? '';
            if (!is_string($asn) || !ctype_digit($asn)) {
                return null;
            }

            $asn = filter_var($asn, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);

            return false === $asn ? null : $asn;
        }

        if ($this->isPrivateIP($ip)) {
            return null;
        }

        try {
            $reader = new Reader($this->asnDbPath);
            $record = $reader->asn($ip);

            return $record->autonomousSystemNumber;
        } catch (AddressNotFoundException $e) {
            $this->logger?->debug('GeoIP ASN lookup: '.$e->getMessage());

            return null;
        } catch (\Exception $e) {
            $this->logInfo('GeoIP Error: can\'t find ASN DB or lookup failed: '.$e->getMessage());

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
    public function getCountry(string $ip): ?string
    {
        if (null !== $this->proxyGeoIp) {
            $country = $this->proxyGeoIp['country'] ?? '';

            return is_string($country) && preg_match('/\A[a-z]{2}\z/i', $country) ? strtoupper($country) : null;
        }

        if ($this->isPrivateIP($ip)) {
            return null;
        }

        try {
            $reader = new Reader($this->cityDbPath);
            $record = $reader->city($ip);

            return $record->country->isoCode;
        } catch (AddressNotFoundException $e) {
            $this->logger?->debug('GeoIP country lookup: '.$e->getMessage());

            return null;
        } catch (\Exception $e) {
            $this->logInfo('GeoIP Error: can\'t find City DB or lookup failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Log informational messages.
     */
    protected function logInfo(string $message = ''): void
    {
        if ($this->logger) {
            $this->logger->info($message);
        } else {
            error_log($message);
        }
    }
}
