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

class casUser extends myUser implements Zend_Acl_Role_Interface
{
    public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = [])
    {
        // initialize parent
        parent::initialize($dispatcher, $storage, $options);
    }

    /**
     * Try to login with the CAS server.
     *
     * @param null|mixed $username
     * @param null|mixed $password
     */
    public function authenticate($username = null, $password = null)
    {
        $authenticated = false;

        arCAS::initializePhpCAS();
        phpCAS::forceAuthentication();
        $username = phpCAS::getUser();

        // Load user using username or, if one doesn't exist, create it.
        $criteria = new Criteria();
        $criteria->add(QubitUser::USERNAME, $username);
        if (null === $user = QubitUser::getOne($criteria)) {
            $user = new QubitUser();
            $user->username = $username;
            $user->save();
        }

        // Parse CAS attributes into group memberships. If enabled, we perform this
        // check each time a user authenticates so that changes made on the CAS
        // server are applied in AtoM on the next login.
        if (true == sfConfig::get('app_cas_set_groups_from_attributes', false)) {
            $attributes = phpCAS::getAttributes();
            $this->setGroupsFromCasAttributes($user, $attributes);
        }

        $authenticated = true;
        $this->signIn($user);

        return $authenticated;
    }

    /**
     * Logout from AtoM and the CAS server.
     */
    public function logout()
    {
        $this->signOut();
        arCAS::initializePhpCAS();
        phpCAS::logout();
    }

    /**
     * Set group membership based on user attributes returned by CAS server.
     *
     * @param mixed $user
     * @param mixed $attributes
     */
    protected function setGroupsFromCasAttributes($user, $attributes)
    {
        // Get the CAS attribute we're checking for AtoM group membership. If the
        // attribute doesn't exist or is null, log the error and return.
        $attributeKey = sfConfig::get('app_cas_attribute_key');

        if (!array_key_exists($attributeKey, $attributes)) {
            sfContext::getInstance()->getLogger()->err('Key not found in CAS attributes');

            return;
        }

        $attributeToCheck = $attributes[$attributeKey];

        if (null === $attributeToCheck) {
            sfContext::getInstance()->getLogger()->err('CAS attribute used for setting AtoM group membership is null');

            return;
        }

        // The value for a given CAS attribute can be an array or a string. If it's
        // a string, we convert into an array to simplify the checking routine.
        if (!is_array($attributeToCheck)) {
            $attributeToCheck = [$attributeToCheck];
        }

        // Delete existing AclUserGroups for this user. This allows us to reset
        // group membership on each login so that users will only belong to groups
        // that are appropriately configured in app_cas_user_groups.
        $criteria = new Criteria();
        $criteria->add(QubitAclUserGroup::USER_ID, $user->id);
        foreach (QubitAclUserGroup::get($criteria) as $item) {
            $item->delete();
        }

        // Add the user to AclUserGroups based on the presence of expected CAS
        // attribute values as set in app_cas_user_groups.
        $userGroups = sfConfig::get('app_cas_user_groups');
        foreach ($userGroups as $item) {
            if (null !== $group = QubitAclGroup::getById($item['group_id'])) {
                $expectedValue = $item['attribute_value'];
                if (in_array($expectedValue, $attributeToCheck)) {
                    $userGroup = new QubitAclUserGroup();
                    $userGroup->userId = $user->id;
                    $userGroup->groupId = $group->id;
                    $userGroup->save();
                }
            }
        }
    }
}
