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
 * Global settings.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class SettingsGlobalAction extends sfAction
{
    public function execute($request)
    {
        $this->globalForm = new SettingsGlobalForm();

        // Handle POST data (form submit)
        if ($request->isMethod('post')) {
            QubitCache::getInstance()->removePattern('settings:i18n:*');

            // Global settings form submission
            if (null !== $request->global_settings) {
                // Hack to populate "version" field so it displays
                // if validation fails. By default, their values are not included in
                // $request->parameterHolder (and thus are not bound) because their
                // <input> field is disabled.
                $version = (null !== $setting = QubitSetting::getByName('version')) ? $setting->getValue(['sourceCulture' => true]) : null;
                $this->globalForm->bind(array_merge($request->global_settings, ['version' => $version]));
                if ($this->globalForm->isValid()) {
                    // Do update and redirect to avoid repeat submit wackiness
                    $this->updateGlobalSettings();

                    $notice = sfContext::getInstance()->i18n->__('Global settings saved.');
                    $this->getUser()->setFlash('notice', $notice);

                    $this->redirect('settings/global');
                }
            }
        }

        $this->populateGlobalForm();
    }

    /**
     * Populate the Global form with database values (non-localized).
     */
    protected function populateGlobalForm()
    {
        // Get global settings
        $version = qubitConfiguration::VERSION;
        if (null !== $setting = QubitSetting::getByName('version')) {
            $version .= ' - '.$setting->getValue(['sourceCulture' => true]);
        }

        $checkForUpdates = QubitSetting::getByName('check_for_updates');
        $hitsPerPage = QubitSetting::getByName('hits_per_page');
        $escapeQueries = QubitSetting::getByName('escape_queries');
        $sortBrowserUser = QubitSetting::getByName('sort_browser_user');
        $sortBrowserAnonymous = QubitSetting::getByName('sort_browser_anonymous');
        $defaultRepositoryView = QubitSetting::getByName('default_repository_browse_view');
        $defaultArchivalDescriptionView = QubitSetting::getByName('default_archival_description_browse_view');
        $multiRepository = QubitSetting::getByName('multi_repository');
        $auditLogEnabled = QubitSetting::getByName('audit_log_enabled');
        $showTooltips = QubitSetting::getByName('show_tooltips');
        $defaultPubStatus = QubitSetting::getByName('defaultPubStatus');
        $draftNotificationEnabled = QubitSetting::getByName('draft_notification_enabled');
        $swordDepositDir = QubitSetting::getByName('sword_deposit_dir');
        $googleMapsApiKey = QubitSetting::getByName('google_maps_api_key');
        $slugTypeInformationObject = QubitSetting::getByName('slug_basis_informationobject');
        $permissiveSlugCreation = QubitSetting::getByName('permissive_slug_creation');
        $generateReportsAsPubUser = QubitSetting::getByName('generate_reports_as_pub_user');
        $enableInstitutionalScoping = QubitSetting::getByName('enable_institutional_scoping');
        $cacheXmlOnSave = QubitSetting::getByName('cache_xml_on_save');

        // Set defaults for global form
        $this->globalForm->setDefaults([
            'version' => $version,
            'check_for_updates' => (isset($checkForUpdates)) ? intval($checkForUpdates->getValue(['sourceCulture' => true])) : 1,
            'hits_per_page' => (isset($hitsPerPage)) ? $hitsPerPage->getValue(['sourceCulture' => true]) : null,
            'escape_queries' => (isset($escapeQueries)) ? $escapeQueries->getValue(['sourceCulture' => true]) : null,
            'sort_browser_user' => (isset($sortBrowserUser)) ? $sortBrowserUser->getValue(['sourceCulture' => true]) : 0,
            'sort_browser_anonymous' => (isset($sortBrowserAnonymous)) ? $sortBrowserAnonymous->getValue(['sourceCulture' => true]) : 0,
            'default_repository_browse_view' => (isset($defaultRepositoryView)) ? $defaultRepositoryView->getValue(['sourceCulture' => true]) : 'card',
            'default_archival_description_browse_view' => (isset($defaultArchivalDescriptionView)) ? $defaultArchivalDescriptionView->getValue(['sourceCulture' => true]) : 'table',
            'multi_repository' => (isset($multiRepository)) ? intval($multiRepository->getValue(['sourceCulture' => true])) : 1,
            'audit_log_enabled' => (isset($auditLogEnabled)) ? intval($auditLogEnabled->getValue(['sourceCulture' => true])) : 0,
            'slug_basis_informationobject' => (isset($slugTypeInformationObject)) ? intval($slugTypeInformationObject->getValue(['sourceCulture' => true])) : QubitSlug::SLUG_BASIS_TITLE,
            'permissive_slug_creation' => (isset($permissiveSlugCreation)) ? intval($permissiveSlugCreation->getValue(['sourceCulture' => true])) : QubitSlug::SLUG_RESTRICTIVE,
            'show_tooltips' => (isset($showTooltips)) ? intval($showTooltips->getValue(['sourceCulture' => true])) : 1,
            'defaultPubStatus' => (isset($defaultPubStatus)) ? $defaultPubStatus->getValue(['sourceCulture' => true]) : QubitTerm::PUBLICATION_STATUS_DRAFT_ID,
            'draft_notification_enabled' => (isset($draftNotificationEnabled)) ? intval($draftNotificationEnabled->getValue(['sourceCulture' => true])) : 0,
            'sword_deposit_dir' => (isset($swordDepositDir)) ? $swordDepositDir->getValue(['sourceCulture' => true]) : null,
            'google_maps_api_key' => (isset($googleMapsApiKey)) ? $googleMapsApiKey->getValue(['sourceCulture' => true]) : null,
            'generate_reports_as_pub_user' => (isset($generateReportsAsPubUser)) ? $generateReportsAsPubUser->getValue(['sourceCulture' => true]) : 1,
            'enable_institutional_scoping' => (isset($enableInstitutionalScoping)) ? intval($enableInstitutionalScoping->getValue(['sourceCulture' => true])) : 0,
            'cache_xml_on_save' => (isset($cacheXmlOnSave)) ? intval($cacheXmlOnSave->getValue(['sourceCulture' => true])) : 0,
        ]);
    }

    /**
     * Update the global settings in database (non-localized).
     */
    protected function updateGlobalSettings()
    {
        $thisForm = $this->globalForm;

        if (null !== $generateReportsAsPubUser = $thisForm->getValue('generate_reports_as_pub_user')) {
            $setting = QubitSetting::getByName('generate_reports_as_pub_user');
            $setting->setValue($generateReportsAsPubUser, ['sourceCulture' => true]);
            $setting->save();
        }

        // Check for updates
        if (null !== $checkForUpdates = $thisForm->getValue('check_for_updates')) {
            $setting = QubitSetting::getByName('check_for_updates');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($checkForUpdates, ['sourceCulture' => true]);
            $setting->save();
        }

        // Hits per page
        if (null !== $hitsPerPage = $thisForm->getValue('hits_per_page')) {
            if (intval($hitsPerPage) && $hitsPerPage > 0) {
                $setting = QubitSetting::getByName('hits_per_page');

                // Force sourceCulture update to prevent discrepency in settings between cultures
                $setting->setValue($hitsPerPage, ['sourceCulture' => true]);
                $setting->save();
            }
        }

        // Escape queries, add setting if it's not already created (to avoid adding it in a migration)
        if (null === $setting = QubitSetting::getByName('escape_queries')) {
            $setting = QubitSetting::createNewSetting('escape_queries', null);
        }

        // Force sourceCulture update to prevent discrepency in settings between cultures
        $setting->setValue($thisForm->getValue('escape_queries'), ['sourceCulture' => true]);
        $setting->save();

        // Sort Browser (for users)
        if (null !== $sortBrowserUser = $thisForm->getValue('sort_browser_user')) {
            $setting = QubitSetting::getByName('sort_browser_user');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($sortBrowserUser, ['sourceCulture' => true]);
            $setting->save();
        }

        // Sort Browser (for anonymous)
        if (null !== $sortBrowserAnonymous = $thisForm->getValue('sort_browser_anonymous')) {
            $setting = QubitSetting::getByName('sort_browser_anonymous');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($sortBrowserAnonymous, ['sourceCulture' => true]);
            $setting->save();
        }

        // Default repository browse page view
        if (null !== $defaultRepositoryView = $thisForm->getValue('default_repository_browse_view')) {
            $setting = QubitSetting::getByName('default_repository_browse_view');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($defaultRepositoryView, ['sourceCulture' => true]);
            $setting->save();
        }

        // Default archival description browse page view
        if (null !== $defaultArchivalDescriptionView = $thisForm->getValue('default_archival_description_browse_view')) {
            $setting = QubitSetting::getByName('default_archival_description_browse_view');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($defaultArchivalDescriptionView, ['sourceCulture' => true]);
            $setting->save();
        }

        // Multi-repository radio button
        if (null !== $multiRepositoryValue = $thisForm->getValue('multi_repository')) {
            $setting = QubitSetting::getByName('multi_repository');

            // Add setting if it's not already in the sampleData.yml file for
            // backwards compatiblity with v1.0.3 sampleData.yml file
            if (null === $setting) {
                $setting = QubitSetting::createNewSetting('multi_repository', null, ['deleteable' => false]);
            }

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($multiRepositoryValue, ['sourceCulture' => true]);
            $setting->save();
        }

        // Audit log enabled
        if (null !== $auditLogEnabled = $thisForm->getValue('audit_log_enabled')) {
            if (null === $setting = QubitSetting::getByName('audit_log_enabled')) {
                $setting = new QubitSetting();
                $setting->name = 'audit_log_enabled';
            }

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($auditLogEnabled, ['sourceCulture' => true]);
            $setting->save();
        }

        if (null !== $slugTypeInformationObject = $thisForm->getValue('slug_basis_informationobject')) {
            $setting = QubitSetting::getByName('slug_basis_informationobject');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($slugTypeInformationObject, ['sourceCulture' => true]);
            $setting->save();
        }

        if (null !== $permissiveSlugCreation = $thisForm->getValue('permissive_slug_creation')) {
            $setting = QubitSetting::getByName('permissive_slug_creation');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($permissiveSlugCreation, ['sourceCulture' => true]);
            $setting->save();
        }

        // Show tooltips
        if (null !== $showTooltips = $thisForm->getValue('show_tooltips')) {
            $setting = QubitSetting::getByName('show_tooltips');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($showTooltips, ['sourceCulture' => true]);
            $setting->save();
        }

        // Default publication status
        if (null !== $defaultPubStatus = $thisForm->getValue('defaultPubStatus')) {
            $setting = QubitSetting::getByName('defaultPubStatus');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($defaultPubStatus, ['sourceCulture' => true]);
            $setting->save();
        }

        // Total drafts notification enabled
        if (null !== $draftNotificationEnabled = $thisForm->getValue('draft_notification_enabled')) {
            if (null === $setting = QubitSetting::getByName('draft_notification_enabled')) {
                $setting = new QubitSetting();
                $setting->name = 'draft_notification_enabled';
            }

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($draftNotificationEnabled, ['sourceCulture' => true]);
            $setting->save();
        }

        // SWORD deposit directory
        if (null !== $swordDepositDir = $thisForm->getValue('sword_deposit_dir')) {
            $setting = QubitSetting::getByName('sword_deposit_dir');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($swordDepositDir, ['sourceCulture' => true]);
            $setting->save();
        }

        // Google Maps Javascript API key
        $googleMapsApiKey = $thisForm->getValue('google_maps_api_key');

        if (null === $setting = QubitSetting::getByName('google_maps_api_key')) {
            $setting = new QubitSetting();
            $setting->name = 'google_maps_api_key';
        }

        // Force sourceCulture update to prevent discrepency in settings between cultures
        $setting->setValue($googleMapsApiKey, ['sourceCulture' => true]);
        $setting->save();

        // Enable Institutional Scoping
        if (null !== $enableInstitutionalScoping = $thisForm->getValue('enable_institutional_scoping')) {
            $setting = QubitSetting::getByName('enable_institutional_scoping');

            // Force sourceCulture update to prevent discrepency in settings between cultures
            $setting->setValue($enableInstitutionalScoping, ['sourceCulture' => true]);
            $setting->save();
        }

        // Cache XML on save
        $cacheXmlOnSave = $thisForm->getValue('cache_xml_on_save');

        if (null === $setting = QubitSetting::getByName('cache_xml_on_save')) {
            $setting = new QubitSetting();
            $setting->name = 'cache_xml_on_save';
        }

        $setting->setValue($cacheXmlOnSave, ['sourceCulture' => true]);
        $setting->save();

        return $this;
    }
}
