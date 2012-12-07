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

class AclGroupEditAction extends sfAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'name',
      'description',
      'translate');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'name':
        $this->form->setDefault($name, $this->group[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'description':
        $this->form->setDefault($name, $this->group[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);

        break;

      case 'translate':
        $choices = array(1 => 'Yes', 0 => 'No');

        // Check for grant permission
        $criteria = new Criteria;
        $criteria->add(QubitAclPermission::GROUP_ID, $this->group->id);
        $criteria->add(QubitAclPermission::ACTION, 'translate');
        $criteria->add(QubitAclPermission::GRANT_DENY, 1);

        $default = 0;
        if (null !== QubitAclPermission::getOne($criteria))
        {
          $default = 1;
        }

        // Search for translate permissions
        $this->form->setDefault($name, $default);
        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormChoice(array('expanded' => true, 'choices' => $choices)));

        break;
    }
  }

  public function execute($request)
  {
    $this->group = new QubitAclGroup;

    if (isset($this->request->id))
    {
      $this->group = QubitAclGroup::getById($this->request->id);

      if (!isset($this->group))
      {
        $this->forward404();
      }
    }

    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->processForm();
        $this->redirect(array($this->group, 'module' => 'aclGroup'));
      }
    }
  }

  protected function processForm()
  {
    foreach ($this->form as $field)
    {
      $this->processField($field);
    }

    if (null === $this->group->parentId)
    {
      // By default, inherit permissions from authenticated group
      $this->group->parentId = QubitAclGroup::AUTHENTICATED_ID;
    }

    $this->group->save();
  }

  /**
   * Process form fields
   *
   * @param $field mixed symfony form widget
   * @return void
   */
  protected function processField($field)
  {
    switch ($name = $field->getName())
    {
      case 'translate':
        $criteria = new Criteria;
        $criteria->add(QubitAclPermission::GROUP_ID, $this->group->id);
        $criteria->add(QubitAclPermission::ACTION, 'translate');

        $translatePermission = QubitAclPermission::getOne($criteria);

        if (1 == $this->form->getValue($name))
        {
          if (null === $translatePermission)
          {
            $translatePermission = new QubitAclPermission;
            $translatePermission->action  = 'translate';
            $translatePermission->grantDeny = 1;
          }
          else
          {
            $translatePermission->grantDeny = 1;
          }

          $this->group->aclPermissions[] = $translatePermission;
        }
        else if (null !== $translatePermission)
        {
          $translatePermission->delete();
        }

        break;

      default:
        $this->group[$name] = $this->form->getValue($name);
    }
  }
}
