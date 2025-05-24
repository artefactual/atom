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

require_once __DIR__.'/../vendor/composer/autoload.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

class ProjectConfiguration extends sfProjectConfiguration
{
    public function setup()
    {
        $this->namespacesClassLoader();

        $plugins = [
            'qbAclPlugin',
            'qtAccessionPlugin',
            'sfDrupalPlugin',
            'sfHistoryPlugin',
            'arElasticSearchPlugin',
            'sfPropelPlugin',
            'sfThumbnailPlugin',
            'sfTranslatePlugin',
            'sfWebBrowserPlugin',
            // sfPluginAdminPlugin depends on sfPropelPlugin
            'sfPluginAdminPlugin',
        ];

        // Check if the OIDC plugin should be enabled.
        $filePath = 'activate-oidc-plugin';
        if (file_exists($filePath) && 0 === filesize($filePath)) {
            $plugins[] = 'arOidcPlugin';
        }

        $this->enablePlugins($plugins);

        $this->dispatcher->connect(
            'debug.web.load_panels',
            ['arWebDebugPanel', 'listenToLoadDebugWebPanelEvent']
        );
    }

    public function isPluginEnabled($pluginName)
    {
        return false !== array_search($pluginName, $this->plugins);
    }

    protected function namespacesClassLoader()
    {
        $rootDir = sfConfig::get('sf_root_dir');

        // Use APC when available to cache the location of namespaced classes
        if (extension_loaded('apcu') || extension_loaded('apc')) {
            // Use unique prefix to avoid cache clashing
            $prefix = sprintf('atom:%s:', md5($rootDir));
            $loader = new QubitApcUniversalClassLoader($prefix);
        } else {
            $loader = new UniversalClassLoader();
        }

        $loader->registerNamespaces([
            'Psr' => $rootDir.DIRECTORY_SEPARATOR.'vendor',
            'AccessToMemory' => $rootDir.DIRECTORY_SEPARATOR.'lib',
        ]);

        $loader->register();
    }
}
