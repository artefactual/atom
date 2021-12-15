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
 * Finding Aid settings.
 */
class SettingsFindingAidAction extends sfAction
{
    /**
     * Main business/application logic.
     *
     * @see sfComponent::execute()
     *
     * @param sfWebRequest $request web request context
     */
    public function execute($request)
    {
        $this->findingAidForm = new SettingsFindingAidForm();

        // Handle POST data (form submit)
        if ($request->isMethod('post')) {
            $this->processForm($request);
        }

        $this->populateFindingAidForm();
    }

    /**
     * Get Finding Aid setting data from database or default value.
     *
     * @param string $name    QubitSetting column name
     * @param string $default default value if QubitSetting doesn't exist
     *
     * @return string QubitSetting or default value
     */
    public function getSetting(string $name, string $default = ''): string
    {
        $setting = QubitSetting::getByName($name);

        if (isset($setting)) {
            return $setting->getValue(['sourceCulture' => true]);
        }

        return $default;
    }

    /**
     * Save Finding Aid setting to database as QubitSetting.
     *
     * @param string $name  QubitSetting column name
     * @param string $value QubitSetting value to save
     */
    public function setSetting(string $name, ?string $value): void
    {
        if (null === $value) {
            return;
        }

        $setting = QubitSetting::findAndSave(
            $name,
            $value,
            [
                'sourceCulture' => true,
                'createNew' => true,
            ]
        );
    }

    /**
     * Handle Finding Aid form submission.
     *
     * @param sfWebRequest $request web request context
     */
    protected function processForm(sfWebRequest $request): void
    {
        // Delete settings_i18n cache
        QubitCache::getInstance()->removePattern('settings:i18n:*');

        if (null === $request->finding_aid) {
            return;
        }

        // Bind & validate form data
        $this->findingAidForm->bind($request->finding_aid);

        if (!$this->findingAidForm->isValid()) {
            $this->error = 'formInvalid';

            return;
        }

        // Save settings to database
        $this->saveFindingAidSettings();

        // Set flash notice to show after redirect
        $this->getUser()->setFlash(
            'notice',
            $this->getContext()->i18n->__('Finding aid settings saved.')
        );

        // Redirect to avoid repeat submit wackiness
        $this->redirect('settings/findingAid');
    }

    /**
     * Populate the Finding Aid form with database data.
     */
    protected function populateFindingAidForm()
    {
        $this->findingAidForm->setDefaults(
            [
                'finding_aids_enabled' => $this->getSetting(
                    'findingAidsEnabled', '1'
                ),
                'finding_aid_format' => $this->getSetting(
                    'findingAidFormat', 'pdf'
                ),
                'finding_aid_model' => $this->getSetting(
                    'findingAidModel', 'inventory-summary'
                ),
                'public_finding_aid' => $this->getSetting(
                    'publicFindingAid', '1'
                ),
            ]
        );
    }

    /**
     * Save the Finding Aid settings to the database.
     */
    protected function saveFindingAidSettings(): self
    {
        $thisForm = $this->findingAidForm;

        $this->setSetting(
            'findingAidsEnabled',
            $thisForm->getValue('finding_aids_enabled')
        );

        $this->setSetting(
            'findingAidFormat',
            $thisForm->getValue('finding_aid_format')
        );

        $this->setSetting(
            'findingAidModel',
            $thisForm->getValue('finding_aid_model')
        );

        $this->setSetting(
            'publicFindingAid',
            $thisForm->getValue('public_finding_aid')
        );

        return $this;
    }
}
