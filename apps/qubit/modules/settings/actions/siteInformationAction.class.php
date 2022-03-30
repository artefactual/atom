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
 * Site information.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class SettingsSiteInformationAction extends SettingsEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'siteTitle',
        'siteDescription',
        'siteBaseUrl',
    ];
    public static $I18N = [
        'siteTitle',
        'siteDescription',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('Site information saved.');

        // Set form decorator
        if (!sfConfig::get('app_b5_theme', false)) {
            $decorator = new QubitWidgetFormSchemaFormatterList($this->form->getWidgetSchema());
            $this->form->getWidgetSchema()->addFormFormatter('list', $decorator);
            $this->form->getWidgetSchema()->setFormFormatterName('list');
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'siteTitle':
                $this->form->setWidget($name, new sfWidgetFormInput());
                $this->form->setValidator($name, new sfValidatorString(['required' => false]));
                $this->form->getWidgetSchema()->{$name}->setLabel($this->i18n->__('Site title'));

                break;

            case 'siteDescription':
                $this->form->setWidget($name, new sfWidgetFormInput());
                $this->form->setValidator($name, new sfValidatorString(['required' => false]));
                $this->form->getWidgetSchema()->{$name}->setLabel($this->i18n->__('Site description'));

                break;

            case 'siteBaseUrl':
                $this->form->setWidget($name, new sfWidgetFormInput());
                $this->form->setValidator($name, new sfValidatorString(['required' => false]));
                $this->form->getWidgetSchema()->{$name}->setLabel(
                    $this->i18n->__('Site base URL (used in MODS and EAD exports)')
                );

                break;
        }
    }
}
