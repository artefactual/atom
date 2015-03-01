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

class MenuUserMenuComponent extends sfComponent
{
  public function execute($request)
  {
    if (sfConfig::get('app_read_only', false))
    {
      return sfView::NONE;
    }

    $this->form = new sfForm;

    $this->form->setValidator('next', new sfValidatorString);
    $this->form->setWidget('next', new sfWidgetFormInputHidden);
    $this->form->setDefault('next', $request->getUri());

    $this->form->setValidator('email', new sfValidatorEmail(array('required' => true), array(
      'required' => $this->context->i18n->__('You must enter your email address'),
      'invalid' => $this->context->i18n->__('This isn\'t a valid email address'))));
    $this->form->setWidget('email', new sfWidgetFormInput);

    $this->form->setValidator('password', new sfValidatorString(array('required' => true), array(
      'required' => $this->context->i18n->__('You must enter your password'))));
    $this->form->setWidget('password', new sfWidgetFormInputPassword);

    if ($this->context->user->isAuthenticated())
    {
      $this->gravatar = sprintf('https://www.gravatar.com/avatar/%s?s=%s',
        md5(strtolower(trim($this->context->user->user->email))),
        25,
        urlencode(public_path('/images/gravatar-anonymous.png', false)));
    }
  }
}
