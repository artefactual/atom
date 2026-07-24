<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 */

declare(strict_types=1);

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\RequestAdapter;
use Atom\Framework\Bridge\RouteState;
use Atom\Framework\Bridge\RoutingAdapter;
use Atom\Framework\Routing\ResourceClassifier;
use Atom\Framework\Routing\ResourceRepository;
use Atom\Framework\Routing\ResourceRouteResolver;
use Atom\Framework\Routing\RouteCompiler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class RoutingAdapterTest extends TestCase
{
    public function testFindsRouteParametersForLegacyCallers(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('match')
            ->with('/user/login')
            ->willReturn([
                '_route' => 'user_login',
                'module' => 'user',
                'action' => 'login',
            ]);
        $routing = new RoutingAdapter(
            $router,
            new RequestAdapter(Request::create('/')),
        );

        self::assertSame([
            'name' => 'user_login',
            'pattern' => '/user/login',
            'parameters' => [
                'module' => 'user',
                'action' => 'login',
            ],
        ], $routing->findRoute(
            'https://example.test/user/login?next=/',
        ));
    }

    public function testReturnsFalseWhenNoRouteMatches(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('match')
            ->willThrowException(new ResourceNotFoundException());
        $routing = new RoutingAdapter(
            $router,
            new RequestAdapter(Request::create('/')),
        );

        self::assertFalse($routing->findRoute('/missing'));
    }

    public function testGeneratesObjectOnlyUrlsFromTheirSlug(): void
    {
        $resource = new \stdClass();
        $resource->slug = 'example-record';
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with(
                'slug',
                ['slug' => 'example-record'],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            )
            ->willReturn('/example-record');
        $routing = new RoutingAdapter(
            $router,
            new RequestAdapter(Request::create('/')),
        );

        self::assertSame(
            '/example-record',
            $routing->generate(null, [$resource]),
        );
    }

    public function testGeneratesHomepageForEmptyTargets(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with(
                'homepage',
                [],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            )
            ->willReturn('/');
        $routing = new RoutingAdapter(
            $router,
            new RequestAdapter(Request::create('/')),
        );

        self::assertSame('/', $routing->generate(null));
    }

    public function testIgnoresRouteStateWhenRegeneratingCurrentUrl(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with(
                'default',
                [
                    'module' => 'staticpage',
                    'action' => 'home',
                    'sf_culture' => 'fr',
                ],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            )
            ->willReturn('/staticpage/home?sf_culture=fr');
        $routing = new RoutingAdapter(
            $router,
            new RequestAdapter(Request::create('/')),
        );

        self::assertSame(
            '/staticpage/home?sf_culture=fr',
            $routing->generate(null, [
                'sf_route' => new RouteState(),
                'module' => 'staticpage',
                'action' => 'home',
                'sf_culture' => 'fr',
            ]),
        );
    }

    public function testEnrichesMatchedResourceRoutes(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('match')->willReturn([
            '_route' => 'slug',
            RouteCompiler::CLASS_ATTRIBUTE => 'QubitMetadataRoute',
            'slug' => 'about',
            'action' => 'index',
        ]);
        $repository = new class implements ResourceRepository {
            public function findBySlug(string $slug): ?object
            {
                return (object) ['slug' => $slug];
            }

            public function defaultTemplate(string $module): false|string
            {
                return false;
            }

            public function informationObjectTemplate(
                object $resource,
            ): false|string {
                return false;
            }
        };
        $classifier = new class implements ResourceClassifier {
            public function classify(object $resource): ?string
            {
                return 'static_page';
            }
        };
        $routing = new RoutingAdapter(
            $router,
            new RequestAdapter(Request::create('/')),
            new ResourceRouteResolver($repository, $classifier),
        );

        self::assertSame([
            'name' => 'slug',
            'pattern' => '/about',
            'parameters' => [
                'slug' => 'about',
                'action' => 'index',
                'module' => 'staticpage',
            ],
        ], $routing->findRoute('/about'));
    }

    public function testGeneratesStoredInternalUrisWithQueryParameters(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with(
                'default',
                [
                    'module' => 'staticpage',
                    'action' => 'index',
                    'slug' => 'about',
                ],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            )
            ->willReturn('/staticpage/index?slug=about');
        $routing = new RoutingAdapter(
            $router,
            new RequestAdapter(Request::create('/')),
        );

        self::assertSame(
            '/staticpage/index?slug=about#content',
            $routing->generateInternalUri(
                'staticpage/index?slug=about#content',
            ),
        );
    }

    public function testContinuesAfterUnresolvedSlugRoutes(): void
    {
        $routes = new RouteCollection();
        $routes->add('slug', new Route('/{slug}', [
            RouteCompiler::CLASS_ATTRIBUTE => 'QubitMetadataRoute',
            'action' => 'index',
        ]));
        $routes->add('default_index', new Route('/{module}', [
            'action' => 'index',
        ]));
        $router = $this->createMock(RouterInterface::class);
        $router->method('match')->willReturn([
            '_route' => 'slug',
            RouteCompiler::CLASS_ATTRIBUTE => 'QubitMetadataRoute',
            'slug' => 'user',
            'action' => 'index',
        ]);
        $router->method('getRouteCollection')->willReturn($routes);
        $router->method('getContext')->willReturn(new RequestContext());
        $repository = new class implements ResourceRepository {
            public function findBySlug(string $slug): ?object
            {
                return null;
            }

            public function defaultTemplate(string $module): false|string
            {
                return false;
            }

            public function informationObjectTemplate(
                object $resource,
            ): false|string {
                return false;
            }
        };
        $classifier = new class implements ResourceClassifier {
            public function classify(object $resource): ?string
            {
                return null;
            }
        };
        $routing = new RoutingAdapter(
            $router,
            new RequestAdapter(Request::create('/')),
            new ResourceRouteResolver($repository, $classifier),
        );

        self::assertSame([
            'name' => 'default_index',
            'pattern' => '/user',
            'parameters' => [
                'action' => 'index',
                'module' => 'user',
            ],
        ], $routing->findRoute('/user'));
    }
}
