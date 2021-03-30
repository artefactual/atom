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
 * Custom ACL rules for QubitInformationObject resources.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitInformationObjectAcl extends QubitAcl
{
    // Add viewDraft and publish actions to list
    public static $ACTIONS = [
        'read' => 'Read',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'translate' => 'Translate',
        'viewDraft' => 'View draft',
        'publish' => 'Publish',
        'readMaster' => 'Access master',
        'readReference' => 'Access reference',
        'readThumbnail' => 'Access thumbnail',
    ];

    // For information objects check parent authorization for create OR publish
    // actions
    protected static $_parentAuthActions = ['create', 'publish'];
    protected static $_digitalObjectActions = ['readMaster', 'readReference', 'readThumbnail'];

    /**
     * Do custom ACL checks for QubitInformationObject resources.
     *
     * @param myUser                 $user     to authorize
     * @param QubitInformationObject $resource target of the requested action
     * @param string                 $action   requested for authorization (e.g. 'read')
     * @param null|array             $options  optional parameters
     *
     * @return bool true if the access request is authorized
     */
    public static function isAllowed($user, $resource, $action, $options = [])
    {
        if ('read' == $action) {
            return self::isReadAllowed($user, $resource, $action, $options);
        }

        // Do custom ACL checks for digital object actions
        if (in_array($action, self::$_digitalObjectActions)) {
            return self::isDigitalObjectActionAllowed(
                $user,
                $resource,
                $action,
                $options
            );
        }

        // Call QubitAcl::isAllowed(), when no special rules apply
        return parent::isAllowed($user, $resource, $action, $options);
    }

    /**
     * Custom QubitInformationObject "read" authorization rules.
     *
     * @param myUser     $user     to authorize
     * @param mixed      $resource target of the requested action
     * @param string     $action   requested for authorization (e.g. 'read')
     * @param null|array $options  optional parameters
     *
     * @return bool true if the access request is authorized
     */
    private static function isReadAllowed($user, $resource, $action, $options = [])
    {
        if (null === $resource->getPublicationStatus()) {
            throw new sfException(
                'No publication status set for information object id: '.$resource->id
            );
        }

        // If this is a draft information object, check "read" and "viewDraft"
        // authorization
        if (
            QubitTerm::PUBLICATION_STATUS_DRAFT_ID
            == $resource->getPublicationStatus()->statusId
        ) {
            $instance = self::getInstance()->buildAcl($resource, $options);

            return $instance->acl->isAllowed($user, $resource, 'read')
                && $instance->acl->isAllowed($user, $resource, 'viewDraft');
        }

        // Otherwise, just do a "read" ACL check
        return parent::isAllowed($user, $resource, $action, $options);
    }

    /**
     * Do custom ACL checks for digital object actions.
     *
     * @param myUser     $user     to authorize
     * @param mixed      $resource target of the requested action
     * @param string     $action   requested for authorization (e.g. 'read')
     * @param null|array $options  optional parameters
     */
    private static function isDigitalObjectActionAllowed(
        $user,
        $resource,
        $action,
        $options = []
    ) {
        // All users are authorized to read text (PDF) masters
        if ('readMaster' == $action && $resource->hasTextDigitalObject()) {
            return true;
        }

        // Do the standard QubitAcl authorization check AND a QubitGrantedRight
        // check
        return parent::isAllowed($user, $resource, $action, $options)
            && QubitGrantedRight::checkPremis($resource->id, $action);
    }
}
