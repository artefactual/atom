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
 * GNU General Public License for that purpose.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Decides whether the current user should see the "Translate user interface"
 * bar (rendered by sfTranslatePlugin in the page footer).
 *
 * Unlike QubitAcl::check('userInterface', 'translate'), which also matches
 * blanket "all privileges" grants (acl_permission rows with NULL action),
 * this check requires an EXPLICIT translate permission (action = 'translate')
 * granted to the user or one of their groups, so administrators with the
 * default "admin all" grant no longer see the bar unless they (or their
 * group) have been explicitly granted translate access.
 *
 * The current UI culture must also satisfy the permission's language
 * conditional (if any), mirroring QubitAclConditionalAssert behavior for
 * 'translate' permissions.
 *
 * @author Sawyer Borror <sawyerb@ksu.edu>
 */
class QubitTranslateBarAccess
{
    /**
     * Check whether the current user has an explicit translate permission
     * that applies to their current UI culture.
     *
     * @param myUser $user sf user instance (defaults to the current user)
     *
     * @return bool true if the user has an explicit, culture-appropriate
     *              translate grant; false otherwise
     */
    public static function hasExplicitTranslateAccess(?myUser $user = null): bool
    {
        if (null === $user) {
            $user = sfContext::getInstance()->getUser();
        }

        if (!$user->isAuthenticated() || null === $user->user) {
            return false;
        }

        $permissions = self::getExplicitTranslatePermissions($user);
        $culture = $user->getCulture();

        foreach ($permissions as $permission) {
            if (1 == $permission->grantDeny && $permission->evaluateConditional(['language' => $culture])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch all explicit, system-wide translate permission rows that apply
     * to the given user: rows belonging to the user directly, or to any
     * group the user belongs to (including that group's ancestors), with a
     * NULL object_id (applies to all objects).
     *
     * Only NULL object_id rows are considered: QubitAcl::check() matches
     * them against the 'userInterface' resource, which is what originally
     * triggered the footer bar.
     *
     * @param myUser $user authenticated sf user instance
     *
     * @return array of QubitAclPermission explicit translate permission rows
     */
    protected static function getExplicitTranslatePermissions(myUser $user): array
    {
        $roleIds = [QubitAclGroup::AUTHENTICATED_ID, $user->getUserID()];

        foreach ($user->user->getAclGroups() as $group) {
            // Include group ancestors so inherited grants are respected,
            // mirroring the role hierarchy built in QubitAcl::buildUserRoleList
            foreach ($group->getAncestorsAndSelfForAcl() as $ancestor) {
                if (!in_array($ancestor->id, $roleIds)) {
                    $roleIds[] = $ancestor->id;
                }
            }
        }

        $criteria = new Criteria();
        $criteria->add(QubitAclPermission::ACTION, 'translate');
        $criteria->add(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);

        // Match permissions on the user itself OR on any of its group roles
        $roleCriterion = $criteria->getNewCriterion(QubitAclPermission::GROUP_ID, $roleIds, Criteria::IN);
        $roleCriterion->addOr($criteria->getNewCriterion(QubitAclPermission::USER_ID, $user->getUserID()));
        $criteria->add($roleCriterion);

        $permissions = QubitAclPermission::get($criteria);

        return is_array($permissions) ? $permissions : iterator_to_array($permissions);
    }
}
