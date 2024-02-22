<?php

use Jumbojett\OpenIDConnectClient;

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

class arOidc
{
    protected static $oidcIsInitialized = false;
    protected static $validTokenNames = ['access-token', 'id-token', 'verified-claims', 'user-info'];
    protected static $validUserMatchingSources = ['oidc-email', 'oidc-username'];

    /**
     * getOidcInstance.
     *
     * @return object $oidc
     */
    public static function getOidcInstance()
    {
        // Return if already initialized.
        if (self::$oidcIsInitialized) {
            return;
        }

        $provider_url = sfConfig::get('app_oidc_provider_url', '');
        if (empty($provider_url)) {
            throw new Exception('Invalid OIDC provider URL. Please review the app_oidc_provider_url parameter in plugin app.yml.');
        }
        $client_id = sfConfig::get('app_oidc_client_id', '');
        if (empty($client_id)) {
            throw new Exception('Invalid OIDC client id. Please review the app_oidc_client_id parameter in plugin app.yml.');
        }
        $client_secret = sfConfig::get('app_oidc_client_secret', '');
        if (empty($client_secret)) {
            throw new Exception('Invalid OIDC client secret. Please review the app_oidc_client_secret parameter in plugin app.yml.');
        }

        $oidc = new OpenIDConnectClient($provider_url, $client_id, $client_secret);

        // Validate requested scopes.
        $scopesArray = sfConfig::get('app_oidc_scopes', []);
        $validScopes = self::validateScopes($scopesArray);
        // Add scopes only if the array is not empty
        if (!empty($validScopes)) {
            $oidc->addScope($validScopes);
        } else {
            throw new Exception('No valid scopes found in app_oidc_scopes.');
        }

        // Validate redirect URL.
        $redirectUrl = sfConfig::get('app_oidc_redirect_url', '');
        if (empty($redirectUrl)) {
            throw new Exception('Invalid OIDC redirect URL. Please review the app_oidc_provider_url parameter in plugin app.yml.');
        }
        $oidc->setRedirectURL($redirectUrl);

        // Validate the server SSL certificate according to configuration.
        $certPath = sfConfig::get('app_oidc_server_cert', false);
        if (0 === !strpos($certPath, '/')) {
            $certPath = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$certPath;
        }

        if (file_exists($certPath)) {
            $oidc->setCertPath($certPath);
        } elseif (false === $certPath) {
            // OIDC server SSL certificate disabled.
        } else {
            throw new Exception('Invalid SSL certificate settings. Please review the app_oidc_server_cert parameter in plugin app.yml.');
        }

        self::$oidcIsInitialized = true;

        return $oidc;
    }

    /**
     * validateScopes.
     *
     * @param mixed $scopesArray
     *
     * @return array $validScopes
     */
    public static function validateScopes($scopesArray = [])
    {
        if (empty($scopesArray)) {
            throw new Exception('Invalid OIDC scopes. The app_oidc_scopes array is empty in the plugin app.yml.');
        }

        $validScopes = [];

        foreach ($scopesArray as $scope) {
            $trimmedScope = trim($scope);

            // Validate the trimmed scope
            if (!empty($trimmedScope)) {
                $validScopes[] = $trimmedScope;
            } else {
                throw new Exception(sprintf('Invalid scope value found in app_oidc_scopes: %s', $trimmedScope));
            }
        }

        return $validScopes;
    }

    /**
     * Check if the roles_source token name is valid.
     *
     * @param mixed $tokenName
     */
    public static function validateRolesSource($tokenName)
    {
        if (in_array($tokenName, self::$validTokenNames)) {
            return true;
        }

        return false;
    }

    /**
     * Validate config field used to select oidc field for matching AtoM user record.
     *
     * @param string $matchingSource
     *
     * @return bool
     */
    public static function validateUserMatchingSource($matchingSource)
    {
        if (in_array($matchingSource, self::$validUserMatchingSources)) {
            return true;
        }

        return false;
    }
}
