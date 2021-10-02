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
class arUnogPreservicaPluginConfiguration extends sfPluginConfiguration
{
    public const PRESERVICA_UUID_PROPERTY_NAME = 'preservicaUUID';
    public const REGEX_SLUG = '[0-9A-Za-z-]+';

    public static $summary = 'Preservica integration plugin (NOTE: requires arRestApiPlugin be enabled).';
    public static $version = '0.0.1';

    public function routingLoadConfiguration(sfEvent $event)
    {
        $this->routing = $event->getSubject();

        // Use similar URL scheme to REST API for digital object create endpoint
        $defaults = [
            'module' => 'arUnogPreservicaPlugin',
            'action' => 'digitalobjectsCreate',
        ];

        $requirements = [
            'sf_method' => 'POST',
        ];

        $this->routing->insertRouteBefore(
            'api_endpointNotFound',
            'preservica_api_create_digitalobjects',
            new sfRequestRoute('/api/preservica/digitalobjects', $defaults, $requirements)
        );

        // Use friendly URL for alternative identifier update endpoint
        $defaults = [
            'module' => 'arUnogPreservicaPlugin',
            'action' => 'altIdentifierUpdate',
        ];

        $requirements = [
            'sf_method' => 'POST',
        ];

        $this->routing->insertRouteBefore(
            'api_endpointNotFound',
            'preservica_api_update_altidentifier',
            new sfRequestRoute('/api/preservica/altIdentifier', $defaults, $requirements)
        );

        // Use friendly URL for alternative identifier search endpoint
        $defaults = [
            'module' => 'arUnogPreservicaPlugin',
            'action' => 'altIdentifierSearch',
        ];

        $requirements = [
            'sf_method' => 'GET',
        ];

        $this->routing->insertRouteBefore(
            'api_endpointNotFound',
            'preservica_api_search_altidentifier',
            new sfRequestRoute('/api/preservica/altIdentifier', $defaults, $requirements)
        );

        // Make Preserica digital object download a Qubit resource route
        $defaults = [
            'module' => 'arUnogPreservicaPlugin',
            'action' => 'downloadMaster',
            'slug' => self::REGEX_SLUG,
        ];

        $requirements = [
            'sf_method' => 'GET',
        ];

        $this->routing->insertRouteBefore(
            'informationobject/action',
            'preservica_download_master',
            new QubitResourceRoute('/:slug/download', $defaults, $requirements)
        );
    }

    public function initialize()
    {
        parent::initialize();

        // Connect event listener to add routes
        $this->dispatcher->connect('routing.load_configuration', [$this, 'routingLoadConfiguration']);
    }
}
