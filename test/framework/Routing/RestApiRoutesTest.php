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

namespace AccessToMemory\test\framework\Routing;

use Atom\Framework\Routing\RestApiRoutes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @internal
 */
final class RestApiRoutesTest extends TestCase
{
    public function testPrependsMethodAwareApiRoutes(): void
    {
        $application = new RouteCollection();
        $application->add(
            'slug/default',
            new Route('/{slug}', ['module' => 'default']),
        );
        $routes = (new RestApiRoutes())->prependTo($application);
        $matcher = new UrlMatcher($routes, new RequestContext(
            method: 'PUT',
        ));
        $match = $matcher->match('/api/informationobjects/example');

        self::assertSame(
            'api_informationobjectsUpdate',
            $match['_route'],
        );
        self::assertSame('api', $match['module']);
        self::assertSame('informationobjectsUpdate', $match['action']);
        self::assertSame('example', $match['slug']);
        self::assertLessThan(
            array_search('slug/default', array_keys($routes->all()), true),
            array_search(
                'api_informationobjectsUpdate',
                array_keys($routes->all()),
                true,
            ),
        );
    }
}
