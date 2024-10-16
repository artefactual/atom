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
 * ACL conditional assert.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitAclConditionalAssert implements Zend_Acl_Assert_Interface
{
    public function __construct($permission)
    {
        $this->permission = $permission;
    }

    public function assert(
        Zend_Acl $acl,
        ?Zend_Acl_Role_Interface $role = null,
        ?Zend_Acl_Resource_Interface $resource = null,
        $privilege = null
    ) {
        // Translate permissions are global to all objects
        if ('translate' == $privilege) {
            // If source language is the current language, then we aren't translating
            if (method_exists($resource, 'getSourceCulture') && $resource->sourceCulture == sfContext::getInstance()->user->getCulture()) {
                return false;
            }

            // Test that user can translate into current language
            if (!$this->permission->evaluateConditional(['language' => sfContext::getInstance()->user->getCulture()])) {
                return false;
            }
        }

        // No update if source language != current language (requires translate)
        elseif ('update' == $privilege && $resource->sourceCulture != sfContext::getInstance()->user->getCulture()) {
            return false;
        }

        if ($resource instanceof QubitInformationObject) {
            $repositorySlug = null;
            if (null !== $repository = $resource->getRepository(['inherit' => true])) {
                $repositorySlug = $repository->slug;
            }

            // Test repository conditional
            if (!$this->permission->evaluateConditional(['repository' => $repositorySlug])) {
                return false;
            }
        } elseif ($resource instanceof QubitTerm) {
            // Test taxonomy conditional
            if (!$this->permission->evaluateConditional(['taxonomy' => $resource->taxonomy->slug])) {
                return false;
            }
        }

        return true;
    }
}
