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
 * Edit menu.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class MenuEditAction extends sfAction
{
    public static $NAMES = [
        'name',
        'label',
        'parentId',
        'path',
        'description',
    ];

    public function processForm()
    {
        foreach ($this->form as $field) {
            if (isset($this->request[$field->getName()])) {
                $this->processField($field);
            }
        }

        return $this;
    }

    public function execute($request)
    {
        $this->form = new sfForm();
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        $this->menu = new QubitMenu();

        if (isset($request->id)) {
            $this->menu = QubitMenu::getById($request->id);

            if (!isset($this->menu)) {
                $this->forward404();
            }
        }

        foreach ($this::$NAMES as $name) {
            $this->addField($name);
        }

        // Handle POST data (form submit)
        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $this->processForm();

                $this->menu->save();

                // Remove cache
                if (null !== $this->context->getViewCacheManager()) {
                    $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_browseMenu&sf_cache_key=*');
                    $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_mainMenu&sf_cache_key=*');
                }

                $this->redirect(['module' => 'menu', 'action' => 'list']);
            }
        }

        QubitDescription::addAssets($this->response);
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'name':
                // Don't allow locked menus to be renamed
                if ($this->menu->isProtected()) {
                    break;
                }

                $this->form->setDefault($name, $this->menu[$name]);
                $this->form->setValidator($name, new QubitValidatorMenuName(['required' => true, 'resource' => $this->menu]));
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            case 'path':
                $this->form->setDefault($name, $this->menu[$name]);
                $pathRequired = (QubitMenu::ROOT_ID == $this->menu->parentId) ? false : true;
                $this->form->setValidator($name, new sfValidatorString(['required' => $pathRequired]));
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            case 'label':
                $this->form->setDefault($name, $this->menu[$name]);
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            case 'parentId':
                // Get menuTree array with menu depths
                $menuTree = QubitMenu::getTreeById(QubitMenu::ROOT_ID);

                // Build an array of choices for "parentId" select box (with blank line)
                $choices = [1 => '[ '.$this->context->i18n->__('Top').' ]'];
                foreach ($menuTree as $menu) {
                    $choices[$menu['id']] = str_repeat('-', $menu['depth']).' '.$menu['name'];
                }

                if (null !== $this->menu->parentId) {
                    $this->form->setDefault('parentId', $this->menu->parentId);
                }

                $this->form->setValidator('parentId', new sfValidatorString(['required' => true]));
                $this->form->setWidget('parentId', new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'description':
                $this->form->setDefault($name, $this->menu[$name]);
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormTextarea());

                break;
        }
    }

    protected function processField($field)
    {
        switch ($name = $field->getName()) {
            case 'parentId':
                if (null == $this->menu['parentId'] = $this->form->getValue('parentId')) {
                    $this->menu['parentId'] = QubitMenu::ROOT_ID;
                }

                break;

            default:
                // Don't allow locked menus to be renamed
                if ('name' == $name && $this->menu->isProtected()) {
                    break;
                }

                $this->menu[$field->getName()] = $this->form->getValue($field->getName());
        }
    }
}
