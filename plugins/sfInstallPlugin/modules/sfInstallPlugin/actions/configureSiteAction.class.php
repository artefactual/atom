<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class sfInstallPluginConfigureSiteAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->form->setValidator('confirmPassword', new sfValidatorString(array('required' => true)));
    $this->form->setWidget('confirmPassword', new sfWidgetFormInputPassword);

    $this->form->setValidator('email', new sfValidatorEmail(array('required' => true)));
    $this->form->setWidget('email', new sfWidgetFormInput);

    $this->form->setValidator('password', new sfValidatorString(array('required' => true)));
    $this->form->setWidget('password', new sfWidgetFormInputPassword);

    $this->form->setValidator('siteDescription', new sfValidatorString);
    $this->form->setWidget('siteDescription', new sfWidgetFormInput);

    $this->form->setValidator('siteTitle', new sfValidatorString(array('required' => true)));
    $this->form->setWidget('siteTitle', new sfWidgetFormInput);

    $this->form->setValidator('username', new sfValidatorString(array('required' => true)));
    $this->form->setWidget('username', new sfWidgetFormInput);

    $this->form->getValidatorSchema()->setPostValidator(new sfValidatorSchemaCompare('password', '==', 'confirmPassword'));

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $setting = new QubitSetting;
        $setting->name = 'siteTitle';
        $setting->value = $this->form->getValue('siteTitle');
        $setting->save();

        $setting = new QubitSetting;
        $setting->name = 'siteDescription';
        $setting->value = $this->form->getValue('siteDescription');
        $setting->save();

        $user = new QubitUser;
        $user->username = $this->form->getValue('username');
        $user->email = $this->form->getValue('email');
        $user->setPassword($this->form->getValue('password'));
        $user->save();

        $aclUserGroup = new QubitAclUserGroup;
        $aclUserGroup->userId = $user->id;
        $aclUserGroup->groupId = QubitAclGroup::ADMINISTRATOR_ID;
        $aclUserGroup->save();

        $this->context->user->signOut();
        $this->context->user->authenticate($this->form->getValue('email'), $this->form->getValue('password'));

        $this->redirect(array('module' => 'sfInstallPlugin', 'action' => 'clearCache'));
      }
    }
  }
}
