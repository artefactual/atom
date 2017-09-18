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
 * Global settings
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsGlobalAction extends sfAction
{
  public function execute($request)
  {
    $this->globalForm = new SettingsGlobalForm;

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      // Global settings form submission
      if (null !== $request->global_settings)
      {
        // Hack to populate "version" field so it displays
        // if validation fails. By default, their values are not included in
        // $request->parameterHolder (and thus are not bound) because their
        // <input> field is disabled.
        $version = (null !== $setting = QubitSetting::getByName('version')) ? $setting->getValue(array('sourceCulture'=>true)) : null;
        $this->globalForm->bind(array_merge($request->global_settings, array('version'=>$version)));
        if ($this->globalForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateGlobalSettings();
          $this->redirect('settings/global');
        }
      }
    }

    $this->populateGlobalForm();
  }

  /**
   * Populate the Global form with database values (non-localized)
   */
  protected function populateGlobalForm()
  {
    // Get global settings
    $version = qubitConfiguration::VERSION;
    if (null !== $setting = QubitSetting::getByName('version'))
    {
      $version .= ' - '.$setting->getValue(array('sourceCulture' => true));
    }

    $checkForUpdates = QubitSetting::getByName('check_for_updates');
    $hitsPerPage = QubitSetting::getByName('hits_per_page');
    $accessionMaskEnabled = QubitSetting::getByName('accession_mask_enabled');
    $accessionMask = QubitSetting::getByName('accession_mask');
    $accessionCounter = QubitSetting::getByName('accession_counter');
    $identifierMaskEnabled = QubitSetting::getByName('identifier_mask_enabled');
    $identifierMask = QubitSetting::getByName('identifier_mask');
    $identifierCounter = QubitSetting::getByName('identifier_counter');
    $separatorCharacter = QubitSetting::getByName('separator_character');
    $inheritCodeInformationObject = QubitSetting::getByName('inherit_code_informationobject');
    $escapeQueries = QubitSetting::getByName('escape_queries');
    $sortBrowserUser = QubitSetting::getByName('sort_browser_user');
    $sortBrowserAnonymous = QubitSetting::getByName('sort_browser_anonymous');
    $defaultRepositoryView = QubitSetting::getByName('default_repository_browse_view');
    $defaultArchivalDescriptionView = QubitSetting::getByName('default_archival_description_browse_view');
    $multiRepository = QubitSetting::getByName('multi_repository');
    $repositoryQuota = QubitSetting::getByName('repository_quota');
    $explodeMultipageFiles = QubitSetting::getByName('explode_multipage_files');
    $showTooltips = QubitSetting::getByName('show_tooltips');
    $defaultPubStatus = QubitSetting::getByName('defaultPubStatus');
    $draftNotificationEnabled = QubitSetting::getByName('draft_notification_enabled');
    $swordDepositDir = QubitSetting::getByName('sword_deposit_dir');
    $googleMapsApiKey = QubitSetting::getByName('google_maps_api_key');
    $slugTypeInformationObject = QubitSetting::getByName('slug_basis_informationobject');
    $generateReportsAsPubUser = QubitSetting::getByName('generate_reports_as_pub_user');
    $enableInstitutionalScoping = QubitSetting::getByName('enable_institutional_scoping');
    $cacheXmlOnSave = QubitSetting::getByName('cache_xml_on_save');

    // Set defaults for global form
    $this->globalForm->setDefaults(array(
      'version' => $version,
      'check_for_updates' => (isset($checkForUpdates)) ? intval($checkForUpdates->getValue(array('sourceCulture'=>true))) : 1,
      'hits_per_page' => (isset($hitsPerPage)) ? $hitsPerPage->getValue(array('sourceCulture'=>true)) : null,
      'accession_mask_enabled' => (isset($accessionMaskEnabled)) ? intval($accessionMaskEnabled->getValue(array('sourceCulture'=>true))) : 1,
      'accession_mask' => (isset($accessionMask)) ? $accessionMask->getValue(array('sourceCulture'=>true)) : null,
      'accession_counter' => (isset($accessionCounter)) ? intval($accessionCounter->getValue(array('sourceCulture'=>true))) : 1,
      'identifier_mask_enabled' => (isset($identifierMaskEnabled)) ? intval($identifierMaskEnabled->getValue(array('sourceCulture'=>true))) : 1,
      'identifier_mask' => (isset($identifierMask)) ? $identifierMask->getValue(array('sourceCulture'=>true)) : null,
      'identifier_counter' => (isset($identifierCounter)) ? intval($identifierCounter->getValue(array('sourceCulture'=>true))) : 1,
      'separator_character' => (isset($separatorCharacter)) ? $separatorCharacter->getValue(array('sourceCulture'=>true)) : null,
      'inherit_code_informationobject' => (isset($inheritCodeInformationObject)) ? intval($inheritCodeInformationObject->getValue(array('sourceCulture'=>true))) : 1,
      'escape_queries' => (isset($escapeQueries)) ? $escapeQueries->getValue(array('sourceCulture'=>true)) : null,
      'sort_browser_user' => (isset($sortBrowserUser)) ? $sortBrowserUser->getValue(array('sourceCulture'=>true)) : 0,
      'sort_browser_anonymous' => (isset($sortBrowserAnonymous)) ? $sortBrowserAnonymous->getValue(array('sourceCulture'=>true)) : 0,
      'default_repository_browse_view' => (isset($defaultRepositoryView)) ? $defaultRepositoryView->getValue(array('sourceCulture' => true)) : 'card',
      'default_archival_description_browse_view' => (isset($defaultArchivalDescriptionView)) ? $defaultArchivalDescriptionView->getValue(array('sourceCulture' => true)) : 'table',
      'multi_repository' => (isset($multiRepository)) ? intval($multiRepository->getValue(array('sourceCulture'=>true))) : 1,
      'repository_quota' => (isset($repositoryQuota)) ? $repositoryQuota->getValue(array('sourceCulture'=>true)) : 0,
      'explode_multipage_files' => (isset($explodeMultipageFiles)) ? intval($explodeMultipageFiles->getValue(array('sourceCulture'=>true))) : 1,
      'slug_basis_informationobject' => (isset($slugTypeInformationObject)) ? intval($slugTypeInformationObject->getValue(array('sourceCulture'=>true))) : QubitSlug::SLUG_BASIS_TITLE,
      'show_tooltips' => (isset($showTooltips)) ? intval($showTooltips->getValue(array('sourceCulture'=>true))) : 1,
      'defaultPubStatus' => (isset($defaultPubStatus)) ? $defaultPubStatus->getValue(array('sourceCulture'=>true)) : QubitTerm::PUBLICATION_STATUS_DRAFT_ID,
      'draft_notification_enabled' => (isset($draftNotificationEnabled)) ? intval($draftNotificationEnabled->getValue(array('sourceCulture'=>true))) : 0,
      'sword_deposit_dir' => (isset($swordDepositDir)) ? $swordDepositDir->getValue(array('sourceCulture'=>true)) : null,
      'google_maps_api_key' => (isset($googleMapsApiKey)) ? $googleMapsApiKey->getValue(array('sourceCulture'=>true)) : null,
      'generate_reports_as_pub_user' => (isset($generateReportsAsPubUser)) ? $generateReportsAsPubUser->getValue(array('sourceCulture'=>true)) : 1,
      'enable_institutional_scoping' => (isset($enableInstitutionalScoping)) ? intval($enableInstitutionalScoping->getValue(array('sourceCulture'=>true))) : 0,
      'cache_xml_on_save' => (isset($cacheXmlOnSave)) ? intval($cacheXmlOnSave->getValue(array('sourceCulture'=>true))) : 0,
    ));
  }

  /**
   * Update the global settings in database (non-localized)
   */
  protected function updateGlobalSettings()
  {
    $thisForm = $this->globalForm;

    if (null !== $generateReportsAsPubUser = $thisForm->getValue('generate_reports_as_pub_user'))
    {
      $setting = QubitSetting::getByName('generate_reports_as_pub_user');
      $setting->setValue($generateReportsAsPubUser, array('sourceCulture' => true));
      $setting->save();
    }

    // Check for updates
    if (null !== $checkForUpdates = $thisForm->getValue('check_for_updates'))
    {
      $setting = QubitSetting::getByName('check_for_updates');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($checkForUpdates, array('sourceCulture' => true));
      $setting->save();
    }

    // Hits per page
    if (null !== $hitsPerPage = $thisForm->getValue('hits_per_page'))
    {
      if (intval($hitsPerPage) && $hitsPerPage > 0)
      {
        $setting = QubitSetting::getByName('hits_per_page');

        // Force sourceCulture update to prevent discrepency in settings between cultures
        $setting->setValue($hitsPerPage, array('sourceCulture'=>true));
        $setting->save();
      }
    }

    // Accession mask enabled
    if (null !== $accessionMaskEnabled = $thisForm->getValue('accession_mask_enabled'))
    {
      if (null === $setting = QubitSetting::getByName('accession_mask_enabled'))
      {
        $setting = new QubitSetting;
        $setting->name = 'accession_mask_enabled';
      }

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($accessionMaskEnabled, array('sourceCulture' => true));
      $setting->save();
    }

    // Accession mask
    if (null !== $accessionMask = $thisForm->getValue('accession_mask'))
    {
      $setting = QubitSetting::getByName('accession_mask');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($accessionMask, array('sourceCulture' => true));
      $setting->save();
    }

    // Accession counter
    if (null !== $accessionCounter = $thisForm->getValue('accession_counter'))
    {
      if (ctype_digit($accessionCounter))
      {
        $setting = QubitSetting::getByName('accession_counter');

        // Force sourceCulture update to prevent discrepency in settings between cultures
        $setting->setValue($accessionCounter, array('sourceCulture' => true));
        $setting->save();
      }
    }

    // Identifier mask enabled
    if (null !== $identifierMaskEnabled = $thisForm->getValue('identifier_mask_enabled'))
    {
      if (null !== $setting = QubitSetting::getByName('identifier_mask_enabled'))
      {
        // Force sourceCulture update to prevent discrepency in settings between cultures
        $setting->setValue($identifierMaskEnabled, array('sourceCulture' => true));
        $setting->save();
      }
    }

    // Identifier mask
    if (null !== $identifierMask = $thisForm->getValue('identifier_mask'))
    {
      $setting = QubitSetting::getByName('identifier_mask');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($identifierMask, array('sourceCulture' => true));
      $setting->save();
    }

    // Identifier counter
    if (null !== $identifierCounter = $thisForm->getValue('identifier_counter'))
    {
      if (ctype_digit($identifierCounter))
      {
        $setting = QubitSetting::getByName('identifier_counter');

        // Force sourceCulture update to prevent discrepency in settings between cultures
        $setting->setValue($identifierCounter, array('sourceCulture' => true));
        $setting->save();
      }
    }

    // Separator character
    if (null !== $separatorCharacter = $thisForm->getValue('separator_character'))
    {
      $setting = QubitSetting::getByName('separator_character');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($separatorCharacter, array('sourceCulture' => true));
      $setting->save();
    }

    // Inherit Code (Information Object)
    if (null !== $inheritCodeInformationObjectValue = $thisForm->getValue('inherit_code_informationobject'))
    {
      $setting = QubitSetting::getByName('inherit_code_informationobject');

       // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($inheritCodeInformationObjectValue, array('sourceCulture'=>true));
      $setting->save();
    }

    // Escape queries, add setting if it's not already created (to avoid adding it in a migration)
    if (null === $setting = QubitSetting::getByName('escape_queries'))
    {
      $setting = QubitSetting::createNewSetting('escape_queries', null);
    }

    // Force sourceCulture update to prevent discrepency in settings between cultures
    $setting->setValue($thisForm->getValue('escape_queries'), array('sourceCulture' => true));
    $setting->save();

    // Sort Browser (for users)
    if (null !== $sortBrowserUser = $thisForm->getValue('sort_browser_user'))
    {
      $setting = QubitSetting::getByName('sort_browser_user');

       // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($sortBrowserUser, array('sourceCulture'=>true));
      $setting->save();
    }

    // Sort Browser (for anonymous)
    if (null !== $sortBrowserAnonymous = $thisForm->getValue('sort_browser_anonymous'))
    {
      $setting = QubitSetting::getByName('sort_browser_anonymous');

       // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($sortBrowserAnonymous, array('sourceCulture'=>true));
      $setting->save();
    }

    // Default repository browse page view
    if (null !== $defaultRepositoryView = $thisForm->getValue('default_repository_browse_view'))
    {
      $setting = QubitSetting::getByName('default_repository_browse_view');

       // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($defaultRepositoryView, array('sourceCulture'=>true));
      $setting->save();
    }

    // Default archival description browse page view
    if (null !== $defaultArchivalDescriptionView = $thisForm->getValue('default_archival_description_browse_view'))
    {
      $setting = QubitSetting::getByName('default_archival_description_browse_view');

       // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($defaultArchivalDescriptionView, array('sourceCulture'=>true));
      $setting->save();
    }

    // Multi-repository radio button
    if (null !== $multiRepositoryValue = $thisForm->getValue('multi_repository'))
    {
      $setting = QubitSetting::getByName('multi_repository');

      // Add setting if it's not already in the sampleData.yml file for
      // backwards compatiblity with v1.0.3 sampleData.yml file
      if (null === $setting)
      {
        $setting = QubitSetting::createNewSetting('multi_repository', null, array('deleteable'=>false));
      }

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($multiRepositoryValue, array('sourceCulture'=>true));
      $setting->save();
    }

    // Repository upload quota
    if (null !== $multiRepositoryValue = $thisForm->getValue('repository_quota'))
    {
      $setting = QubitSetting::getByName('repository_quota');

      // Add setting if it's not already in the sampleData.yml file for
      // backwards compatiblity with v1.0.3 sampleData.yml file
      if (null === $setting)
      {
        $setting = QubitSetting::createNewSetting('repository_quota', null, array('deleteable'=>false));
      }

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($multiRepositoryValue, array('sourceCulture'=>true));
      $setting->save();
    }

    // Upload multi-page files as multiple descriptions
    if (null !== $explodeMultipageFiles = $thisForm->getValue('explode_multipage_files'))
    {
      $setting = QubitSetting::getByName('explode_multipage_files');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($explodeMultipageFiles, array('sourceCulture' => true));
      $setting->save();
    }

    if (null !== $slugTypeInformationObject = $thisForm->getValue('slug_basis_informationobject'))
    {
      $setting = QubitSetting::getByName('slug_basis_informationobject');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($slugTypeInformationObject, array('sourceCulture' => true));
      $setting->save();
    }

    // Show tooltips
    if (null !== $showTooltips = $thisForm->getValue('show_tooltips'))
    {
      $setting = QubitSetting::getByName('show_tooltips');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($showTooltips, array('sourceCulture' => true));
      $setting->save();
    }

    // Default publication status
    if (null !== $defaultPubStatus = $thisForm->getValue('defaultPubStatus'))
    {
      $setting = QubitSetting::getByName('defaultPubStatus');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($defaultPubStatus, array('sourceCulture' => true));
      $setting->save();
    }

    // Total drafts notification enabled
    if (null !== $draftNotificationEnabled = $thisForm->getValue('draft_notification_enabled'))
    {
      if (null === $setting = QubitSetting::getByName('draft_notification_enabled'))
      {
        $setting = new QubitSetting;
        $setting->name = 'draft_notification_enabled';
      }

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($draftNotificationEnabled, array('sourceCulture' => true));
      $setting->save();
    }

    // SWORD deposit directory
    if (null !== $swordDepositDir = $thisForm->getValue('sword_deposit_dir'))
    {
      $setting = QubitSetting::getByName('sword_deposit_dir');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($swordDepositDir, array('sourceCulture' => true));
      $setting->save();
    }

    // Google Maps Javascript API key
    $googleMapsApiKey = $thisForm->getValue('google_maps_api_key');

    if (null === $setting = QubitSetting::getByName('google_maps_api_key'))
    {
      $setting = new QubitSetting;
      $setting->name = 'google_maps_api_key';
    }

    // Force sourceCulture update to prevent discrepency in settings between cultures
    $setting->setValue($googleMapsApiKey, array('sourceCulture' => true));
    $setting->save();

    // Enable Institutional Scoping
    if (null !== $enableInstitutionalScoping = $thisForm->getValue('enable_institutional_scoping'))
    {
      $setting = QubitSetting::getByName('enable_institutional_scoping');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($enableInstitutionalScoping, array('sourceCulture' => true));
      $setting->save();
    }

    // Cache XML on save
    $cacheXmlOnSave = $thisForm->getValue('cache_xml_on_save');

    if (null === $setting = QubitSetting::getByName('cache_xml_on_save'))
    {
      $setting = new QubitSetting;
      $setting->name = 'cache_xml_on_save';
    }

    $setting->setValue($cacheXmlOnSave, array('sourceCulture' => true));
    $setting->save();

    return $this;
  }
}
