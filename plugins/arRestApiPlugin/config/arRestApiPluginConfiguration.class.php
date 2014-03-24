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
 * arRestApiPluginConfiguration configuration.
 *
 * @package     arRestApiPluginConfiguration
 * @subpackage  config
 */
class arRestApiPluginConfiguration extends sfPluginConfiguration
{
  const REGEX_UUID = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';
  const REGEX_ID   = '\d+';

  public function routingLoadConfiguration(sfEvent $event)
  {
    $this->routing = $event->getSubject();

    // How slow is inserting the routes here? I don't think I can obtain the
    // same results using a nested routing.yml files in arRestApiPlugin because
    // there's no way to bypass some of the catch-any routes in the main yaml.
    // This is probably not being cached at all :(

    /**
     * Dashboard resources
     */

    $this->addRoute('GET', '/api/dashboard', array(
      'module' => 'api',
      'action' => 'dashboardView'));

    /**
     * AIP resources
     */

    $this->addRoute('GET', '/api/aips', array(
      'module' => 'api',
      'action' => 'aipsBrowse'));

    $this->addRoute('GET', '/api/aips/:uuid', array(
      'module' => 'api',
      'action' => 'aipsView',
      'params' => array('uuid' => self::REGEX_UUID)));

    $this->addRoute('POST', '/api/aips/:uuid/reclassify', array(
      'module' => 'api',
      'action' => 'aipsReclassify',
      'params' => array('uuid' => self::REGEX_UUID)));

    /**
     * Information object resources
     */

    $this->addRoute('GET', '/api/informationobjects', array(
      'module' => 'api',
      'action' => 'informationobjectsBrowse'));

    $this->addRoute('GET,POST,DELETE', '/api/informationobjects/:id', array(
      'module' => 'api',
      'action' => 'informationobjectsDetail',
      'params' => array('id' => self::REGEX_ID)));

    $this->addRoute('GET', '/api/informationobjects/:id/tree', array(
      'module' => 'api',
      'action' => 'informationobjectsTree',
      'params' => array('id' => self::REGEX_ID)));

    $this->addRoute('GET', '/api/informationobjects/:id/files', array(
      'module' => 'api',
      'action' => 'informationobjectsFiles',
      'params' => array('id' => self::REGEX_ID)));

    $this->addRoute('GET', '/api/informationobjects/:id/tms', array(
      'module' => 'api',
      'action' => 'informationobjectsTms',
      'params' => array('id' => self::REGEX_ID)));

    $this->addRoute('POST', '/api/informationobjects/:id/associate', array(
      'module' => 'api',
      'action' => 'informationobjectsAssociate',
      'params' => array('id' => self::REGEX_ID)));


    /**
     * Actors
     */

    $this->addRoute('GET', '/api/actors', array(
      'module' => 'api',
      'action' => 'actorsBrowse'));


    /**
     * Taxonomies and terms
     */

    $this->addRoute('GET', '/api/taxonomies/:id', array(
      'module' => 'api',
      'action' => 'taxonomiesBrowse',
      'params' => array('id' => self::REGEX_ID)));
  }

  protected function addRoute($method, $pattern, array $options = array())
  {
    $defaults = $requirements = array();

    $name = str_replace('/', '_', $pattern);

    $requirements['sf_method'] = explode(',', $method);

    if (isset($options['module']))
    {
      $defaults['module'] = $options['module'];
    }

    if (isset($options['action']))
    {
      $defaults['action'] = $options['action'];
    }

    if (isset($options['params']))
    {
      $params = $options['params'];
      foreach ($params as $field => $regex)
      {
        $requirements[$field] = $regex;
      }
    }

    // Add route before slug;default_index
    $this->routing->insertRouteBefore('slug;default_index', $name,
      new sfRequestRoute($pattern, $defaults, $requirements));
  }

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    // Enable sfInstallPlugin module
    $enabledModules = sfConfig::get('sf_enabled_modules');
    $enabledModules[] = 'api';
    sfConfig::set('sf_enabled_modules', $enabledModules);

    // Connect event listener to add routes
    $this->dispatcher->connect('routing.load_configuration', array($this, 'routingLoadConfiguration'));
  }
}
