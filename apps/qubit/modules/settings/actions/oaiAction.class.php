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
 * OAI settings
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 * @author     Damian Bauder <drbauder@ucalgary.ca>
 */

class SettingsOaiAction extends sfAction
{
  public function execute($request)
  {
    // Redirect to global settings form if the OAI plugin is not enabled
    if (!in_array('arOaiPlugin', unserialize(sfConfig::get('app_plugins'))))
    {
      $this->redirect('settings/global');
    }

    $this->oaiRepositoryForm = new SettingsOaiRepositoryForm;

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      // Handle OAI Repository form submission
      if (null !== $request->oai_repository)
      {
        QubitCache::getInstance()->removePattern('settings:i18n:*');

        $this->oaiRepositoryForm->bind($request->oai_repository);
        if ($this->oaiRepositoryForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateOaiRepositorySettings($this->oaiRepositoryForm);
          $this->redirect('settings/oai');
        }
      }
    }

    $this->populateOaiRepositoryForm($this->oaiRepositoryForm);
  }

  /**
   * Populate the OAI Repository form with database values (non-localized)
   */
  protected function populateOaiRepositoryForm()
  {
    // Get OAI Repository settings
    $oaiAuthenticationEnabled = QubitSetting::getByName('oai_authentication_enabled');
    $oaiRepositoryCode = QubitSetting::getByName('oai_repository_code');
    $oaiAdminEmails = QubitSetting::getByName('oai_admin_emails');
    $oaiRepositoryIdentifier = QubitOai::getRepositoryIdentifier();
    $sampleOaiIdentifier = QubitOai::getOaiSampleIdentifier();
    $resumptionTokenLimit = QubitSetting::getByName('resumption_token_limit');
    $oaiAdditionalSetsEnabled = QubitSetting::getByName('oai_additional_sets_enabled');

    // Set defaults for global form
    $this->oaiRepositoryForm->setDefaults(array(
      'oai_authentication_enabled' => (isset($oaiAuthenticationEnabled)) ? intval($oaiAuthenticationEnabled->getValue(array('sourceCulture'=>true))) : 1,
      'oai_repository_code' => (isset($oaiRepositoryCode)) ? $oaiRepositoryCode->getValue(array('sourceCulture'=>true)) : null,
      'oai_repository_identifier' => $oaiRepositoryIdentifier,
      'oai_admin_emails' => $oaiAdminEmails,
      'sample_oai_identifier' => $sampleOaiIdentifier,
      'resumption_token_limit' => (isset($resumptionTokenLimit)) ? $resumptionTokenLimit->getValue(array('sourceCulture'=>true)) : null,
      'oai_additional_sets_enabled' => (isset($oaiAdditionalSetsEnabled)) ? intval($oaiAdditionalSetsEnabled->getValue(array('sourceCulture'=>true))) : 0
    ));
  }

  /**
   * Update the OAI Repository settings in database (non-localized)
   */
  protected function updateOaiRepositorySettings()
  {
    $thisForm = $this->oaiRepositoryForm;

    // OAI API authentication enabled radio button
    $oaiEnabledValue = $thisForm->getValue('oai_authentication_enabled');
    $setting = QubitSetting::getByName('oai_authentication_enabled');
    $setting->setValue($oaiEnabledValue, array('sourceCulture' => true));
    $setting->save();

    // OAI repository code
    $oaiRepositoryCodeValue = $thisForm->getValue('oai_repository_code');
    $setting = QubitSetting::getByName('oai_repository_code');
    $setting->setValue($oaiRepositoryCodeValue, array('sourceCulture' => true));
    $setting->save();

    // OAI admin emails
    $oaiAdminEmailsValue = $thisForm->getValue('oai_admin_emails');
    $setting = QubitSetting::getByName('oai_admin_emails');
    $setting->setValue($oaiAdminEmailsValue, array('sourceCulture' => true));
    $setting->save();

    // Hits per page
    $resumptionTokenLimit = $thisForm->getValue('resumption_token_limit');

    if (intval($resumptionTokenLimit) && $resumptionTokenLimit > 0)
    {
      $setting = QubitSetting::getByName('resumption_token_limit');
      $setting->setValue($resumptionTokenLimit, array('sourceCulture' => true));
      $setting->save();
    }

    // OAI additional sets enabled radio button
    $oaiAdditionalSetsEnabledValue = $thisForm->getValue('oai_additional_sets_enabled');
    $setting = QubitSetting::getByName('oai_additional_sets_enabled');
    $setting->setValue($oaiAdditionalSetsEnabledValue, array('sourceCulture' => true));
    $setting->save();

    return $this;
  }
}
