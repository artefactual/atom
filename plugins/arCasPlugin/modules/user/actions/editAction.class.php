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
  public static
    $NAMES = array(
      'active',
      'groups',
      'translate',
      'restApiKey',
      'oaiApiKey');

  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = new QubitUser;
    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;
    }

    // HACK: because $this->user->getAclPermissions() is erroneously calling
    // QubitObject::getaclPermissionsById()
    $this->permissions = null;
    if (isset($this->resource->id))
    {
      $permissions = QubitUser::getaclPermissionsById($this->resource->id, array('self' => $this))->orderBy('constants')->orderBy('object_id');

      foreach ($permissions as $item)
      {
        $repository = $item->getConstants(array('name' => 'repository'));
        $this->permissions[$repository][$item->objectId][$item->action] = $item->grantDeny;
      }
    }

    // List of actions without translate
    $this->basicActions = QubitInformationObjectAcl::$ACTIONS;
    unset($this->basicActions['translate']);
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'active':
        if (isset($this->resource->id))
        {
          $this->form->setDefault('active', (bool)$this->resource->active);
        }
        else
        {
          $this->form->setDefault('active', true);
        }


        $this->form->setValidator('active', new sfValidatorBoolean);
        $this->form->setWidget('active', new sfWidgetFormInputCheckbox);

        break;

      case 'groups':
        $values = array();
        $criteria = new Criteria;
        $criteria->add(QubitAclUserGroup::USER_ID, $this->resource->id);
        foreach (QubitAclUserGroup::get($criteria) as $item)
        {
          $values[] = $item->groupId;
        }

        $choices = array();
        $criteria = new Criteria;
        $criteria->add(QubitAclGroup::ID, 99, Criteria::GREATER_THAN);
        foreach (QubitAclGroup::get($criteria) as $item)
        {
          $choices[$item->id] = $item->getName(array('cultureFallback' => true));
        }

        $this->form->setDefault('groups', $values);
        $this->form->setValidator('groups', new sfValidatorPass);
        $this->form->setWidget('groups', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

        break;

      case 'translate':
        $c = sfCultureInfo::getInstance($this->context->user->getCulture());
        $languages = $c->getLanguages();
        $choices = array();

        foreach (sfConfig::get('app_i18n_languages') as $item)
        {
          $choices[$item] = $languages[$item];
        }

        // Find existing translate permissions
        $criteria = new Criteria;
        $criteria->add(QubitAclPermission::USER_ID, $this->resource->id);
        $criteria->add(QubitAclPermission::ACTION, 'translate');

        $defaults = null;
        if (null !== $permission = QubitAclPermission::getOne($criteria))
        {
          $defaults = $permission->getConstants(array('name' => 'languages'));
        }

        $this->form->setDefault('translate', $defaults);
        $this->form->setValidator('translate', new sfValidatorPass);
        $this->form->setWidget('translate', new sfWidgetFormSelect(array('choices'  => $choices, 'multiple' => true)));

        break;

      case 'restApiKey':
      case 'oaiApiKey':
        // Give user option of (re)generating or deleting API key
        $choices = array(
          ''         => $this->context->i18n->__('-- Select action --'),
          'generate' => $this->context->i18n->__('(Re)generate API key'),
          'delete'   => $this->context->i18n->__('Delete API key')
        );

        $this->form->setValidator($name, new sfValidatorString());
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        // Expose API key value to template if one exists
        $apiKey = QubitProperty::getOneByObjectIdAndName($this->resource->id, sfInflector::camelize($name));
        if (null != $apiKey)
        {
          $this->$name = $apiKey->value;
        }

        // Expose whether or not API is enabled
        if ($name == 'oaiApiKey')
        {
          $this->oaiEnabled = $this->context->getConfiguration()->isPluginEnabled('arOaiPlugin');
        }
        else
        {
          $this->restEnabled = $this->context->getConfiguration()->isPluginEnabled('arRestApiPlugin');
        }

        break;
    }
  }

  protected function processField($field)
  {
    switch ($name = $field->getName())
    {
      case 'active':
        $this->resource->active = $this->form->getValue('active') ? true : false;

        break;

      case 'groups':
        $newGroupIds = $formGroupIds = array();

        if (null != ($groups = $this->form->getValue('groups')))
        {
          foreach ($groups as $item)
          {
            $newGroupIds[$item] = $formGroupIds[$item] = $item;
          }
        }
        else
        {
          $newGroupIds = $formGroupIds = array();
        }

        // Don't re-add existing groups + delete exiting groups that are no longer
        // in groups list
        foreach ($this->resource->aclUserGroups as $item)
        {
          if (in_array($item->groupId, $formGroupIds))
          {
            unset($newGroupIds[$item->groupId]);
          }
          else
          {
            $item->delete();
          }
        }

        foreach ($newGroupIds as $item)
        {
          $userGroup = new QubitAclUserGroup;
          $userGroup->groupId = $item;

          $this->resource->aclUserGroups[] = $userGroup;
        }

        break;

      case 'restApiKey':
      case 'oaiApiKey':
        $keyAction = $this->form->getValue($name);
        $apiKey = QubitProperty::getOneByObjectIdAndName($this->resource->id, sfInflector::camelize($name));

        switch ($keyAction)
        {
          case 'generate':
            // Create user OAI-PMH key property if it doesn't exist
            if (null === $apiKey)
            {
              $apiKey = new QubitProperty;
              $apiKey->name = sfInflector::camelize($name);
            }

            // Generate new OAI-PMH API key
            $apiKey->value = bin2hex(openssl_random_pseudo_bytes(8));

            if (!isset($apiKey->id))
            {
              $this->resource->propertys[] = $apiKey;
            }
            else
            {
              $apiKey->save();
            }

            break;

          case 'delete':
            // Delete user OAI-PMH key property if it exists
            if (null != $apiKey)
            {
              $apiKey->delete();
            }

            break;
        }

        break;

      default:
        $this->resource[$name] = $this->form->getValue($name);
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        if ($this->resource !== sfContext::getInstance()->getUser()->user)
        {
          $this->resource->active = 0;
        }

        $this->processForm();

        $this->resource->save();

        // Allowed languages for translation must be saved after the user is created
        $languages = $this->form->getValue('translate');

        $criteria = new Criteria;
        $criteria->add(QubitAclPermission::USER_ID, $this->resource->id);
        $criteria->addAnd(QubitAclPermission::USER_ID, null, Criteria::ISNOTNULL);
        $criteria->add(QubitAclPermission::ACTION, 'translate');

        if (null === $permission = QubitAclPermission::getOne($criteria))
        {
          $permission = new QubitAclPermission;
          $permission->userId = $this->resource->id;
          $permission->action = 'translate';
          $permission->grantDeny = 1;
          $permission->conditional = 'in_array(%p[language], %k[languages])';
        }
        else if (!is_array($languages))
        {
          // If $languages is not an array, then remove the translate permission
          $permission->delete();
        }

        if (is_array($languages))
        {
          $permission->setConstants(array('languages' => $languages));
          $permission->save();
        }

        if ($this->context->getViewCacheManager() !== null)
        {
          // We just need to remove the cache for this user but sf_cache_key
          // contents also the culture code, it worth the try? I don't think so
          $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_mainMenu&sf_cache_key=*');
        }

        $this->redirect(array($this->resource, 'module' => 'user'));
      }
    }
  }
}
