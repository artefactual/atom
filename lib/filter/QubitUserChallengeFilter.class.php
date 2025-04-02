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

class QubitUserChallengeFilter extends sfFilter
{
    protected $request;
    protected $remoteIp;

    public function execute($filterChain)
    {
        $this->context = $this->getContext();
        $this->request = $this->context->getRequest();

        // Check if challenge is enabled. Bypass challenge if destination is challenge.
        if (false === sfConfig::get('app_user_challenge_activated', false)
            || ('admin' == $this->request->getParameter('module') && 'challenge' == $this->request->getParameter('action'))) {
            $filterChain->execute();

            return;
        }

        $this->remoteIp = $this->getRemoteAddress();
        if (!isset($this->remoteIp)) {
            $this->context->getLogger()->err('Unable to determine remote address.');
            $filterChain->execute();

            return;
        }

        $geoIpHelper = new QubitGeoIpHelper();
        $country = $geoIpHelper->getCountry($this->remoteIp);
        $clientAsn = $geoIpHelper->getAsn($this->remoteIp);
        $userAgent = $this->request->getHttpHeader('User-Agent');

        $result = $this->shouldBypassChallenge($country, $clientAsn, $geoIpHelper, $userAgent);
        if (true === $result) {
            $filterChain->execute();

            return;
        }

        // Convert days to seconds since epoch.
        $cookieLifespan = time() + 60 * 60 * 24 * sfConfig::get('app_user_challenge_cookie_days', 90);
        $cookieNameVisited = sfConfig::get('app_user_challenge_cookiename_visited', 'atom_visited');
        $cookieNameHeadless = sfConfig::get('app_user_challenge_cookiename_headless', 'atom_headless');
        $cookieNameHasJs = sfConfig::get('app_user_challenge_cookiename_js', 'atom_js');
        $salt = sfConfig::get('app_user_challenge_salt');
        $key = md5($this->remoteIp.':'.$salt);

        // If 'visited' cookie present then skip js challenge.
        if (true === $this->canFindCookie($cookieNameVisited, $key)) {
            // Expire the challenge cookies if they haven't already.
            $this->setCookie($cookieNameHeadless, '', time() - 3600, '/');
            $this->setCookie($cookieNameHasJs, '', time() - 3600, '/');
            $filterChain->execute();

            return;
        }

        $testHeadless = sfConfig::get('app_user_challenge_test_headless', false);
        $userChallenge = new QubitUserChallenge($testHeadless, $cookieNameHeadless);

        if (true === $this->canFindCookie($cookieNameHasJs, $key)
            && $userChallenge->checkHeadless($key)) {
            $this->setCookie($cookieNameVisited, $key, $cookieLifespan, '/');
            $filterChain->execute();

            return;
        }

        $homepageUri = $this->context->controller->genUrl('@homepage', true);
        $requestedUri = $this->request->getUri();

        if (!empty($requestedUri)) {
            if ($requestedUri === $homepageUri) {
                // If the requested URL is the base site URL, remove the attribute if it exists.
                $this->context->user->getAttributeHolder()->remove('atom-challenge-requested-uri');
            } else {
                // Otherwise, save the requested URI.
                $this->context->user->setAttribute('atom-challenge-requested-uri', $requestedUri);
            }
        }

        $userChallenge->challenge();

        $filterChain->execute();
    }

    /**
     * Check if this request matches any exception rules.
     * Returns true if a match is found, otherwise false.
     *
     * @param mixed $country
     * @param mixed $clientAsn
     * @param mixed $geoIpHelpers
     * @param mixed $geoIpHelper
     * @param mixed $userAgent
     */
    public function shouldBypassChallenge($country, $clientAsn, $geoIpHelper, $userAgent)
    {
        // Exceptions Check: Country code.
        if (null !== $country && $this->matchesCountryException($country)) {
            return true;
        }

        // Exceptions check: User-Agent + ASN pairs.
        if (null !== $clientAsn && $this->matchesAsnUserAgentException($clientAsn, $userAgent)) {
            return true;
        }

        // Exceptions check: User-Agent + src_net pairs.
        if ($this->matchesNetworkUserAgentException($this->remoteIp, $userAgent, $geoIpHelper)) {
            return true;
        }

        // Exceptions check: ASN exception list.
        if (null !== $clientAsn && $this->matchesAsnException($clientAsn)) {
            return true;
        }

        // Exceptions check: CIDR exception list.
        if ($this->matchesCidrException($this->remoteIp, $geoIpHelper)) {
            return true;
        }

        return false;
    }

    /**
     * Get IP address from HTTP request.
     */
    public function getRemoteAddress()
    {
        $pathInfo = $this->request->getPathInfoArray();

        return $pathInfo['REMOTE_ADDR'] ?? null;
    }

    /**
     * Return true if cookie with specified name and value is found, otherwise false.
     *
     * @param mixed $name
     * @param mixed $value
     */
    public function canFindCookie($name, $value)
    {
        return isset($_COOKIE[$name]) && $_COOKIE[$name] == $value;
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
     * @param mixed $userAgent
     * @param mixed $remoteIp
     * @param mixed $geoIpHelper
     */
    protected function matchesNetworkUserAgentException($remoteIp, $userAgent, $geoIpHelper)
    {
        $customExceptions = sfConfig::get('app_user_challenge_network_user_agent_exceptions', []);
        foreach ($customExceptions as $name => $exception) {
            if (isset($exception['src_net'], $exception['user_agent'])) {
                if ($geoIpHelper->ipInCidr($remoteIp, $exception['src_net'])
                    && preg_match('/'.$exception['user_agent'].'/', $userAgent)) {
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
    protected function matchesAsnException($clientAsn)
    {
        $asnExceptions = sfConfig::get('app_user_challenge_asn_exceptions', []);
        if (!empty($asnExceptions) && in_array($clientAsn, $asnExceptions)) {
            // Exception matched; bypass the JS challenge.
            return true;
        }

        return false;
    }

    /**
     * @param mixed $remoteIp
     * @param mixed $geoIpHelper
     */
    protected function matchesCidrException($remoteIp, $geoIpHelper)
    {
        $cidrExceptions = sfConfig::get('app_user_challenge_cidr_exceptions', []);
        foreach ($cidrExceptions as $cidr) {
            if ($geoIpHelper->ipInCidr($remoteIp, $cidr)) {
                // Exception matched; bypass the JS challenge.
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $country
     */
    protected function matchesCountryException($country)
    {
        $countryExceptions = sfConfig::get('app_user_challenge_country_exceptions', []);
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

    /**
     * @param mixed $clientAsn
     * @param mixed $userAgent
     */
    protected function matchesAsnUserAgentException($clientAsn, $userAgent)
    {
        $customAsnExceptions = sfConfig::get('app_user_challenge_asn_user_agent_exceptions', []);
        foreach ($customAsnExceptions as $name => $exception) {
            if (isset($exception['asn'], $exception['user_agent'])) {
                if ($clientAsn == $exception['asn']
                    && preg_match('/'.$exception['user_agent'].'/', $userAgent)) {
                    // Exception matched; bypass the JS challenge.
                    return true;
                }
            }
        }

        return false;
    }
}
