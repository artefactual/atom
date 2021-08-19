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
 * Add extra form features to sfForm.
 */
class QubitForm extends sfForm
{
    /**
     * Embeds a sfForm into the current form.
     *
     * Added to sfForm method: update embedded form widget name format to avoid
     * name clashes with current form widgets.
     *
     * @param string $name      The field name
     * @param sfForm $form      A sfForm instance
     * @param string $decorator A HTML decorator for the embedded form
     */
    public function embedForm($name, sfForm $form, $decorator = null)
    {
        parent::embedForm($name, $form, $decorator);

        // Concatenate parent HTML element name format with embedded form name
        if ('%s' === $this->getWidgetSchema()->getNameFormat()) {
            // e.g. embeddedFormName[%s]
            $nameFormat = $name.'[%s]';
        } else {
            // e.g. parentFormName[embeddedFormName][%s]
            $nameFormat = sprintf(
                '%s[%s][%%s]',
                str_replace(
                    '[%s]', '', $this->getWidgetSchema()->getNameFormat()
                ),
                $name
            );
        }

        $form->getWidgetSchema()->setNameFormat($nameFormat);
    }

    /**
     * Binds the form with input values.
     *
     * Added to sfForm method: Bind embedded form values recursively.
     *
     * @param array $taintedValues An array of input values
     * @param array $taintedFiles  An array of uploaded files (in the $_FILES
     *                             or $_GET format)
     */
    public function bind(
        array $taintedValues = null,
        array $taintedFiles = null
    ) {
        parent::bind($taintedValues, $taintedFiles);

        if (!empty($this->embeddedForms)) {
            $this->bindEmbeddedForms($taintedValues, $taintedFiles);
        }
    }

    /**
     * Recursively bind input data to embedded forms.
     *
     * @param array $taintedValues An array of input values
     * @param array $taintedFiles  An array of uploaded files (in the $_FILES
     *                             or $_GET format)
     */
    public function bindEmbeddedForms(
        array $taintedValues = null,
        array $taintedFiles = null
    ) {
        foreach ($this->embeddedForms as $name => $form) {
            if (
                isset($taintedValues[$name])
                || isset($taintedFiles[$name])
            ) {
                // Bind relevant input values to embedded form
                $form->bind($taintedValues[$name], $taintedFiles[$name]);
            } else {
                // Delete the embedded form if there is no POST data associated
                // with it (i.e. it was removed from the form)
                unset($this->embeddedForms[$name]);
            }
        }
    }

    /**
     * Recursively check validity of this form and embedded forms.
     */
    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }

        // Check validity of embedded forms
        if (!empty($this->embeddedForms)) {
            foreach ($this->embeddedForms as $name => $form) {
                if (!$form->isValid()) {
                    return false;
                }
            }
        }

        return true;
    }
}
