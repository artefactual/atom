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

class UserEditAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'active',
        'email',
        'groups',
        'translate',
        'username',
        'restApiKey',
        'oaiApiKey',
    ];

    public function execute($request)
    {
        parent::execute($request);

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                if ($this->resource !== sfContext::getInstance()->getUser()->user) {
                    $this->resource->active = 0;
                }

                $this->processForm();

                $this->resource->save();

                // Allowed languages for translation must be saved after the user is created
                $languages = $this->form->getValue('translate');

                $criteria = new Criteria();
                $criteria->add(QubitAclPermission::USER_ID, $this->resource->id);
                $criteria->addAnd(QubitAclPermission::USER_ID, null, Criteria::ISNOTNULL);
                $criteria->add(QubitAclPermission::ACTION, 'translate');

                if (null === $permission = QubitAclPermission::getOne($criteria)) {
                    $permission = new QubitAclPermission();
                    $permission->userId = $this->resource->id;
                    $permission->action = 'translate';
                    $permission->grantDeny = 1;
                    $permission->conditional = 'in_array(%p[language], %k[languages])';
                } elseif (!is_array($languages)) {
                    // If $languages is not an array, then remove the translate permission
                    $permission->delete();
                }

                if (is_array($languages)) {
                    $permission->setConstants(['languages' => $languages]);
                    $permission->save();
                }

                if (null !== $this->context->getViewCacheManager()) {
                    // We just need to remove the cache for this user but sf_cache_key
                    // contents also the culture code, it worth the try? I don't think so
                    $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_mainMenu&sf_cache_key=*');
                }

                $this->redirect([$this->resource, 'module' => 'user']);
            }
        }
    }

    public function exists($validator, $values)
    {
        $criteria = new Criteria();

        if (isset($this->resource->id)) {
            $criteria->add(QubitUser::ID, $this->resource->id, Criteria::NOT_EQUAL);
        }

        $criterion1 = $criteria->getNewCriterion(QubitUser::USERNAME, $values['username']);
        $criterion2 = $criteria->getNewCriterion(QubitUser::EMAIL, $values['email']);
        $criteria->add($criterion1->addOr($criterion2));

        if (0 < count(QubitUser::get($criteria))) {
            throw new sfValidatorError($validator, $this->context->i18n->__('The username or e-mail address you entered is already in use'));
        }

        return $values;
    }

    protected function earlyExecute()
    {
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) {
            $this->form->getValidatorSchema()->setPostValidator(
                new sfValidatorCallback(['callback' => [$this, 'exists']])
            );
        }

        $this->resource = new QubitUser();
        if (isset($this->getRoute()->resource)) {
            $this->resource = $this->getRoute()->resource;
        }

        // HACK: because $this->user->getAclPermissions() is erroneously calling
        // QubitObject::getaclPermissionsById()
        $this->permissions = null;
        if (isset($this->resource->id)) {
            $permissions = QubitUser::getaclPermissionsById($this->resource->id, ['self' => $this])->orderBy('constants')->orderBy('object_id');

            foreach ($permissions as $item) {
                $repository = $item->getConstants(['name' => 'repository']);
                $this->permissions[$repository][$item->objectId][$item->action] = $item->grantDeny;
            }
        }

        // List of actions without translate
        $this->basicActions = QubitInformationObjectAcl::$ACTIONS;
        unset($this->basicActions['translate']);
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'username':
                if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) {
                    $this->form->setDefault('username', $this->resource->username);
                    $this->form->setValidator('username', new sfValidatorString(['required' => true]));
                    $this->form->setWidget('username', new sfWidgetFormInput());
                }

                break;

            case 'email':
                if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) {
                    $this->form->setDefault('email', $this->resource->email);
                    $this->form->setValidator('email', new sfValidatorEmail(['required' => true]));
                    $this->form->setWidget('email', new sfWidgetFormInput());
                }

                break;

            case 'active':
                if (isset($this->resource->id)) {
                    $this->form->setDefault('active', (bool) $this->resource->active);
                } else {
                    $this->form->setDefault('active', true);
                }

                $this->form->setValidator('active', new sfValidatorBoolean());
                $this->form->setWidget('active', new sfWidgetFormInputCheckbox());

                break;

            case 'groups':
                $values = [];
                $criteria = new Criteria();
                $criteria->add(QubitAclUserGroup::USER_ID, $this->resource->id);
                foreach (QubitAclUserGroup::get($criteria) as $item) {
                    $values[] = $item->groupId;
                }

                $choices = [];
                $criteria = new Criteria();
                $criteria->add(QubitAclGroup::ID, 99, Criteria::GREATER_THAN);
                foreach (QubitAclGroup::get($criteria) as $item) {
                    $choices[$item->id] = $item->getName(['cultureFallback' => true]);
                }

                $this->form->setDefault('groups', $values);
                $this->form->setValidator('groups', new sfValidatorPass());
                $this->form->setWidget('groups', new sfWidgetFormSelect(['choices' => $choices, 'multiple' => true]));

                break;

            case 'translate':
                $c = sfCultureInfo::getInstance($this->context->user->getCulture());
                $languages = $c->getLanguages();
                $choices = [];

                foreach (sfConfig::get('app_i18n_languages') as $item) {
                    $choices[$item] = $languages[$item];
                }

                // Find existing translate permissions
                $criteria = new Criteria();
                $criteria->add(QubitAclPermission::USER_ID, $this->resource->id);
                $criteria->add(QubitAclPermission::ACTION, 'translate');

                $defaults = null;
                if (null !== $permission = QubitAclPermission::getOne($criteria)) {
                    $defaults = $permission->getConstants(['name' => 'languages']);
                }

                $this->form->setDefault('translate', $defaults);
                $this->form->setValidator('translate', new sfValidatorPass());
                $this->form->setWidget('translate', new sfWidgetFormSelect(['choices' => $choices, 'multiple' => true]));

                break;

            case 'restApiKey':
            case 'oaiApiKey':
                // Give user option of (re)generating or deleting API key
                $choices = [
                    '' => $this->context->i18n->__('-- Select action --'),
                    'generate' => $this->context->i18n->__('(Re)generate API key'),
                    'delete' => $this->context->i18n->__('Delete API key'),
                ];

                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                // Expose API key value to template if one exists
                $apiKey = QubitProperty::getOneByObjectIdAndName($this->resource->id, sfInflector::camelize($name));
                if (null != $apiKey) {
                    $this->{$name} = $apiKey->value;
                }

                // Expose whether or not API is enabled
                if ('oaiApiKey' == $name) {
                    $this->oaiEnabled = $this->context->getConfiguration()->isPluginEnabled('arOaiPlugin');
                } else {
                    $this->restEnabled = $this->context->getConfiguration()->isPluginEnabled('arRestApiPlugin');
                }

                break;
        }
    }

    protected function processField($field)
    {
        switch ($name = $field->getName()) {
            case 'active':
                $this->resource->active = $this->form->getValue('active') ? true : false;

                break;

            case 'groups':
                $newGroupIds = $formGroupIds = [];

                if (null != ($groups = $this->form->getValue('groups'))) {
                    foreach ($groups as $item) {
                        $newGroupIds[$item] = $formGroupIds[$item] = $item;
                    }
                } else {
                    $newGroupIds = $formGroupIds = [];
                }

                // Don't re-add existing groups + delete exiting groups that are no longer
                // in groups list
                foreach ($this->resource->aclUserGroups as $item) {
                    if (in_array($item->groupId, $formGroupIds)) {
                        unset($newGroupIds[$item->groupId]);
                    } else {
                        $item->delete();
                    }
                }

                foreach ($newGroupIds as $item) {
                    $userGroup = new QubitAclUserGroup();
                    $userGroup->groupId = $item;

                    $this->resource->aclUserGroups[] = $userGroup;
                }

                break;

            case 'restApiKey':
            case 'oaiApiKey':
                $keyAction = $this->form->getValue($name);
                $apiKey = QubitProperty::getOneByObjectIdAndName($this->resource->id, sfInflector::camelize($name));

                switch ($keyAction) {
                    case 'generate':
                        // Create user OAI-PMH key property if it doesn't exist
                        if (null === $apiKey) {
                            $apiKey = new QubitProperty();
                            $apiKey->name = sfInflector::camelize($name);
                        }

                        // Generate new OAI-PMH API key
                        $apiKey->value = bin2hex(openssl_random_pseudo_bytes(8));

                        if (!isset($apiKey->id)) {
                            $this->resource->propertys[] = $apiKey;
                        } else {
                            $apiKey->save();
                        }

                        break;

                    case 'delete':
                        // Delete user OAI-PMH key property if it exists
                        if (null != $apiKey) {
                            $apiKey->delete();
                        }

                        break;
                }

                break;

            case 'username':
            case 'email':
                if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) {
                    $this->resource[$name] = $this->form->getValue($name);
                }

                break;

            default:
                $this->resource[$name] = $this->form->getValue($name);
        }
    }
}
