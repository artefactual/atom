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

/**
 * Custom ACL rules for QubitContactInformation resources.
 *
 * @author     Daniel Lovegrove <d.lovegrove11@gmail.com>
 */
class QubitContactInformationAcl extends QubitAcl
{
    /**
     * Do custom ACL checks for QubitContactInformation resources.
     *
     * @param myUser                  $user     to authorize
     * @param QubitContactInformation $resource target of the requested action
     * @param string                  $action   requested for authorization (e.g. 'read')
     * @param null|array              $options  optional parameters
     *
     * @return bool true if the access request is authorized
     */
    public static function isAllowed($user, $resource, $action, $options = [])
    {
        if (!$user->isAuthenticated()) {
            return false;
        }

        // Always allow when the user is an editor or administrator
        if ($user->hasGroup(QubitAclGroup::ADMINISTRATOR_ID) || $user->hasGroup(QubitAclGroup::EDITOR_ID)) {
            return true;
        }

        // A contact information may be a child of a:
        // - QubitRepository
        // - QubitActor (and its various sub-classes, like QubitDonor)
        //
        // If the contact information is linked to either, do the permission check on the parent object instead.

        if (null !== $repository = QubitRepository::getById($resource->actorId)) {
            return parent::isAllowed($user, $repository, $action, $options);
        }

        if (null !== $actor = QubitActor::getById($resource->actorId)) {
            return parent::isAllowed($user, $actor, $action, $options);
        }

        return false;
    }
}
