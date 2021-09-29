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
 * Permissions.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class SettingsPermissionsAction extends sfAction
{
    public function execute($request)
    {
        $this->permissionsForm = new SettingsPermissionsForm();
        $this->permissionsAccessStatementsForm = new SettingsPermissionsAccessStatementsForm();
        $this->permissionsCopyrightStatementForm = new SettingsPermissionsCopyrightStatementForm();
        $this->permissionsPreservationSystemAccessStatementForm = new SettingsPermissionsPreservationSystemAccessStatementForm();

        $this->basis = [];
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item) {
            $this->basis[$item->slug] = $item->getName(['cultureFallback' => true]);
        }

        $this->copyrightStatementSetting = QubitSetting::getByName('digitalobject_copyright_statement');
        $this->preservationSystemAccessStatementSetting = QubitSetting::getByName(
            'digitalobject_preservation_system_access_statement'
        );

        $this->response->addJavaScript('permissionsSettings', 'last');

        // Handle POST data (form submit)
        if ($request->isMethod('post')) {
            // Give the user the ability to preview the copyright statement before
            // we persist the changes. We are reusing the viewCopyrightStatement
            // template, populating the properties that are needed.
            if ($request->hasParameter('preview')) {
                $this->setTemplate('viewCopyrightStatement', 'digitalobject');

                $this->preview = true;
                $this->resource = new QubitInformationObject();

                $this->permissionsCopyrightStatementForm->bind($request->getPostParameters());
                $statementData = $this->permissionsCopyrightStatementForm['copyrightStatement']->getValue();
                $this->copyrightStatement = QubitHtmlPurifier::getInstance()->purify($statementData['copyrightStatement']);

                return sfView::SUCCESS;
            }

            QubitCache::getInstance()->removePattern('settings:i18n:*');

            $values = $request->getPostParameters();
            $this->permissionsForm->bind($values['permissions']);
            $this->permissionsAccessStatementsForm->bind($values['accessStatements']);
            $this->permissionsCopyrightStatementForm->bind($values['copyrightStatement']);
            $this->permissionsPreservationSystemAccessStatementForm->bind($values['preservationSystemAccessStatement']);

            // Validate all forms at once and avoid redirection to show global errors
            if (
                !$this->permissionsForm->isValid()
                || !$this->permissionsAccessStatementsForm->isValid()
                || !$this->permissionsCopyrightStatementForm->isValid()
                || !$this->permissionsPreservationSystemAccessStatementForm->isValid()
            ) {
                return;
            }

            // PREMIS access permissions
            $premisAccessRight = QubitSetting::getByName('premisAccessRight');
            $premisAccessRight->setValue($this->permissionsForm->getValue('granted_right'), ['sourceCulture' => true]);
            $premisAccessRight->save();

            $premisAccessRightValues = QubitSetting::getByName('premisAccessRightValues');
            $premisAccessRightValues->setValue(serialize($this->permissionsForm->getValue('permissions')), ['sourceCulture' => true]);
            $premisAccessRightValues->save();

            // PREMIS access statements
            $accessValues = $this->permissionsAccessStatementsForm->getValues();

            foreach ($accessValues as $key => $value) {
                $setting = QubitSetting::getByNameAndScope($key, 'access_statement');
                if (null === $setting) {
                    $setting = new QubitSetting();
                    $setting->name = $key;
                    $setting->scope = 'access_statement';
                }
                $setting->setValue($value);
                $setting->save();
            }

            // Remove unused settings (e.g. a term of the basis taxonomy was
            // deleted). We use array_key_exists because isset() returns false
            // if the key is defined but its value is NULL.
            foreach (QubitSetting::getByScope('access_statement') as $setting) {
                if (!array_key_exists($setting->name, $accessValues)) {
                    $setting->delete();
                }
            }

            // Copyright statement
            $setting = QubitSetting::getByName('digitalobject_copyright_statement_enabled');
            if (null === $setting) {
                $setting = new QubitSetting();
                $setting->name = 'digitalobject_copyright_statement_enabled';
                $setting->sourceCulture = sfConfig::get('sf_default_culture');
            }
            $setting->setValue($this->permissionsCopyrightStatementForm->getValue('copyrightStatementEnabled'), ['sourceCulture' => true]);
            $setting->save();

            $statement = $this->permissionsCopyrightStatementForm->getValue('copyrightStatement');
            $statement = QubitHtmlPurifier::getInstance()->purify($statement);

            if (!empty($statement)) {
                $setting = QubitSetting::getByName('digitalobject_copyright_statement');
                if (null === $setting) {
                    $setting = new QubitSetting();
                    $setting->name = 'digitalobject_copyright_statement';
                }
                $setting->setValue($statement);
                $setting->save();
            }

            $setting = QubitSetting::getByName('digitalobject_copyright_statement_apply_globally');
            if (null === $setting) {
                $setting = new QubitSetting();
                $setting->name = 'digitalobject_copyright_statement_apply_globally';
                $setting->sourceCulture = sfConfig::get('sf_default_culture');
            }
            $value = $this->permissionsCopyrightStatementForm->getValue('copyrightStatementApplyGlobally');
            if (!$this->permissionsCopyrightStatementForm->getValue('copyrightStatementEnabled')) {
                // Disable applying global copyright if the main setting is disabled too
                $value = '0';
            }
            $setting->setValue($value, ['sourceCulture' => true]);
            $setting->save();

            // Preservation system access statement
            $setting = QubitSetting::getByName('digitalobject_preservation_system_access_statement_enabled');
            if (null === $setting) {
                $setting = new QubitSetting();
                $setting->name = 'digitalobject_preservation_system_access_statement_enabled';
                $setting->sourceCulture = sfConfig::get('sf_default_culture');
            }
            $setting->setValue(
                $this->permissionsPreservationSystemAccessStatementForm->getValue(
                    'preservationSystemAccessStatementEnabled'
                ),
                ['sourceCulture' => true]
            );
            $setting->save();

            $statement = $this->permissionsPreservationSystemAccessStatementForm->getValue(
                'preservationSystemAccessStatement'
            );
            $statement = QubitHtmlPurifier::getInstance()->purify($statement);

            if (!empty($statement)) {
                $setting = QubitSetting::getByName('digitalobject_preservation_system_access_statement');
                if (null === $setting) {
                    $setting = new QubitSetting();
                    $setting->name = 'digitalobject_preservation_system_access_statement';
                }
                $setting->setValue($statement);
                $setting->save();
            }

            $notice = sfContext::getInstance()->i18n->__('Permissions saved.');
            $this->getUser()->setFlash('notice', $notice);

            $this->redirect('settings/permissions');
        }
    }
}
