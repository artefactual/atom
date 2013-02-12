<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * List of qubit settings
 *
 * @package    qubit
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsListAction extends sfAction
{
  // Available languages (have XLIFF dir and search engine config)
  protected static $availableLanguges = array(
    '' => '&nbsp;',
    'ar' => 'العربية',
    'de' => 'deutsch',
    'el' => 'eλληνικά',
    'en' => 'english',
    'es' => 'español',
    'fa' => 'فارسی',
    'fr' => 'français',
    'is' => 'íslenska',
    'it' => 'italiano',
    'ja' => '日本語',
    'ko' => '한국어 ',
    'nl' => 'nederlands',
    'pt' => 'português',
    'sl' => 'slovenščina'
  );

  public function execute($request)
  {
    $this->form = new sfForm;

    $this->culture = $this->context->user->getCulture();

    $this->globalForm = new SettingsGlobalForm;
    $this->siteInformationForm = new SettingsSiteInformationForm;
    $this->defaultTemplateForm = new SettingsDefaultTemplateForm;
    $this->uiLabelForm = new SettingsGenericForm(array(), array(
      'settings' => QubitSetting::getByScope('ui_label'), 'scope'=>'ui_label', 'fieldsRequired' => false));
    $this->oaiRepositoryForm = new SettingsOaiRepositoryForm;
    $this->jobSchedulingForm = new SettingsJobSchedulingForm;
    $this->securityForm = new SettingsSecurityForm;

    $this->initializeDefaultPageElementsForm();

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      if ($this->context->getViewCacheManager() !== null)
      {
        $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_browseMenu&sf_cache_key=*');
        $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_mainMenu&sf_cache_key=*');
      }

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
          $this->redirect('settings/list');
        }
      }

      // Handle site information form submission
      if (null !== $request->site_information)
      {
        $this->siteInformationForm->bind($request->site_information);
        if ($this->siteInformationForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateSiteInformationSettings();
          $this->redirect('settings/list');
        }
      }

      // Handle default template form submission
      if (null !== $request->default_template)
      {
        $this->defaultTemplateForm->bind($request->default_template);
        if ($this->defaultTemplateForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateDefaultTemplateSettings($this->defaultTemplateForm);
          $this->redirect('settings/list');
        }
      }

      // Handle default template form submission
      if (null !== $request->ui_label)
      {
        $this->uiLabelForm->bind($request->ui_label);
        if ($this->uiLabelForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateUiLabelSettings($this->uiLabelForm);
          $this->redirect('settings/list');
        }
      }

      // Handle OAI Repository form submission
      if (null !== $request->oai_repository)
      {
        $this->oaiRepositoryForm->bind($request->oai_repository);
        if ($this->oaiRepositoryForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateOaiRepositorySettings($this->oaiRepositoryForm);
          $this->redirect('settings/list');
        }
      }

      // Handle job scheduling form submission
      if (null !== $request->job_scheduling)
      {
        $this->jobSchedulingForm->bind($request->job_scheduling);
        if ($this->jobSchedulingForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateJobSchedulingSettings($this->jobSchedulingForm);
          $this->redirect('settings/list');
        }
      }

      // Handle security form submission
      if (null !== $request->security)
      {
        $this->securityForm->bind($request->security);
        if ($this->securityForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateSecuritySettings($this->securityForm);
          $this->redirect('settings/list');
        }
      }

      if (null !== $languageCode = $request->languageCode)
      {
        try
        {
          format_language($languageCode, $languageCode);
        }
        catch (Exception $e)
        {
          $this->redirect(array('module' => 'settings', 'action' => 'list'));
        }

        $setting = new QubitSetting;
        $setting->name = $languageCode;
        $setting->scope = 'i18n_languages';
        $setting->value = $languageCode;
        $setting->deleteable = true;
        $setting->editable = true;
        $setting->getCurrentSettingI18n()->setCulture('en');
        $setting->sourceCulture = 'en';

        $setting->save();
      }
    }

    // Populate forms
    $this->populateGlobalForm();
    $this->populateSiteInformationForm();
    $this->populateDefaultTemplateForm($this->defaultTemplateForm);
    $this->populateUiLabelForm($this->uiLabelForm);
    $this->populateOaiRepositoryForm($this->oaiRepositoryForm);
    $this->populateJobSchedulingForm($this->jobSchedulingForm);
    $this->populateSecurityForm($this->securityForm);

    // Last symfony 1.0 forms holdout
    $this->i18nLanguages = QubitSetting::getByScope('i18n_languages');

    $this->form->setValidator('languageCode', new sfValidatorI18nChoiceLanguage);
    $this->form->setWidget('languageCode', new sfWidgetFormI18nChoiceLanguage(array('add_empty' => true, 'culture' => $this->context->user->getCulture())));

    // make vars available to template
    $this->availableLanguages = self::$availableLanguges;
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
    $refImageMaxWidth = QubitSetting::getByName('reference_image_maxwidth');
    $hitsPerPage = QubitSetting::getByName('hits_per_page');
    $accessionMask = QubitSetting::getByName('accession_mask');
    $accessionCounter = QubitSetting::getByName('accession_counter');
    $separatorCharacter = QubitSetting::getByName('separator_character');
    $inheritCodeInformationObject = QubitSetting::getByName('inherit_code_informationobject');
    $sortTreeviewInformationObject = QubitSetting::getByName('sort_treeview_informationobject');
    $sortBrowserUser = QubitSetting::getByName('sort_browser_user');
    $sortBrowserAnonymous = QubitSetting::getByName('sort_browser_anonymous');
    $multiRepository = QubitSetting::getByName('multi_repository');
    $repositoryQuota = QubitSetting::getByName('repository_quota');
    $explodeMultipageFiles = QubitSetting::getByName('explode_multipage_files');
    $showTooltips = QubitSetting::getByName('show_tooltips');
    $defaultPubStatus = QubitSetting::getByName('defaultPubStatus');
    $swordDepositDir = QubitSetting::getByName('sword_deposit_dir');

    // Set defaults for global form
    $this->globalForm->setDefaults(array(
      'version' => $version,
      'check_for_updates' => (isset($checkForUpdates)) ? intval($checkForUpdates->getValue(array('sourceCulture'=>true))) : 1,
      'reference_image_maxwidth' => (isset($refImageMaxWidth)) ? $refImageMaxWidth->getValue(array('sourceCulture'=>true)) : null,
      'hits_per_page' => (isset($hitsPerPage)) ? $hitsPerPage->getValue(array('sourceCulture'=>true)) : null,
      'accession_mask' => (isset($accessionMask)) ? $accessionMask->getValue(array('sourceCulture'=>true)) : null,
      'accession_counter' => (isset($accessionCounter)) ? intval($accessionCounter->getValue(array('sourceCulture'=>true))) : 1,
      'separator_character' => (isset($separatorCharacter)) ? $separatorCharacter->getValue(array('sourceCulture'=>true)) : null,
      'inherit_code_informationobject' => (isset($inheritCodeInformationObject)) ? intval($inheritCodeInformationObject->getValue(array('sourceCulture'=>true))) : 1,
      'sort_treeview_informationobject' => (isset($sortTreeviewInformationObject)) ? $sortTreeviewInformationObject->getValue(array('sourceCulture'=>true)) : 0,
      'sort_browser_user' => (isset($sortBrowserUser)) ? $sortBrowserUser->getValue(array('sourceCulture'=>true)) : 0,
      'sort_browser_anonymous' => (isset($sortBrowserAnonymous)) ? $sortBrowserAnonymous->getValue(array('sourceCulture'=>true)) : 0,
      'multi_repository' => (isset($multiRepository)) ? intval($multiRepository->getValue(array('sourceCulture'=>true))) : 1,
      'repository_quota' => (isset($repositoryQuota)) ? $repositoryQuota->getValue(array('sourceCulture'=>true)) : 0,
      'explode_multipage_files' => (isset($explodeMultipageFiles)) ? intval($explodeMultipageFiles->getValue(array('sourceCulture'=>true))) : 1,
      'show_tooltips' => (isset($showTooltips)) ? intval($showTooltips->getValue(array('sourceCulture'=>true))) : 1,
      'defaultPubStatus' => (isset($defaultPubStatus)) ? $defaultPubStatus->getValue(array('sourceCulture'=>true)) : QubitTerm::PUBLICATION_STATUS_DRAFT_ID,
      'sword_deposit_dir' => (isset($swordDepositDir)) ? $swordDepositDir->getValue(array('sourceCulture'=>true)) : null
    ));
  }

  /**
   * Update the global settings in database (non-localized)
   */
  protected function updateGlobalSettings()
  {
    $thisForm = $this->globalForm;

    // Check for updates
    if (null !== $checkForUpdates = $thisForm->getValue('check_for_updates'))
    {
      $setting = QubitSetting::getByName('check_for_updates');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($checkForUpdates, array('sourceCulture' => true));
      $setting->save();
    }

    // Reference image max width
    if (null !== $refMaxWidth = $thisForm->getValue('reference_image_maxwidth'))
    {
      if (intval($refMaxWidth) && $refMaxWidth > 0)
      {
        $setting = QubitSetting::getByName('reference_image_maxwidth');

        // Force sourceCulture update to prevent discrepency in settings between cultures
        $setting->setValue($refMaxWidth, array('sourceCulture'=>true));
        $setting->save();
      }
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
      if (ctype_digit($accessionCounter) && $accessionCounter > -1)
      {
        $setting = QubitSetting::getByName('accession_counter');

        // Force sourceCulture update to prevent discrepency in settings between cultures
        $setting->setValue($accessionCounter, array('sourceCulture' => true));
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

    // Sort Treeview (Information Object)
    if (null !== $sortTreeviewInformationObjectValue = $thisForm->getValue('sort_treeview_informationobject'))
    {
      $setting = QubitSetting::getByName('sort_treeview_informationobject');

       // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($sortTreeviewInformationObjectValue, array('sourceCulture'=>true));
      $setting->save();
    }

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

    // SWORD deposit directory
    if (null !== $swordDepositDir = $thisForm->getValue('sword_deposit_dir'))
    {
      $setting = QubitSetting::getByName('sword_deposit_dir');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($swordDepositDir, array('sourceCulture' => true));
      $setting->save();
    }

    return $this;
  }

  /**
   * Populate the site information settings from the database (localized)
   */
  protected function populateSiteInformationForm()
  {
    // Get site information settings
    $this->siteTitle = (null !== $siteTitle = QubitSetting::getByName('siteTitle')) ? $siteTitle : new QubitSetting;
    $this->siteDescription = (null !== $siteDescription = QubitSetting::getByName('siteDescription')) ? $siteDescription : new QubitSetting;

    // Set defaults values
    $this->siteInformationForm->setDefaults(array(
      'site_title' => $this->siteTitle->getValue(array('culture' => $this->culture)),
      'site_description' => $this->siteDescription->getValue(array('culture' => $this->culture))
    ));

    return $this;
  }

  /**
   * Update site information settings (localized)
   */
  protected function updateSiteInformationSettings()
  {
    $thisForm = $this->siteInformationForm;

    // Get Site Title
    $siteTitle = $thisForm->getValue('site_title');
    $siteTitleSetting = QubitSetting::getByName('siteTitle');

    // Create new QubitSetting if site_title doesn't already exist
    if (null === $siteTitleSetting)
    {
      $siteTitleSetting = QubitSetting::createNewSetting('siteTitle', null, array('scope'=>'site_information', 'deleteable'=>false));
    }
    $siteTitleSetting->setValue($siteTitle);
    $siteTitleSetting->save();

    // Save Site Description
    $siteDescription = $thisForm->getValue('site_description');
    $siteDescSetting = QubitSetting::getByName('siteDescription');

    // Create new QubitSetting if site_description doesn't already exist
    if (null === $siteDescSetting)
    {
      $siteDescSetting = QubitSetting::createNewSetting('siteDescription', null, array('scope'=>'site_information', 'deleteable'=>false));
    }
    $siteDescSetting->setValue($siteDescription);
    $siteDescSetting->save();

    return $this;
  }

  /**
   * Populate the default template settings from the database (non-localized)
   */
  protected function populateDefaultTemplateForm($form)
  {
    $infoObjectTemplate = QubitSetting::getByNameAndScope('informationobject', 'default_template');
    $actorTemplate = QubitSetting::getByNameAndScope('actor', 'default_template');
    $repositoryTemplate = QubitSetting::getByNameAndScope('repository', 'default_template');

    // Set defaults for global form
    $this->defaultTemplateForm->setDefaults(array(
      'informationobject' => (isset($infoObjectTemplate)) ? $infoObjectTemplate->getValue(array('sourceCulture'=>true)) : null,
      'actor' => (isset($actorTemplate)) ? $actorTemplate->getValue(array('sourceCulture'=>true)) : null,
      'repository' => (isset($repositoryTemplate)) ? $repositoryTemplate->getValue(array('sourceCulture'=>true)) : null,
    ));
  }

  /**
   * Update default template db values with form values (non-localized)
   *
   * @return $this;
   */
  protected function updateDefaultTemplateSettings($form)
  {
    if (null !== $newValue = $form->getValue('informationobject'))
    {
      $setting = QubitSetting::findAndSave('informationobject', $newValue, array(
        'scope'=>'default_template', 'createNew'=>true, 'sourceCulture'=>true));
    }

    if (null !== $newValue = $form->getValue('actor'))
    {
      $setting = QubitSetting::findAndSave('actor', $newValue, array(
        'scope'=>'default_template', 'createNew'=>true, 'sourceCulture'=>true));
    }

    if (null !== $newValue = $form->getValue('repository'))
    {
      $setting = QubitSetting::findAndSave('repository', $newValue, array(
        'scope'=>'default_template', 'createNew'=>true, 'sourceCulture'=>true));
    }

    return $this;
  }

  /**
   * Populate the ui_label form with database values (localized)
   */
  protected function populateUiLabelForm($form)
  {
    foreach ($form->getSettings() as $setting)
    {
      $form->setDefault($setting->getName(), $setting->getValue());
    }
  }

  /**
   * Update ui_label db values with form values (localized)
   *
   * @return $this
   */
  protected function updateUiLabelSettings($form)
  {
    foreach ($form->getSettings() as $setting)
    {
      if (null !== $value = $form->getValue($setting->getName()))
      {
        $setting->setValue($value);
        $setting->save();
      }
    }

    // Add a new ui_label
    if (null !== ($newName = $form->getValue('new_setting_name')) && strlen($newValue = $form->getValue('new_setting_value')))
    {
      $setting = QubitSetting::createNewSetting($newName, $newValue, array('scope'=>$form->getScope()));
      $setting->save();
    }

    return $this;
  }

  /**
   * Populate the OAI Repository form with database values (non-localized)
   */
  protected function populateOaiRepositoryForm()
  {
    // Get OAI Repository settings
    $oaiEnabled = QubitSetting::getByName('oai_enabled');
    $oaiRepositoryCode = QubitSetting::getByName('oai_repository_code');
    $oaiRepositoryIdentifier = QubitOai::getRepositoryIdentifier();
    $sampleOaiIdentifier = QubitOai::getSampleIdentifier();
    $resumptionTokenLimit = QubitSetting::getByName('resumption_token_limit');

    // Set defaults for global form
    $this->oaiRepositoryForm->setDefaults(array(
      'oai_enabled' => (isset($oaiEnabled)) ? intval($oaiEnabled->getValue(array('sourceCulture'=>true))) : 1,
      'oai_repository_code' => (isset($oaiRepositoryCode)) ? $oaiRepositoryCode->getValue(array('sourceCulture'=>true)) : null,
      'oai_repository_identifier' => $oaiRepositoryIdentifier,
      'sample_oai_identifier' => $sampleOaiIdentifier,
      'resumption_token_limit' => (isset($resumptionTokenLimit)) ? $resumptionTokenLimit->getValue(array('sourceCulture'=>true)) : null
    ));
  }

  /**
   * Update the OAI Repository settings in database (non-localized)
   */
  protected function updateOaiRepositorySettings()
  {
    $thisForm = $this->oaiRepositoryForm;

    // OAI enabled radio button
    if (null !== $oaiEnabledValue = $thisForm->getValue('oai_enabled'))
    {
      $setting = QubitSetting::getByName('oai_enabled');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($oaiEnabledValue, array('sourceCulture'=>true));
      $setting->save();
    }

    // OAI repository code
    $oaiRepositoryCodeValue = $thisForm->getValue('oai_repository_code');
    $setting = QubitSetting::getByName('oai_repository_code');
    $setting->setValue($oaiRepositoryCodeValue, array('sourceCulture'=>true));
    $setting->save();

    // Hits per page
    if (null !== $resumptionTokenLimit = $thisForm->getValue('resumption_token_limit'))
    {
      if (intval($resumptionTokenLimit) && $resumptionTokenLimit > 0)
      {
        $setting = QubitSetting::getByName('resumption_token_limit');
        $setting->setValue($resumptionTokenLimit, array('sourceCulture'=>true));
        $setting->save();
      }
    }

    return $this;
  }

  /**
   * Populate the Job scheduling form
   */
  protected function populateJobSchedulingForm()
  {
    $useJobScheduler = QubitSetting::getByName('use_job_scheduler');

    $this->jobSchedulingForm->setDefaults(array(
      'use_job_scheduler' => (isset($useJobScheduler)) ? intval($useJobScheduler->getValue(array('sourceCulture' => true))) : 1
    ));
  }

  /**
   * Update the Job scheduling settings
   */
  protected function updateJobSchedulingSettings()
  {
    $thisForm = $this->jobSchedulingForm;

    if (null !== $useJobSchedulerValue = $thisForm->getValue('use_job_scheduler'))
    {
      $setting = QubitSetting::getByName('use_job_scheduler');
      $setting->setValue($useJobSchedulerValue, array('sourceCulture' => true));
      $setting->save();
    }

    return $this;
  }

  /**
   * Populate the security form
   */
  protected function populateSecurityForm()
  {
    $limitAdminIp = QubitSetting::getByName('limit_admin_ip');
    $requireSslAdmin = QubitSetting::getByName('require_ssl_admin');
    $requireStrongPasswords = QubitSetting::getByName('require_strong_passwords');

    $this->securityForm->setDefaults(array(
      'limit_admin_ip' => (isset($limitAdminIp)) ? $limitAdminIp->getValue(array('sourceCulture'=>true)) : null,
      'require_ssl_admin' => (isset($requireSslAdmin)) ? intval($requireSslAdmin->getValue(array('sourceCulture'=>true))) : 1,
      'require_strong_passwords' => (isset($requireStrongPasswords)) ? intval($requireStrongPasswords->getValue(array('sourceCulture'=>true))) : 1
    ));
  }

  /**
   * Update the security settings
   */
  protected function updateSecuritySettings()
  {
    $thisForm = $this->securityForm;

    // Limit admin IP
    $setting = QubitSetting::getByName('limit_admin_ip');
    // Force sourceCulture update to prevent discrepency in settings between cultures
    $setting->setValue($thisForm->getValue('limit_admin_ip'), array('sourceCulture' => true));
    $setting->save();

    // Require SSL for admin funcionality
    if (null !== $requireSslAdmin = $thisForm->getValue('require_ssl_admin'))
    {
      $setting = QubitSetting::getByName('require_ssl_admin');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($requireSslAdmin, array('sourceCulture' => true));
      $setting->save();
    }

    // Require strong passwords
    if (null !== $requireStrongPasswords = $thisForm->getValue('require_strong_passwords'))
    {
      $setting = QubitSetting::getByName('require_strong_passwords');

      // Force sourceCulture update to prevent discrepency in settings between cultures
      $setting->setValue($requireStrongPasswords, array('sourceCulture' => true));
      $setting->save();
    }

    return $this;
  }

  protected function initializeDefaultPageElementsForm()
  {
    $this->defaultPageElementsForm = new sfForm;
    $this->defaultPageElementsForm->setWidgets(array(
      'toggleDescription' => new sfWidgetFormInputCheckbox,
      'toggleLogo' => new sfWidgetFormInputCheckbox,
      'toggleTitle' => new sfWidgetFormInputCheckbox));

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleDescription');
    if (1 == count($toggleDescriptionQuery = QubitSetting::get($criteria)))
    {
      $toggleDescriptionSetting = $toggleDescriptionQuery[0];

      $this->defaultPageElementsForm->setDefault('toggleDescription', $toggleDescriptionSetting->__get('value', array('sourceCulture' => true)));
    }

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleLogo');
    if (1 == count($toggleLogoQuery = QubitSetting::get($criteria)))
    {
      $toggleLogoSetting = $toggleLogoQuery[0];

      $this->defaultPageElementsForm->setDefault('toggleLogo', $toggleLogoSetting->__get('value', array('sourceCulture' => true)));
    }

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleTitle');
    if (1 == count($toggleTitleQuery = QubitSetting::get($criteria)))
    {
      $toggleTitleSetting = $toggleTitleQuery[0];

      $this->defaultPageElementsForm->setDefault('toggleTitle', $toggleTitleSetting->__get('value', array('sourceCulture' => true)));
    }

    return $this;
  }
} // End class
