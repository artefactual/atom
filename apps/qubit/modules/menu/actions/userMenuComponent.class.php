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
        if (sfConfig::get('app_read_only', false)) {
            return sfView::NONE;
        }

        $this->form = new sfForm();

        $this->form->setValidator('next', new sfValidatorString());
        $this->form->setWidget('next', new sfWidgetFormInputHidden());
        $this->form->setDefault('next', $request->getUri());

        $this->form->setValidator('email', new sfValidatorEmail(['required' => true], [
            'required' => $this->context->i18n->__('You must enter your email address'),
            'invalid' => $this->context->i18n->__('This isn\'t a valid email address'),
        ]));
        $this->form->setWidget('email', new sfWidgetFormInput());

        $this->form->setValidator('password', new sfValidatorString(['required' => true], [
            'required' => $this->context->i18n->__('You must enter your password'),
        ]));
        $this->form->setWidget('password', new sfWidgetFormInputPassword());

        $this->showLogin = false;
        if ($this->context->user->isAuthenticated()) {
            $this->gravatar = sprintf(
                'https://www.gravatar.com/avatar/%s?s=%s',
                md5(strtolower(trim($this->context->user->user->email))),
                25,
                urlencode(public_path('/images/gravatar-anonymous.png', false))
            );

            $this->menuLabels = [
                'logout' => $this->getMenuLabel('logout'),
                'myProfile' => $this->getMenuLabel('myProfile'),
            ];
        } elseif (check_field_visibility('app_element_visibility_global_login_button')) {
            $this->showLogin = true;
            $this->menuLabels = ['login' => $this->getMenuLabel('login')];
        }
    }

    protected function getMenuLabel($name)
    {
        if (null !== $menu = QubitMenu::getByName($name)) {
            return $menu->getLabel(['cultureFallback' => true]);
        }

        switch ($name) {
            case 'login':
                return $this->context->getI18n()->__('Log in');

            case 'logout':
                return $this->context->getI18n()->__('Log out');

            case 'myProfile':
                return $this->context->getI18n()->__('Profile');
        }
    }
}
