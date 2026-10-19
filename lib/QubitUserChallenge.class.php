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

class QubitUserChallenge
{
    protected bool $testHeadless;
    protected string $cookieNameHeadless;
    protected array $config;
    protected QubitGeoIpHelper $geoIpHelper;
    protected ?LoggerInterface $logger;

    /**
     * @param array                $config             Challenge configuration array
     * @param QubitGeoIpHelper     $geoIpHelper        GeoIP helper for network checks
     * @param bool                 $testHeadless       Whether to perform headless test
     * @param string               $cookieNameHeadless Name of the headless-js cookie
     * @param null|LoggerInterface $logger             Optional PSR-3 logger
     */
    public function __construct(
        array $config,
        QubitGeoIpHelper $geoIpHelper,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->geoIpHelper = $geoIpHelper;
        $this->testHeadless = (bool) ($config['test_headless'] ?? false);
        $this->cookieNameHeadless = $config['cookiename_headless'] ?? 'atom_headless';
        $this->logger = $logger;
    }

    /**
     * Check to see if the browser reports if it is headless or not. Returns
     * true if is headless, otherwise false.
     *
     * @param string $key
     *
     * @return bool
     */
    public function checkHeadless(string $key): bool
    {
        // Check if test is activated.
        if (empty($this->testHeadless)) {
            return true;
        }

        if (isset($_COOKIE[$this->cookieNameHeadless])) {
            $parts = explode(':', base64_decode($_COOKIE[$this->cookieNameHeadless]));
            if (isset($parts[0], $parts[1]) && $parts[0] === $key && 'false' === $parts[1]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the current request should bypass the JS challenge.
     *
     * @param string $remoteIp
     * @param string $userAgent
     *
     * @return bool
     */
    public function shouldBypassChallenge(string $remoteIp, string $userAgent): bool
    {
        // IP-based exceptions do not require GeoIP lookups.
        if ($this->matchesCidrException($remoteIp)
            || $this->matchesNetworkUserAgentException($remoteIp, $userAgent)) {
            return true;
        }

        // Country code exception
        if (!empty($this->config['country_exceptions'])) {
            $country = $this->geoIpHelper->getCountry($remoteIp);
            if (null !== $country && $this->matchesCountryException($country)) {
                return true;
            }
        }

        // ASN + UA exception
        $asn = null;
        if (!empty($this->config['asn_exceptions']) || !empty($this->config['asn_user_agent_exceptions'])) {
            $asn = $this->geoIpHelper->getAsn($remoteIp);
        }
        if (null !== $asn && $this->matchesAsnUserAgentException($asn, $userAgent)) {
            return true;
        }

        // ASN-only exception
        if (null !== $asn && $this->matchesAsnException($asn)) {
            return true;
        }

        return false;
    }

    public static function matchesEndpointException(string $requestUri, array $prefixes): bool
    {
        $requestPath = parse_url($requestUri, PHP_URL_PATH);
        if (false === $requestPath || null === $requestPath || '' === $requestPath) {
            $requestPath = '/';
        }

        $patterns = array_map(function (string $path) {
            $escaped = preg_quote($path, '#');

            return '#^'.$escaped.'(/.*)?$#';
        }, $prefixes);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $requestPath)) {
                return true;
            }
        }

        return false;
    }

    public function setCookie(
        $name,
        $value = '',
        $expires = 0,
        $path = '',
        $domain = '',
        $secure = true,
        $httponly = true,
        $samesite = 'strict'
    ) {
        if (headers_sent()) {
            return;
        }

        $params = [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
        ];

        if ($samesite) {
            $params['samesite'] = $samesite;
        }

        setcookie($name, $value, $params);
    }

    /**
     * Log informational message.
     */
    protected function logInfo(string $message = ''): void
    {
        if ($this->logger) {
            $this->logger->info($message);
        } else {
            error_log($message);
        }
    }

    /**
     * @param mixed $clientAsn
     * @param mixed $userAgent
     */
    protected function matchesAsnUserAgentException(int $clientAsn, string $userAgent): bool
    {
        $customAsnExceptions = $this->config['asn_user_agent_exceptions'] ?? [];
        foreach ($customAsnExceptions as $name => $exception) {
            if (isset($exception['asn'], $exception['user_agent'])) {
                if ($clientAsn === $exception['asn']
                    && preg_match('/'.$exception['user_agent'].'/i', $userAgent)) {
                    // Exception matched; bypass the JS challenge.
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $remoteIp
     * @param mixed $userAgent
     */
    protected function matchesNetworkUserAgentException(string $remoteIp, string $userAgent): bool
    {
        $customExceptions = $this->config['network_user_agent_exceptions'] ?? [];
        foreach ($customExceptions as $name => $exception) {
            if (isset($exception['src_net'], $exception['user_agent'])) {
                if ($this->geoIpHelper->ipInCidr($remoteIp, $exception['src_net'])
                    && preg_match('/'.$exception['user_agent'].'/i', $userAgent)) {
                    // Exception matched; bypass the JS challenge.
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $clientAsn
     */
    protected function matchesAsnException($clientAsn): bool
    {
        $asnExceptions = $this->config['asn_exceptions'] ?? [];
        if (!empty($asnExceptions) && in_array($clientAsn, $asnExceptions)) {
            // Exception matched; bypass the JS challenge.
            return true;
        }

        return false;
    }

    /**
     * @param mixed $remoteIp
     */
    protected function matchesCidrException(string $remoteIp): bool
    {
        $cidrExceptions = $this->config['cidr_exceptions'] ?? [];
        foreach ($cidrExceptions as $cidr) {
            if ($this->geoIpHelper->ipInCidr($remoteIp, $cidr)) {
                // Exception matched; bypass the JS challenge.
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $country
     */
    protected function matchesCountryException(string $country): bool
    {
        $countryExceptions = $this->config['country_exceptions'] ?? [];
        if (!empty($countryExceptions)) {
            foreach ($countryExceptions as $name => $exception) {
                if (isset($exception['country']) && strtoupper($country) == strtoupper($exception['country'])) {
                    // Exception matched; bypass the JS challenge.
                    return true;
                }
            }
        }

        return false;
    }
}
