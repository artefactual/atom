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

class oidcUser extends myUser implements Zend_Acl_Role_Interface
{
    private $oidcClient;

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
        if (null === $this->oidcClient) {
            $this->oidcClient = arOidc::getOidcInstance();
        }

        parent::initialize($dispatcher, $storage, $options);
    }

    /**
     * Try to authenticate with IAM system using OIDC.
     *
     * @param null|mixed $username
     * @param null|mixed $password
     *
     * @return bool
     */
    public function authenticate($username = null, $password = null)
    {
        $authenticated = false;
        $user = null;
        $authenticateResult = false;

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

                if (true == sfConfig::get('app_oidc_enable_refresh_token_use', false)) {
                    $this->setAttribute('oidc-refresh', $this->oidcClient->getRefreshToken());
                }
            }
        } catch (OpenIDConnectClientException $e) {
            $this->logger->err(sprintf('OIDC exception: %s', $e->__toString().PHP_EOL));

            return $authenticated;
        }

        if ($authenticateResult) {
            // Validate user source setting.
            $userMatchingSource = sfConfig::get('app_oidc_user_matching_source', '');
            if (!arOidc::validateUserMatchingSource($userMatchingSource)) {
                $this->logger->err('OIDC user matching source is configured but is not set properly. Unable to match OIDC users to AtoM users.');

                return $authenticated;
            }

            // If oidc-email is set and email is populated from OIDC, use it to match.
            if (!empty($email) && 'oidc-email' == $userMatchingSource) {
                $criteria = new Criteria();
                $criteria->add(QubitUser::EMAIL, $email);
                if (null === $user = QubitUser::getOne($criteria)) {
                    $user = new QubitUser();
                    $user->username = $username;
                    $user->email = $email;
                    $user->save();
                }
            }

            // If oidc-username is set and username is populated from OIDC, use it to match.
            if (!empty($username) && 'oidc-username' == $userMatchingSource) {
                $criteria = new Criteria();
                $criteria->add(QubitUser::USERNAME, $username);
                if (null === $user = QubitUser::getOne($criteria)) {
                    $user = new QubitUser();
                    $user->username = $username;
                    $user->email = $email;
                    $user->save();
                }
            }

            // If user does not exist, then something failed.
            if (null === $user) {
                $this->logger->err('OIDC authentication succeeded but unable to find or create user in AtoM.');

                return $authenticated;
            }

            // Parse OIDC group claims into group memberships. If enabled, we perform this
            // check each time a user authenticates so that changes made on the OIDC
            // server are applied in AtoM on the next login.
            $setGroupsFromClaims = sfConfig::get('app_oidc_set_groups_from_attributes', false);
            if (true == $setGroupsFromClaims) {
                $rolesPath = sfConfig::get('app_oidc_roles_path', []);
                $rolesSource = sfConfig::get('app_oidc_roles_source', '');

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
            // Refresh user so new groups and credentials are immediately available on signIn().
            $this->signIn(QubitUser::getById($user->id));
        }

        return $authenticated;
    }

    /**
     * Returns bool value indicating if this user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        $authenticated = parent::isAuthenticated();

        if (false == sfConfig::get('app_oidc_enable_refresh_token_use', false) || false === $authenticated) {
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
                $refreshResult = $this->oidcClient->refreshToken($refreshToken);

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
    public function logout()
    {
        $idToken = $this->getAttribute('oidc-token', null);
        $this->unsetAttributes();
        $this->signOut();

        if (true == sfConfig::get('app_oidc_send_oidc_logout', false) && !empty($idToken)) {
            $logoutRedirectUrl = sfConfig::get('app_oidc_logout_redirect_url', '');
            if (empty($logoutRedirectUrl)) {
                $logoutRedirectUrl = null;
                $this->logger->err('Setting "app_oidc_logout_redirect_url" invalid. Unable to redirect on sign out.');
            }

            // Dex does not yet implement end_session_endpoint with it's oidc connector
            // so $this->oidcClient->signOut will fail.
            // https://github.com/dexidp/dex/issues/1697
            try {
                $this->oidcClient->signOut($idToken, $logoutRedirectUrl);
            } catch (Exception $e) {
                $this->logger->err($e->__toString().PHP_EOL);
            }
        }
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
     * Parse group claims for role info returned by OIDC server. Returns
     * array containing assigned roles. Claims are searched based on nodes
     * specified in $pathArray.
     *
     * @param object $claims
     * @param array  $pathArray
     *
     * @return array $roles
     */
    protected function parseOidcRoleClaims($claims, $pathArray)
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
        $userGroups = sfConfig::get('app_oidc_user_groups');
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
    private function unsetAttributes()
    {
        $this->setAttribute('oidc-token', '');
        $this->setAttribute('oidc-expiry', '');
        $this->setAttribute('oidc-refresh', '');
    }
}
