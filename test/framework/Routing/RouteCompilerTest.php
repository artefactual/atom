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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Tests\Framework\Routing;

use Atom\Framework\Routing\RouteCompiler;
use Atom\Framework\Routing\RoutingException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RouteCompilerTest extends TestCase
{
    public function testCompilesAtoMRouteSyntax(): void
    {
        $routes = (new RouteCompiler())->compile([
            'slug;template' => [
                'url' => '/:slug;:template',
                'class' => 'QubitMetadataRoute',
                'param' => [
                    'action' => 'index',
                    'slug' => ['pattern' => '[^;]+'],
                ],
            ],
        ]);

        $route = $routes->get('slug;template');

        self::assertNotNull($route);
        self::assertSame('/{slug};{template}', $route->getPath());
        self::assertSame('index', $route->getDefault('action'));
        self::assertSame('[^;]+', $route->getRequirement('slug'));
        self::assertSame(
            'QubitMetadataRoute',
            $route->getDefault(RouteCompiler::CLASS_ATTRIBUTE),
        );
        self::assertSame(
            'QubitMetadataRoute',
            $route->getOption(RouteCompiler::CLASS_OPTION),
        );
        self::assertFalse($route->hasDefault('slug'));
    }

    public function testCompilesWildcardRoute(): void
    {
        $routes = (new RouteCompiler())->compile([
            'upload' => [
                'url' => '/uploads/r/*',
                'param' => [
                    'module' => 'digitalobject',
                    'action' => 'view',
                ],
            ],
        ]);

        $route = $routes->get('upload');

        self::assertNotNull($route);
        self::assertSame('/uploads/r/{_star}', $route->getPath());
        self::assertSame('', $route->getDefault('_star'));
        self::assertSame('.*', $route->getRequirement('_star'));
    }

    public function testRejectsUnknownRouteClasses(): void
    {
        $this->expectException(RoutingException::class);

        (new RouteCompiler())->compile([
            'unknown' => [
                'url' => '/',
                'class' => 'UnknownRoute',
            ],
        ]);
    }
}
