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

require_once dirname(__FILE__).'/../vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

require_once __DIR__.'/../vendor/symfony2/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
require_once __DIR__.'/../lib/QubitApcUniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->namespacesClassLoader();

    $plugins = array(
      'qbAclPlugin',
      'qtAccessionPlugin',
      'sfDrupalPlugin',
      'sfFormExtraPlugin',
      'sfHistoryPlugin',
      'arElasticSearchPlugin',
      'sfPropelPlugin',
      'sfThumbnailPlugin',
      'sfTranslatePlugin',
      'sfWebBrowserPlugin',

      // sfInstallPlugin and sfPluginAdminPlugin depend on sfPropelPlugin, so
      // must be enabled last
      'sfInstallPlugin',
      'sfPluginAdminPlugin');

    $this->enablePlugins($plugins);

    $this->dispatcher->connect('debug.web.load_panels',
      array('arWebDebugPanel', 'listenToLoadDebugWebPanelEvent'));
  }

  protected function namespacesClassLoader()
  {
    $rootDir = sfConfig::get('sf_root_dir');

    // Use APC when available to cache the location of namespaced classes
    if (extension_loaded('apcu')|| extension_loaded('apc'))
    {
      // Use unique prefix to avoid cache clashing
      $prefix = sprintf('atom:%s:', md5($rootDir));
      $loader = new QubitApcUniversalClassLoader($prefix);
    }
    else
    {
      $loader = new UniversalClassLoader();
    }

    $loader->registerNamespaces(array(
      'Elastica' => $rootDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'elastica',
      'Elasticsearch' => $rootDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'elastica',
      'Psr' => $rootDir.DIRECTORY_SEPARATOR.'vendor'));

    $loader->register();
  }

  public function isPluginEnabled($pluginName)
  {
    return false !== array_search($pluginName, $this->plugins);
  }
}
