<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RestApiRoutes
{
    private const DEFINITIONS = [
        ['GET', '/api', 'index'],
        ['GET', '/api/taxonomies/{id}', 'taxonomiesBrowse', [
            'id' => '\d+',
        ]],
        [
            'GET',
            '/api/informationobjects',
            'informationobjectsBrowse',
        ],
        [
            'GET',
            '/api/informationobjects/{slug}',
            'informationobjectsRead',
            ['slug' => '[^/]+'],
        ],
        [
            'GET',
            '/api/informationobjects/{slug}/digitalobject',
            'informationobjectsDownloadDigitalObject',
            ['slug' => '[^/]+'],
        ],
        [
            'GET',
            '/api/informationobjects/tree/{slug}',
            'informationobjectsTree',
            ['slug' => '[^/]+'],
        ],
        [
            'PUT',
            '/api/informationobjects/{slug}',
            'informationobjectsUpdate',
            ['slug' => '[^/]+'],
        ],
        [
            'DELETE',
            '/api/informationobjects/{slug}',
            'informationobjectsDelete',
            ['slug' => '[^/]+'],
        ],
        [
            'POST',
            '/api/informationobjects',
            'informationobjectsCreate',
        ],
        ['GET', '/api/digitalobjects', 'digitalobjectsBrowse'],
        ['POST', '/api/digitalobjects', 'digitalobjectsCreate'],
        ['POST', '/api/physicalobjects', 'physicalobjectsCreate'],
        ['*', '/api/{_star}', 'endpointNotFound', [
            '_star' => '.*',
        ]],
    ];

    public function prependTo(RouteCollection $application): RouteCollection
    {
        $routes = new RouteCollection();

        foreach (self::DEFINITIONS as $definition) {
            [$method, $path, $action] = $definition;
            $route = new Route(
                $path,
                ['module' => 'api', 'action' => $action],
                $definition[3] ?? [],
            );

            if ('*' !== $method) {
                $route->setMethods([$method]);
            }

            $routes->add('api_'.$action, $route);
        }

        $routes->addCollection($application);

        return $routes;
    }
}
