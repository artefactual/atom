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

require_once 'Zend/Acl.php';

class StubAcl extends Zend_Acl
{
    public function allow($roles = null, $resource = null, $privileges = null, ?Zend_Acl_Assert_Interface $assert = null)
    {
        return $this;
    }

    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        return true;
    }
}

class QubitTestFunctional extends sfTestFunctional
{
    public function disableSecurity()
    {
        QubitAcl::getInstance()->acl = new StubAcl();

        // PHP 5.3 only, but this will soon be a minimum requirement?
        $this->browser->addListener('controller.change_action', function (sfEvent $event) {
            $controller = $event->getSubject();

            // Total HACK, disable sfBasicSecurityFilter
            sfConfig::set('sf_secure_module', $event->module);
            sfConfig::set('sf_secure_action', $event->action);
        });
    }
}
