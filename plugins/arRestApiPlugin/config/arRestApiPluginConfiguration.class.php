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
 */
class arRestApiPluginConfiguration extends sfPluginConfiguration
{
    public const REGEX_UUID = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';
    public const REGEX_ID = '\d+';
    public const REGEX_SLUG = '[0-9A-Za-z-]+';

    public static $summary = 'REST API plugin';
    public static $version = '1.0.0';

    public function routingLoadConfiguration(sfEvent $event)
    {
        $this->routing = $event->getSubject();

        // How slow is inserting the routes here? I don't think I can obtain the
        // same results using a nested routing.yml files in arRestApiPlugin because
        // there's no way to bypass some of the catch-any routes in the main yaml.
        // This is probably not being cached at all :(

        $this->addRoute('GET', '/api', [
            'module' => 'api',
            'action' => 'index',
        ]);

        // Taxonomies and terms.
        $this->addRoute('GET', '/api/taxonomies/:id', [
            'module' => 'api',
            'action' => 'taxonomiesBrowse',
            'params' => ['id' => self::REGEX_ID],
        ]);

        // Information objects.
        $this->addRoute('GET', '/api/informationobjects', [
            'module' => 'api',
            'action' => 'informationobjectsBrowse',
        ]);

        $this->addRoute('GET', '/api/informationobjects/:slug', [
            'module' => 'api',
            'action' => 'informationobjectsRead',
            'params' => ['slug' => self::REGEX_SLUG],
        ]);

        $this->addRoute('GET', '/api/informationobjects/:slug/digitalobject', [
            'module' => 'api',
            'action' => 'informationobjectsDownloadDigitalObject',
            'params' => ['slug' => self::REGEX_SLUG],
        ]);

        $this->addRoute('GET', '/api/informationobjects/tree/:parent_slug', [
            'module' => 'api',
            'action' => 'informationobjectsTree',
            'params' => ['parent_slug' => self::REGEX_SLUG],
        ]);

        $this->addRoute('PUT', '/api/informationobjects/:slug', [
            'module' => 'api',
            'action' => 'informationobjectsUpdate',
            'params' => ['slug' => self::REGEX_SLUG],
        ]);

        $this->addRoute('DELETE', '/api/informationobjects/:slug', [
            'module' => 'api',
            'action' => 'informationobjectsDelete',
            'params' => ['slug' => self::REGEX_SLUG],
        ]);

        $this->addRoute('POST', '/api/informationobjects', [
            'module' => 'api',
            'action' => 'informationobjectsCreate',
        ]);

        // Digital objects.
        $this->addRoute('GET', '/api/digitalobjects', [
            'module' => 'api',
            'action' => 'digitalobjectsBrowse',
        ]);

        $this->addRoute('POST', '/api/digitalobjects', [
            'module' => 'api',
            'action' => 'digitalobjectsCreate',
        ]);

        $this->addRoute('*', '/api/*', [
            'module' => 'api',
            'action' => 'endpointNotFound',
        ]);
    }

    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        // Enable arRestApiPlugin module
        $enabledModules = sfConfig::get('sf_enabled_modules');
        $enabledModules[] = 'api';
        sfConfig::set('sf_enabled_modules', $enabledModules);

        // Connect event listener to add routes
        $this->dispatcher->connect('routing.load_configuration', [$this, 'routingLoadConfiguration']);
    }

    protected function addRoute($method, $pattern, array $options = [])
    {
        $defaults = $requirements = [];

        // Allow routes to only apply to specific HTTP methods
        if ('*' != $method) {
            $requirements['sf_method'] = explode(',', $method);
        }

        if (isset($options['module'])) {
            $defaults['module'] = $options['module'];
        }

        if (isset($options['action'])) {
            $defaults['action'] = $options['action'];
            $name = 'api_'.$options['action'];
        } else {
            $name = 'api_'.str_replace('/', '_', $pattern);
        }

        if (isset($options['params'])) {
            $params = $options['params'];
            foreach ($params as $field => $regex) {
                $requirements[$field] = $regex;
            }
        }

        // Add route before slug;default_index
        $this->routing->insertRouteBefore(
            'slug/default',
            $name,
            new sfRequestRoute($pattern, $defaults, $requirements)
        );
    }
}
