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

namespace Atom\Framework\Bridge;

use Atom\Framework\Routing\ResourceRouteResolver;
use Atom\Framework\Routing\RouteCompiler;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final readonly class RoutingAdapter
{
    private const METADATA_MODULES = [
        'sfIsaarPlugin' => 'isaar',
        'sfEacPlugin' => 'eac',
        'sfEadPlugin' => 'ead',
        'sfIsadPlugin' => 'isad',
        'sfDcPlugin' => 'dc',
        'sfSkosPlugin' => 'skos',
        'sfRadPlugin' => 'rad',
        'sfModsPlugin' => 'mods',
        'arDacsPlugin' => 'dacs',
        'sfIsdfPlugin' => 'isdf',
    ];

    public function __construct(
        private RouterInterface $router,
        private RequestAdapter $request,
        private ?ResourceRouteResolver $resourceResolver = null,
    ) {}

    public function generate(
        ?string $route,
        array|object $parameters = [],
        bool $absolute = false,
    ): string {
        $parameters = $this->normalizeParameters(
            $parameters,
            null === $route,
        );
        $route ??= $this->routeFor($parameters);
        unset($parameters['sf_route']);

        return $this->router->generate(
            ltrim($route, '@'),
            $parameters,
            $absolute
                ? UrlGeneratorInterface::ABSOLUTE_URL
                : UrlGeneratorInterface::ABSOLUTE_PATH,
        );
    }

    public function getCurrentInternalUri(bool $withRouteName = false): mixed
    {
        $module = $this->request->getParameter('module');
        $action = $this->request->getParameter('action');
        $uri = sprintf('%s/%s', $module, $action);

        return $withRouteName
            ? [$this->request->getAttribute('_route'), $uri]
            : $uri;
    }

    public function generateInternalUri(
        string $uri,
        bool $absolute = false,
    ): string {
        $parts = parse_url('/'.ltrim($uri, '/'));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        [$module, $action] = array_pad(explode('/', $path, 2), 2, '');
        $parameters = [
            'module' => $module,
            'action' => $action,
        ];

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            $parameters = array_replace($parameters, $query);
        }

        $url = $this->generate('default', $parameters, $absolute);

        if (isset($parts['fragment'])) {
            $url .= '#'.$parts['fragment'];
        }

        return $url;
    }

    public function findRoute(string $url): array|false
    {
        $match = $this->match($url);

        if (false === $match) {
            return false;
        }

        unset($match['resource']);

        return $match;
    }

    public function parse(string $url): array|false
    {
        $match = $this->match($url);

        if (false === $match) {
            return false;
        }

        $route = new RouteState();
        $route->resource = $match['resource'];
        $parameters = $match['parameters'];
        $parameters['_sf_route'] = $route;

        return $parameters;
    }

    private function match(string $url): array|false
    {
        $path = parse_url($url, \PHP_URL_PATH);

        if (!is_string($path)) {
            return false;
        }

        try {
            $parameters = $this->router->match(
                '/'.ltrim($path, '/'),
            );
        } catch (ResourceNotFoundException) {
            return false;
        }

        $resource = null;

        try {
            while (null !== $this->resourceResolver) {
                $resolved = $this->resourceResolver->resolve($parameters);

                if (null !== $resolved) {
                    $parameters = $resolved->parameters;
                    $resource = $resolved->resource;

                    break;
                }

                $parameters = $this->nextMatch(
                    (string) ($parameters['_route'] ?? ''),
                    $path,
                );
            }
        } catch (NotFoundException|ResourceNotFoundException) {
            return false;
        }

        $name = (string) ($parameters['_route'] ?? '');
        unset(
            $parameters['_route'],
            $parameters['_controller'],
            $parameters[RouteCompiler::CLASS_ATTRIBUTE],
        );

        return [
            'name' => $name,
            'pattern' => $path,
            'parameters' => $parameters,
            'resource' => $resource,
        ];
    }

    private function routeFor(array $parameters): string
    {
        if ([] === $parameters) {
            return 'homepage';
        }

        if (
            isset($parameters['sf_route'])
            && is_string($parameters['sf_route'])
        ) {
            return $parameters['sf_route'];
        }

        if (isset($parameters['slug'], $parameters['template'])) {
            return 'slug;template';
        }

        if (
            isset($parameters['slug'])
            && 'edit' === ($parameters['action'] ?? null)
            && !isset($parameters['module'])
        ) {
            return 'edit';
        }

        if (isset($parameters['slug'], $parameters['module'])) {
            return isset($parameters['action'])
                ? 'slug/default'
                : 'slug;default_index';
        }

        if (isset($parameters['slug'])) {
            return 'slug';
        }

        if (isset($parameters['id'], $parameters['module'])) {
            return 'id/default';
        }

        return isset($parameters['action']) ? 'default' : 'default_index';
    }

    private function normalizeParameters(
        array|object $parameters,
        bool $selectRoute,
    ): array {
        if (is_object($parameters)) {
            $parameters = [$parameters];
        }

        $resource = OutputEscaper::unescape($parameters[0] ?? null);
        unset($parameters[0]);

        if (is_object($resource)) {
            try {
                $slug = $resource->slug;
            } catch (\Throwable) {
                $slug = null;
            }

            if (null !== $slug && '' !== (string) $slug) {
                $parameters['slug'] ??= (string) $slug;
            } else {
                try {
                    $id = $resource->id;
                } catch (\Throwable) {
                    $id = null;
                }

                if (null !== $id && '' !== (string) $id) {
                    $parameters['id'] ??= (string) $id;
                }
            }
        }

        if (
            isset($parameters['id'], $parameters['module'])
            && $selectRoute
            && !isset($parameters['sf_route'])
        ) {
            $parameters['action'] ??= 'index';
        }

        if (
            isset($parameters['slug'], $parameters['module'])
            && $selectRoute
            && !isset($parameters['sf_route'])
            && in_array(
                $parameters['action'] ?? 'index',
                ['index', 'edit'],
                true,
            )
        ) {
            if (
                'index' === ($parameters['action'] ?? 'index')
                && isset(self::METADATA_MODULES[$parameters['module']])
            ) {
                $parameters['template'] =
                    self::METADATA_MODULES[$parameters['module']];
            }

            unset($parameters['module']);
        }

        return $parameters;
    }

    private function nextMatch(string $routeName, string $path): array
    {
        $routes = new RouteCollection();
        $found = false;

        foreach ($this->router->getRouteCollection() as $name => $route) {
            if ($found) {
                $routes->add($name, $route);
            } elseif ($name === $routeName) {
                $found = true;
            }
        }

        if (!$found) {
            throw new ResourceNotFoundException(sprintf(
                'Matched route "%s" is not in the route collection.',
                $routeName,
            ));
        }

        return (new UrlMatcher(
            $routes,
            $this->router->getContext(),
        ))->match($path);
    }
}
