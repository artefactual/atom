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
 * Check for updates component
 *
 * @package qubit
 * @subpackage default
 * @version svn: $Id: updateCheckComponent.class.php 12020 2012-07-31 21:59:35Z sevein $
 * @author Jesús García Crespo <correo@sevein.com>
 */
class DefaultUpdateCheckComponent extends sfComponent
{
  public function execute($request)
  {
    if (!$this->context->user->hasCredential('administrator') || !sfConfig::get('app_check_for_updates'))
    {
      return sfView::NONE;
    }

    // updateCheck service URL
    $this->updateCheckUrl = 'https://updatecheck.qubit-toolkit.org/check/';

    // Application version
    $this->currentVersion = qubitConfiguration::VERSION;

    // We are using cookies so we need to identify the application path
    $this->cookiePath = sfContext::getInstance()->request->getRelativeUrlRoot();
    if (1 > strlen($this->cookiePath))
    {
      $this->cookiePath = '/';
    }

    // Build array with user data that will be sent to the service
    $this->updateCheckData = array();

    // Absolute URL
    $this->updateCheckData['address'] = $request->getUriPrefix() . $request->getScriptName() . $request->getPathInfo();

    // Version (including db version)
    $this->updateCheckData['version'] = qubitConfiguration::VERSION.' - '.sfConfig::get('app_version');

    // Distribution (icaatom, dcb, qubit)
    if (null === $this->updateCheckData['distribution'] = $this->context->user->getAttribute('distribution'))
    {
      $packageXmlPath = sfConfig::get('sf_config_dir').'/package.xml';
      if (file_exists($packageXmlPath))
      {
        require_once sfConfig::get('sf_root_dir').'/vendor/FluentDOM/FluentDOM.php';

        $fd = FluentDOM($packageXmlPath)
          ->namespaces(array('p' => 'http://pear.php.net/dtd/package-2.0'));

        $this->context->user->setAttribute('distribution', $this->updateCheckData['distribution'] = $fd->find('/*/p:name')->item(0)->textContent);
      }
    }

    // Site description
    $this->updateCheckData['site_description'] = sfConfig::get('app_siteDescription');

    // Site title
    $this->updateCheckData['site_title'] = sfConfig::get('app_siteTitle');

    // If the client does not support JavaScript we try to access to the service
    // using sfWebBrowser, a wrapper for php_curl/fopen/sockets (in that order)
    if (!$request->getCookie('has_js'))
    {
      if (null === ($this->lastVersion = $this->context->user->getAttribute('last_version')))
      {
        try
        {
          $browser = new sfWebBrowser;
          $this->lastVersion = $browser->post($this->updateCheckUrl, $this->updateCheckData)->getResponseText();
        }
        catch (Exception $e)
        {
          $this->lastVersion = 0;
        }

        $this->context->user->setAttribute('last_version', $this->lastVersion);
      }

      if (0 == $this->lastVersion || 1 > version_compare($this->lastVersion, qubitConfiguration::VERSION))
      {
        return sfView::NONE;
      }
    }
  }
}
