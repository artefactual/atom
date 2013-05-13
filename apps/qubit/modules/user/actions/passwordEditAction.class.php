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

class UserPasswordEditAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'confirmPassword',
      'password');

  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
    $this->form->getValidatorSchema()->setPreValidator(new sfValidatorSchemaCompare(
      'password', '==', 'confirmPassword',
      array(),
      array('invalid' => $this->context->i18n->__('Your password confirmation did not match your password.'))));

    $this->resource = new QubitUser;
    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;
    }

    // Except for administrators, only allow users to reset their own password
    if (!$this->context->user->isAdministrator())
    {
      if ($this->resource->id != $this->context->user->getAttribute('user_id'))
      {
        QubitAcl::forwardToSecureAction();
      }
    }
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'password':
        $this->form->setDefault('password', null);

        // Use QubitValidatorPassword only when strong passwords are required
        if (sfConfig::get('app_require_strong_passwords'))
        {
          $this->form->setValidator('password', new QubitValidatorPassword(
            array(),
            array('invalid' => $this->context->i18n->__('Your password is not strong enough.'),
                  'min_length' => $this->context->i18n->__('Your password is not strong enough (too short).'))));
        }
        else
        {
          $this->form->setValidator('password', new sfValidatorString(array('required' => !isset($this->getRoute()->resource))));
        }

        $this->form->setWidget('password', new sfWidgetFormInputPassword);

      case 'confirmPassword':
        $this->form->setDefault('confirmPassword', null);
        $this->form->setValidator('confirmPassword', new sfValidatorString);
        $this->form->setWidget('confirmPassword', new sfWidgetFormInputPassword);

        break;
    }
  }

  protected function processField($field)
  {
    switch ($name = $field->getName())
    {
      case 'confirmPassword':
        // Don't do anything for confirmPassword
        break;

      case 'password':

        if (0 < strlen(trim($this->form->getValue('password'))))
        {
          $this->resource->setPassword($this->form->getValue('password'));
        }

        break;
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
        $this->processForm();

        $this->resource->save();

        $this->redirect(array($this->resource, 'module' => 'user'));
      }
    }
  }
}
