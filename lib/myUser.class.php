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

class myUser extends sfBasicSecurityUser implements Zend_Acl_Role_Interface
{
    public $user;

    // Module-specific permissions get temporarily stored here for access checks
    protected $security = [];

    /**
     * Required for Zend_Acl_Role_Interface.
     */
    public function getRoleId()
    {
        if ($this->isAuthenticated()) {
            return $this->getUserID();
        }

        return QubitAclGroup::ANONYMOUS_ID;
    }

    public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = [])
    {
        // initialize parent
        parent::initialize($dispatcher, $storage, $options);

        // On timeout, remove *all* user credentials
        if ($this->isTimedOut()) {
            $this->signOut();

            return;
        }

        if ($this->isAuthenticated()) {
            try {
                $this->user = QubitUser::getById($this->getUserID());
            } catch (Exception $e) {
                $this->user = null;
            }

            // If this user's account has been *deleted* or this user session is from a
            // different install of qubit on the same server (cross-site), then signout
            // user
            if (null === $this->user || !$this->user->active) {
                $this->signOut();
            }
        }

        // Allow reverse proxies to pass a header to change culture
        if (!empty($_SERVER['HTTP_X_ATOM_CULTURE'])) {
            $this->setCulture($_SERVER['HTTP_X_ATOM_CULTURE']);
        }
    }

    public function signIn($user)
    {
        $this->setAuthenticated(true);
        $this->user = $user;

        foreach ($user->getAclGroups() as $group) {
            $this->addCredential($group->getName(['culture' => 'en']));
        }

        $this->setAttribute('user_id', $user->id);
        $this->setAttribute('user_slug', $user->slug);
        $this->setAttribute('user_name', $user->username);
    }

    public function signOut()
    {
        $this->getAttributeHolder()->removeNamespace('credentialScope');

        $this->clearCredentials();
        $this->setAuthenticated(false);

        $this->getAttributeHolder()->remove('user_id');
        $this->getAttributeHolder()->remove('user_slug');
        $this->getAttributeHolder()->remove('user_name');
        $this->getAttributeHolder()->remove('login_route');
        $this->getAttributeHolder()->remove('nav_context_module');
    }

    public function removeAttribute($attribute)
    {
        $this->getAttributeHolder()->remove($attribute);
    }

    public function getUserID()
    {
        return $this->getAttribute('user_id');
    }

    public function getUserSlug()
    {
        return $this->getAttribute('user_slug');
    }

    public function getUserName()
    {
        return $this->getAttribute('user_name');
    }

    public function authenticate($username, $password)
    {
        return $this->authenticateWithBasicAuth($username, $password);
    }

    // This method is is intended to be overridable in user classes that inherit
    // from myUser. This enables authorization schemes such as CAS to retain a
    // basic auth option for DIP Upload that can be voided if necessary.
    public function authenticateWithBasicAuth($username, $password)
    {
        $authenticated = false;
        // anonymous is not a real user
        if ('anonymous' == $username) {
            return false;
        }

        $user = QubitUser::checkCredentials($username, $password, $error);

        // user account exists?
        if (null !== $user) {
            $authenticated = true;
            $this->signIn($user);
        }

        return $authenticated;
    }

    public function getQubitUser()
    {
        return $this->user;
    }

    public function getAclGroups()
    {
        if (!$this->isAuthenticated()) {
            return [QubitAclGroup::getById(QubitAclGroup::ANONYMOUS_ID)];
        }

        return $this->user->getAclGroups();
    }

    public function hasGroup($checkGroups)
    {
        $hasGroup = false;

        if ($this->isAuthenticated()) {
            $hasGroup = $this->user->hasGroup($checkGroups);
        } else {
            if (!is_array($checkGroups)) {
                $checkGroups = [$checkGroups];
            }

            if (in_array(QubitAclGroup::ANONYMOUS_ID, $checkGroups)) {
                $hasGroup = true;
            }
        }

        return $hasGroup;
    }

    public function listGroups()
    {
        if ($this->isAuthenticated()) {
            $groups = [QubitAclGroup::getById(QubitAclGroup::AUTHENTICATED_ID)];

            if (null !== $this->user->aclUserGroups) {
                foreach ($this->user->aclUserGroups as $aclUserGroup) {
                    $groups[] = QubitAclGroup::getById($aclUserGroup->groupId);
                }
            }

            return $groups;
        }

        return QubitAclGroup::getById(QubitAclGroup::ANONYMOUS_ID);
    }

    /**
     * Checks whether or not a user, based on security.yml settings, has access
     * to a module's action.
     *
     * This method uses the checkConfig method, used in other permission checks
     * in Symfony, that returns the name of a file containing PHP code derived
     * from YAML files. The file is generated if it doesn't yet exist. This
     * is done for performance reasons (to avoid the performance hit of parsing
     * the YAML repeatedly).
     *
     * @param string $module Name of module to check
     * @param string $action Name of action to check
     *
     * @return bool
     */
    public function checkModuleActionAccess($module, $action)
    {
        // Set security property to module's security configuration
        $securityFilePath = 'modules/'.$module.'/config/security.yml';
        if ($file = sfContext::getInstance()->getConfigCache()->checkConfig($securityFilePath, true)) {
            require $file;
        }

        // Get credentials, using security.yml parsing convention
        $credentials = $this->getModuleSecurityValue($action, 'credentials');

        // Allow access if action isn't secured or user has appropriate credentials
        return !$this->getModuleSecurityValue($action, 'is_secure', false) || $this->hasCredential($credentials);
    }

    /**
     * Get action-specific security setting value, if available, or, if not,
     * global or default value.
     *
     * @param string     $action          Name of module action to check
     * @param string     $securitySetting Security property to check
     * @param null|mixed $default
     *
     * @return bool
     */
    public function getModuleSecurityValue($action, $securitySetting, $default = null)
    {
        // These values get lower-cased when security.yml's rendered to PHP
        $action = strtolower($action);
        $securitySetting = strtolower($securitySetting);

        // If a property's specifically set for the action, return it
        if (isset($this->security[$action][$securitySetting])) {
            return $this->security[$action][$securitySetting];
        }

        // If a property's set for all actions that don't override it, return it
        if (isset($this->security['all'][$securitySetting])) {
            return $this->security['all'][$securitySetting];
        }

        return $default;
    }

    /**
     * Using $sf_user->hasGroup() since it relies on database,
     * $sf_user->hasCredential('administrator') relies on session storage
     * See 4214.
     *
     * @return bool
     */
    public function isAdministrator()
    {
        return $this->hasGroup(QubitAclGroup::ADMINISTRATOR_ID);
    }

    public function isAuthenticated()
    {
        if (sfConfig::get('app_read_only', false)) {
            return false;
        }

        return parent::isAuthenticated();
    }
}
