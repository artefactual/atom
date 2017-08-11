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
 * Site information
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsSiteInformationAction extends sfAction
{
  public function execute($request)
  {
    $this->culture = $this->context->user->getCulture();

    $this->siteInformationForm = new SettingsSiteInformationForm;

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      // Handle site information form submission
      if (null !== $request->site_information)
      {
        $this->siteInformationForm->bind($request->site_information);
        if ($this->siteInformationForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateSiteInformationSettings();
          $this->redirect('settings/siteInformation');
        }
      }
    }

    $this->populateSiteInformationForm();
  }

  /**
   * Populate the site information settings from the database (localized)
   */
  protected function populateSiteInformationForm()
  {
    // Get site information settings
    $this->siteTitle = (null !== $siteTitle = QubitSetting::getByName('siteTitle')) ? $siteTitle : new QubitSetting;
    $this->siteDescription = (null !== $siteDescription = QubitSetting::getByName('siteDescription')) ? $siteDescription : new QubitSetting;
    $this->siteBaseUrl = (null !== $siteBaseUrl = QubitSetting::getByName('siteBaseUrl')) ? $siteBaseUrl : new QubitSetting;

    // Set defaults values
    $this->siteInformationForm->setDefaults(array(
      'site_title' => $this->siteTitle->getValue(array('culture' => $this->culture)),
      'site_description' => $this->siteDescription->getValue(array('culture' => $this->culture)),
      'site_base_url' => $this->siteBaseUrl->getValue(array('culture' => $this->culture))
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

    // Save Site Base URL
    $siteBaseUrl = $thisForm->getValue('site_base_url');
    $siteUrlSetting = QubitSetting::getByName('siteBaseUrl');

    // Create new QubitSetting if site_description doesn't already exist
    if (null === $siteUrlSetting)
    {
      $siteUrlSetting = QubitSetting::createNewSetting('siteBaseUrl', null, array('deleteable'=>false));
    }
    $siteUrlSetting->setValue($siteBaseUrl);
    $siteUrlSetting->save();

    return $this;
  }
}
