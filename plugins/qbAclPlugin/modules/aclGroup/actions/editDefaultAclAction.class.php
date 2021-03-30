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

class AclGroupEditDefaultAclAction extends sfAction
{
    public function execute($request)
    {
        $this->form = new sfForm();
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        if (isset($this->request->id)) {
            $this->resource = QubitAclGroup::getById($this->request->id);

            if (!isset($this->resource)) {
                $this->forward404();
            }
        }

        foreach ($this::$NAMES as $name) {
            $this->addField($name);
        }
    }

    protected function addField($name)
    {
        $this->form->setValidator($name, new sfValidatorString());
        $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => []]));
    }

    protected function processForm()
    {
        foreach ($this->request->acl as $key => $value) {
            // If key has an underscore, then we are creating a new permission
            if (1 == preg_match('/([\w]+)_(.*)/', $key, $matches)) {
                list($action, $uri) = array_slice($matches, 1, 2);
                $params = $this->context->routing->parse(Qubit::pathInfo($uri));
                if (isset($params['_sf_route']->resource)) {
                    $resource = $params['_sf_route']->resource;
                } else {
                    continue;
                }

                if (QubitAcl::INHERIT != $value && isset($this->basicActions[$action])) {
                    $aclPermission = new QubitAclPermission();
                    $aclPermission->action = $action;
                    $aclPermission->grantDeny = (QubitAcl::GRANT == $value) ? 1 : 0;

                    switch ($resource->className) {
                        case 'QubitRepository':
                            // Coming from editInformationObjectAcl class, add permissions to the
                            // repository information objects
                            if (false !== strrpos($this->request->getReferer(), 'editInformationObjectAcl')) {
                                $aclPermission->objectId = QubitInformationObject::ROOT_ID;
                                $aclPermission->setRepository($resource);
                            }
                            // If not, add permissions to the repository
                            else {
                                $aclPermission->object = $resource;
                            }

                            break;

                        case 'QubitTaxonomy':
                            $aclPermission->objectId = QubitTerm::ROOT_ID;
                            $aclPermission->setTaxonomy($resource);

                            break;

                        default:
                            $aclPermission->object = $resource;
                    }

                    $this->resource->aclPermissions[] = $aclPermission;
                }
            }

            // Otherwise, update an existing permission
            elseif (null !== $aclPermission = QubitAclPermission::getById($key)) {
                if (QubitAcl::INHERIT == $value) {
                    $aclPermission->delete();
                } else {
                    $aclPermission->grantDeny = (QubitAcl::GRANT == $value) ? 1 : 0;

                    $this->resource->aclPermissions[] = $aclPermission;
                }
            }
        }

        $this->resource->save();
    }
}
