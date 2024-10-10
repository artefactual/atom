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

class oidcUser extends myUser implements Zend_Acl_Role_Interface
{
    private ?OpenIDConnectClient $oidcClient = null;

    /**
     * Initialize.
     *
     * @param mixed $dispatcher
     * @param mixed $storage
     * @param mixed $options
     */
    public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = [])
    {
        $this->logger = sfContext::getInstance()->getLogger();

        parent::initialize($dispatcher, $storage, $options);

        $this->setOidcClient(arOidc::getOidcInstance());

        $this->setRedirectURL();
    }

    public function setRedirectUrl()
    {
        $redirectUrl = sfConfig::get('app_oidc_redirect_url', '');
        if (empty($redirectUrl)) {
            throw new Exception('Invalid OIDC redirect URL. Please review the app_oidc_redirect_url parameter in plugin app.yml.');
        }
        $this->oidcClient->setRedirectURL($redirectUrl);
    }

    /**
     * Setter for the OIDC client.
     */
    public function setOidcClient(OpenIDConnectClient $oidcClient)
    {
        if (null !== $oidcClient) {
            $this->oidcClient = $oidcClient;
        }
    }

    /**
     * Try to authenticate with IAM system using OIDC.
     *
     * @param null|mixed $username
     * @param null|mixed $password
     */
    public function authenticate($username = null, $password = null): bool
    {
        $authenticated = false;
        $user = null;
        $authenticateResult = false;
        $email = null;

        // Get provider ID from session storage as it may have been set elsewhere.
        $providerId = $this->getSessionProviderId();
        // Validate and set provider ID in session.
        if (null !== $providerId = $this->validateProviderId($providerId, true)) {
            // Set provider details in OIDC client.
            $result = $this->setOidcProviderDetails($providerId);
        }
        if (null === $providerId || !isset($result) || false === $result) {
            return $authenticated;
        }

        // Set provider server cert.
        $this->setServerCert($this->getProviderConfigValue('server_cert', false));
        $this->setOidcScopes($this->getProviderConfigValue('scopes', []));

        if (isset($_REQUEST['code'])) {
            $this->logger->info('OIDC request "code" is set.');
        }

        try {
            $authenticateResult = $this->oidcClient->authenticate();
            if ($authenticateResult) {
                $username = trim($this->oidcClient->requestUserInfo('preferred_username'));
                $email = trim($this->oidcClient->requestUserInfo('email'));

                // Save the idToken for use when calling logout().
                $idToken = $this->oidcClient->getIdToken();
                $this->setAttribute('oidc-token', $idToken);

                $expiryTime = $this->oidcClient->getVerifiedClaims('exp');
                $this->setAttribute('oidc-expiry', $expiryTime);

                if (true == $this->getProviderConfigValue('enable_refresh_token_use', false)) {
                    $this->setAttribute('oidc-refresh', $this->oidcClient->getRefreshToken());
                }
            }
        } catch (OpenIDConnectClientException $e) {
            $this->logger->err(sprintf('OIDC exception: %s', $e->__toString().PHP_EOL));

            return $authenticated;
        }

        if ($authenticateResult) {
            // Validate user source setting.
            $userMatchingSource = $this->getProviderConfigValue('user_matching_source', '');
            if (!arOidc::validateUserMatchingSource($userMatchingSource)) {
                $this->logger->err('OIDC user matching source is configured but is not set properly. Unable to match OIDC users to AtoM users.');
                $this->logout();

                return $authenticated;
            }

            // If oidc-email is set and email is populated from OIDC, use it to match.
            if (!empty($email) && 'oidc-email' == $userMatchingSource) {
                $criteria = new Criteria();
                $criteria->add(QubitUser::EMAIL, $email);
                $user = QubitUser::getOne($criteria);
            }

            // If oidc-username is set and username is populated from OIDC, use it to match.
            if (!empty($username) && 'oidc-username' == $userMatchingSource) {
                $criteria = new Criteria();
                $criteria->add(QubitUser::USERNAME, $username);
                $user = QubitUser::getOne($criteria);
            }

            $autoCreateUser = $this->getProviderConfigValue('auto_create_atom_user', true);
            if (!is_bool($autoCreateUser)) {
                $this->logger->err('OIDC auto_create_atom_user is configured but is not set properly - value should be of type bool. Unable to match OIDC users to AtoM users.');
                $this->logout();

                return $authenticated;
            }

            // If user does not exist and $autoCreateUser is true, then try to create a new user.
            if (null === $user && $autoCreateUser) {
                $user = new QubitUser();
                $user->username = $username;
                $user->email = $email;
                $user->save();
            }

            // If user is null and $autoCreateUser is true, then something failed.
            if (null === $user && $autoCreateUser) {
                $this->logger->err('OIDC authentication succeeded but unable to find or create user in AtoM.');
                $this->logout();

                return $authenticated;
            }

            // If user is null and $autoCreateUser is false, then user has not been previously created or matching failed.
            if (null === $user && !$autoCreateUser) {
                $this->logger->err('OIDC authentication succeeded but user not found and auto_create_atom_user is set to false.');
                $this->logout();

                return $authenticated;
            }

            // Parse OIDC group claims into group memberships. If enabled, we perform this
            // check each time a user authenticates so that changes made on the OIDC
            // server are applied in AtoM on the next login.
            $setGroupsFromClaims = $this->getProviderConfigValue('set_groups_from_attributes', false);
            if (!is_bool($setGroupsFromClaims)) {
                $this->logger->err('OIDC set_groups_from_attributes is configured but is not set properly - value should be of type bool. Unable to complete authentication.');
                $this->logout();

                return $authenticated;
            }
            if (true == $setGroupsFromClaims) {
                $rolesPath = $this->getProviderConfigValue('roles_path', []);
                $rolesSource = $this->getProviderConfigValue('roles_source', '');

                // Validate Settings.
                if (!arOidc::validateRolesSource($rolesSource) || empty($rolesPath)) {
                    $this->logger->err('OIDC group mapping is configured but roles_path and roles_source are not set properly.');
                }

                // Get OIDC claims if rolesSource and rolesPath set correctly.
                if (arOidc::validateRolesSource($rolesSource) && !empty($rolesPath)) {
                    $claims = $this->getTokenContents($rolesSource);
                }

                // Extract user groups at $rolesPath.
                $groups = $this->parseOidcRoleClaims($claims, $rolesPath);

                if (null === $groups) {
                    $this->logger->err('OIDC group mapping is configured but group claims not received from upstream.');
                }

                // Delete user's previous groups and re-set them from the OIDC roles.
                if (null !== $groups) {
                    $this->setGroupsFromOidcGroups($user, $groups);
                }
            }

            $authenticated = true;

            // Clear template cache.
            $cacheClear = new sfCacheClearTask(sfContext::getInstance()->getEventDispatcher(), new sfFormatter());
            $cacheClear->run([], ['type' => 'template']);

            // Refresh user so new groups and credentials are immediately available on signIn().
            $this->signIn(QubitUser::getById($user->id));
        }

        return $authenticated;
    }

    /**
     * Returns bool value indicating if this user is authenticated.
     */
    public function isAuthenticated(): bool
    {
        $authenticated = parent::isAuthenticated();

        if (false == $this->getProviderConfigValue('enable_refresh_token_use', false) || false === $authenticated) {
            return $authenticated;
        }

        // Refresh token is in use.
        $currentTime = time();
        $expiryTime = $this->getAttribute('oidc-expiry', null);
        $refreshToken = $this->getAttribute('oidc-refresh', null);

        if (empty($refreshToken)) {
            $this->logger->info('Refresh token unavailable - authenticating user');
            $this->unsetAttributes();

            return false;
        }

        // Check if token has expired.
        if (null !== $expiryTime && $currentTime >= $expiryTime) {
            try {
                $this->logger->info('ID token expired - using refresh token to extend session.');
                $providerId = $this->getSessionProviderId();
                // Set provider details in the OIDC client using provider id.
                if (true === $this->setOidcProviderDetails($providerId)) {
                    // Set provider server cert.
                    $this->setServerCert($this->getProviderConfigValue('server_cert', false));
                    $this->setOidcScopes($this->getProviderConfigValue('scopes', []));

                    $refreshResult = $this->oidcClient->refreshToken($refreshToken);
                }

                // Validate the new refresh token. If the refresh token is invalid, the user is logged out.
                if (!isset($refreshResult->refresh_token) || empty($refreshResult->refresh_token)) {
                    $this->logger->info('Invalid OIDC token received. Session no longer active.');
                    $this->unsetAttributes();

                    return false;
                }

                if (isset($refreshResult->refresh_expires_in) && is_numeric($refreshResult->refresh_expires_in)) {
                    $newExpiryTime = $currentTime + (int) $refreshResult->refresh_expires_in;
                    $this->logger->info(sprintf('$newExpiryTime: %s', $newExpiryTime));
                } else {
                    $this->logger->err('Error calculating new token expiry. Session no longer active.');
                    $this->unsetAttributes();

                    return false;
                }

                // Use the new access tokens going forward. Do not try to re-set oidc-token
                // here because we do not receive a new idToken on refresh.
                $this->setAttribute('oidc-expiry', $newExpiryTime);
                $this->setAttribute('oidc-refresh', $refreshResult->refresh_token);
            } catch (OpenIDConnectClientException $e) {
                $this->logger->err(sprintf('OIDC refresh exception: %s', $e->__toString().PHP_EOL));
            }
        }

        return $authenticated;
    }

    /**
     * Logout from AtoM and the OIDC server.
     */
    public function logout(): void
    {
        $idToken = $this->getAttribute('oidc-token', null);
        $this->unsetAttributes();
        $this->signOut();

        if (true == $this->getProviderConfigValue('send_oidc_logout', false) && !empty($idToken)) {
            $logoutRedirectUrl = sfConfig::get('app_oidc_logout_redirect_url', '');
            if (empty($logoutRedirectUrl)) {
                $logoutRedirectUrl = null;
                $this->logger->err('Setting "app_oidc_logout_redirect_url" invalid. Unable to redirect on sign out.');
            }

            // Set provider server cert.
            $this->setServerCert($this->getProviderConfigValue('server_cert', false));
            $this->setOidcScopes($this->getProviderConfigValue('scopes', []));

            // Get saved session provider id.
            $providerId = $this->getSessionProviderId();
            // Unset session provider Id.
            $this->setSessionProviderId();

            // Set provider details in the OIDC client using provider id.
            if (true === $this->setOidcProviderDetails($providerId)) {
                try {
                    // Dex does not yet implement end_session_endpoint with it's oidc connector
                    // so $this->oidcClient->signOut will fail.
                    // https://github.com/dexidp/dex/issues/1697
                    $this->oidcClient->signOut($idToken, $logoutRedirectUrl);
                } catch (Exception $e) {
                    $this->logger->err($e->__toString().PHP_EOL);
                }
            }
        }
    }

    // Get provider specific config vals from app.yml.
    public function getProviderConfigValue(string $configVariableName = '', $default = null)
    {
        // Get saved session provider id.
        $providerId = $this->getSessionProviderId();

        // Get OIDC provider list. If none are configured this is an error.
        $providers = sfConfig::get('app_oidc_providers', []);
        if (empty($providers)) {
            $this->logger->err('OIDC providers not found in app.yml - check plugin configuration. Unable to authenticate using OIDC.');

            return $default;
        }

        // Get provider from list.
        if (!empty($providerId)
            && isset($providers[$providerId])
        ) {
            $provider = $providers[$providerId];
        }

        // Get config var from $provider array.
        if (isset($provider[$configVariableName])
        ) {
            return $provider[$configVariableName];
        }

        return $default;
    }

    // Parse the query params from a URL. If a param matches the provider ID selector
    // then return the value.
    public function parseProviderIdFromURL(string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $providerQueryParamName = sfConfig::get('app_oidc_provider_query_param_name', '');
        if (empty($providerQueryParamName)) {
            return null;
        }

        $urlParts = parse_url(strip_tags($url));
        parse_str($urlParts['query'], $queryParts);

        // Test if valid query param selector name.
        if (isset($queryParts[$providerQueryParamName])) {
            $providerId = $queryParts[$providerQueryParamName];
        }

        // A provider ID was specified.
        if (isset($providerId)) {
            return $providerId;
        }

        return null;
    }

    // Get provider ID from session storage.
    public function getSessionProviderId(): string
    {
        return $this->getAttribute('oidc-session-provider-id', '');
    }

    // Set provider in session storage.
    public function setSessionProviderId(string $providerId = ''): void
    {
        $this->setAttribute('oidc-session-provider-id', $providerId);
    }

    // Determine provider ID, test if valid and if so, save in session storage.
    public function validateProviderId(string $providerId = '', bool $setSessionProviderId = false): ?string
    {
        // If not available get primary provider.
        if (empty($providerId)) {
            $providerId = sfConfig::get('app_oidc_primary_provider_name', 'primary');
        }

        // Get OIDC provider list. If none are configured this is an error.
        $providers = sfConfig::get('app_oidc_providers', []);
        if (empty($providers)) {
            $this->logger->err('OIDC providers not found in app.yml - check plugin configuration. Unable to authenticate using OIDC.');

            return null;
        }

        // Test if provider ID is valid.
        if (!empty($providerId)
            && isset($providers[$providerId])
        ) {
            // Save provider ID in session storage.
            if (true === $setSessionProviderId) {
                $this->setSessionProviderId($providerId);
            }

            return $providerId;
        }

        // Provider ID specified does not match any configured providers.
        $this->logger->err('OIDC provider matching unsuccessful - check plugin configuration. Unable to authenticate using OIDC.');

        return null;
    }

    // Look up provider details from provider ID and set values in the OIDC client object.
    protected function setOidcProviderDetails(string $providerId = ''): bool
    {
        if (empty($providerId)) {
            $this->logger->err('OIDC providers is empty - ensure setSessionProviderId() is called before calling setOidcProviderDetails(). Unable to authenticate using OIDC.');

            return false;
        }

        // Get configured providers.
        $providers = sfConfig::get('app_oidc_providers', []);
        if (empty($providers)) {
            $this->logger->err('OIDC providers not found in app.yml - check plugin configuration. Unable to authenticate using OIDC.');

            return false;
        }

        // Get provider from list.
        if (!empty($providerId)
            && isset($providers[$providerId])
        ) {
            $provider = $providers[$providerId];
        }

        // Set provider details in OIDC Client.
        if (isset($provider['url'], $provider['client_id'], $provider['client_secret'])
        ) {
            $this->oidcClient->setProviderUrl($provider['url']);
            $this->oidcClient->setClientID($provider['client_id']);
            $this->oidcClient->setClientSecret($provider['client_secret']);
            $this->oidcClient->setIssuer($provider['url']);

            return true;
        }

        $this->logger->err('OIDC provider matching unsuccessful - check plugin provider configuration. Unable to authenticate using OIDC.');

        return false;
    }

    /**
     * getTokenContents() maps the location of the role info as set in app.yml
     * to a function that extracts these claims details.
     *
     * @param mixed $tokenType
     *
     * @return null|mixed
     */
    protected function getTokenContents($tokenType)
    {
        $tokenContentsFunctions = [
            'access-token' => 'getAccessTokenPayload',
            'id-token' => 'getIdTokenPayload',
            'verified-claims' => 'getVerifiedClaims',
            'user-info' => 'requestUserInfo',
        ];

        if (array_key_exists($tokenType, $tokenContentsFunctions)) {
            $functionName = $tokenContentsFunctions[$tokenType];

            return $this->oidcClient->{$functionName}();
        }

        // Return null if token type is not recognized.
        return null;
    }

    /**
     * Set provider server cert.
     *
     * @param mixed $certPath
     */
    protected function setServerCert($certPath = false)
    {
        // Validate the server SSL certificate according to configuration.
        if (0 === !strpos($certPath, '/')) {
            $certPath = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$certPath;
        }

        if (file_exists($certPath)) {
            $this->oidcClient->setCertPath($certPath);
        } elseif (false === $certPath) {
            // OIDC server SSL certificate disabled.
        } else {
            throw new Exception('Invalid OIDC SSL certificate settings. Please review the app_oidc_server_cert parameter in plugin app.yml.');
        }
    }

    /**
     * Set provider OIDC scopes from config.
     */
    protected function setOidcScopes(array $scopes = []): void
    {
        // Validate requested scopes.
        $validScopes = arOidc::validateScopes($scopes);
        // Add scopes only if the array is not empty
        if (!empty($validScopes)) {
            $this->oidcClient->addScope($validScopes);
        } else {
            throw new Exception('No valid OIDC scopes found in app_oidc_scopes.');
        }
    }

    /**
     * Parse group claims for role info returned by OIDC server. Returns
     * array containing assigned roles. Claims are searched based on nodes
     * specified in $pathArray.
     *
     * @param object $claims
     * @param array  $pathArray
     *
     * @return array $roles
     */
    protected function parseOidcRoleClaims($claims, $pathArray): array
    {
        $currentElement = $claims;
        foreach ($pathArray as $key) {
            if (isset($currentElement->{$key})) {
                $currentElement = $currentElement->{$key};
            } else {
                // If we get here, path does not exist.
                return [];
            }
        }

        // If result is a string, convert into an array to simplify validation.
        if (!is_array($currentElement)) {
            $currentElement = [$currentElement];
        }

        return $currentElement;
    }

    /**
     * Set group membership based on user group claims returned by OIDC server.
     *
     * @param mixed $user
     * @param mixed $groups
     */
    protected function setGroupsFromOidcGroups($user, array $groups)
    {
        if (null === $groups) {
            $this->logger->err('OIDC group list used for setting AtoM group membership is null');

            return;
        }

        // If groups param is a string convert into an array to simplify validation.
        if (!is_array($groups)) {
            $groups = [$groups];
        }

        // Delete existing AclUserGroups for this user. This allows us to reset
        // group membership on each login so that users will only belong to groups
        // that are appropriately configured in app_oidc_user_groups.
        $criteria = new Criteria();
        $criteria->add(QubitAclUserGroup::USER_ID, $user->id);
        foreach (QubitAclUserGroup::get($criteria) as $item) {
            $item->delete();
        }

        // Add the user to AclUserGroups based on the presence of expected OIDC
        // group values as set in app_oidc_user_groups.
        $userGroups = $this->getProviderConfigValue('user_groups', []);
        foreach ($userGroups as $item) {
            if (null !== $group = QubitAclGroup::getById($item['group_id'])) {
                $expectedValue = $item['attribute_value'];
                if (in_array($expectedValue, $groups)) {
                    $userGroup = new QubitAclUserGroup();
                    $userGroup->userId = $user->id;
                    $userGroup->groupId = $group->id;
                    $userGroup->save();
                }
            }
        }
    }

    /**
     * Clear out session vars holding auth info.
     */
    private function unsetAttributes(): void
    {
        $this->setAttribute('oidc-token', '');
        $this->setAttribute('oidc-expiry', '');
        $this->setAttribute('oidc-refresh', '');
    }
}
