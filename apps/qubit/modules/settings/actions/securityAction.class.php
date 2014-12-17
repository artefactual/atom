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
 * Security
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsSecurityAction extends sfAction
{
  public function execute($request)
  {
    $this->securityForm = new SettingsSecurityForm;

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      // Handle security form submission
      if (null !== $request->security)
      {
        $this->securityForm->bind($request->security);
        if ($this->securityForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateSecuritySettings($this->securityForm);
          $this->redirect('settings/security');
        }
      }
    }

    $this->populateSecurityForm($this->securityForm);
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
}
