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

class SettingsDigitalObjectDerivativesAction extends SettingsEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'digital_object_derivatives_pdf_page_number',
        'reference_image_maxwidth',
    ];

    protected static $refImageMaxWidthMin = 100;
    protected static $refImageMaxWidthMax = 2000;

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('Digital object derivative settings saved.');

        // Relay info to template
        $this->pdfinfoAvailable = sfImageMagickAdapter::pdfinfoToolAvailable();
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'digital_object_derivatives_pdf_page_number':
                $this->form->setValidator($name, new sfValidatorInteger(['min' => 1]));
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            case 'reference_image_maxwidth':
                $this->form->setValidator($name, new sfValidatorInteger(
                    ['min' => self::$refImageMaxWidthMin, 'max' => self::$refImageMaxWidthMax],
                    ['min' => $this->i18n->__('This value must be at least %min% pixels'), 'max' => $this->i18n->__('This value can not be greater than %max% pixels')]
                ));
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;
        }
    }
}
