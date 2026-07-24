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

namespace Atom\Framework\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteCompiler
{
    public const CLASS_ATTRIBUTE = '_atom_route_class';
    public const CLASS_OPTION = 'atom_route_class';

    private const SUPPORTED_CLASSES = [
        'sfRoute',
        'QubitRoute',
        'QubitResourceRoute',
        'QubitMetadataRoute',
    ];

    public function compile(array $definitions): RouteCollection
    {
        $routes = new RouteCollection();

        foreach ($definitions as $name => $definition) {
            if (!is_array($definition)) {
                throw new RoutingException(sprintf(
                    'Route "%s" must contain a mapping.',
                    $name,
                ));
            }

            $routes->add(
                (string) $name,
                $this->compileRoute((string) $name, $definition),
            );
        }

        return $routes;
    }

    private function compileRoute(string $name, array $definition): Route
    {
        if (
            'collection' === ($definition['type'] ?? null)
            || str_contains((string) ($definition['class'] ?? ''), 'Collection')
        ) {
            throw new RoutingException(sprintf(
                'Route collection "%s" is not supported.',
                $name,
            ));
        }

        $class = (string) ($definition['class'] ?? 'sfRoute');

        if (!in_array($class, self::SUPPORTED_CLASSES, true)) {
            throw new RoutingException(sprintf(
                'Route "%s" uses unsupported class "%s".',
                $name,
                $class,
            ));
        }

        $defaults = $this->routeArray(
            $name,
            $definition,
            'params',
            'param',
        );
        $requirements = $this->routeArray(
            $name,
            $definition,
            'requirements',
        );
        $options = $this->routeArray($name, $definition, 'options');

        [$defaults, $requirements] = $this->normalizeDefaults(
            $defaults,
            $requirements,
        );
        $requirements = $this->normalizeRequirements($requirements);

        $path = $this->normalizePath($name, $definition['url'] ?? '/');

        if (str_contains($path, '*')) {
            if (1 !== substr_count($path, '*')) {
                throw new RoutingException(sprintf(
                    'Route "%s" may contain only one wildcard.',
                    $name,
                ));
            }

            $path = str_replace('*', '{_star}', $path);
            $defaults['_star'] ??= '';
            $requirements['_star'] ??= '.*';
        }

        if ('sfRoute' !== $class) {
            $defaults[self::CLASS_ATTRIBUTE] = $class;
            $options[self::CLASS_OPTION] = $class;
        }

        return new Route(
            $path,
            $defaults,
            $requirements,
            $options,
        );
    }

    private function normalizeDefaults(
        array $defaults,
        array $requirements,
    ): array {
        $normalized = [];

        foreach ($defaults as $key => $value) {
            if (ctype_digit((string) $key)) {
                $normalized[(string) $value] = true;

                continue;
            }

            if (is_array($value)) {
                if (isset($value['default'])) {
                    $normalized[$key] = $value['default'];
                }

                if (isset($value['pattern'])) {
                    $requirements[$key] = $value['pattern'];
                }

                continue;
            }

            $normalized[$key] = urldecode((string) $value);
        }

        return [$normalized, $requirements];
    }

    private function normalizePath(string $name, mixed $path): string
    {
        if (!is_string($path) || '' === $path) {
            $path = '/';
        }

        if (!str_starts_with($path, '/')) {
            throw new RoutingException(sprintf(
                'Route "%s" path must start with "/".',
                $name,
            ));
        }

        return preg_replace(
            '/:([a-zA-Z_][a-zA-Z0-9_]*)/',
            '{$1}',
            $path,
        );
    }

    private function normalizeRequirements(array $requirements): array
    {
        foreach ($requirements as $name => $requirement) {
            if (!is_string($requirement)) {
                throw new RoutingException(sprintf(
                    'Requirement "%s" must be a string.',
                    $name,
                ));
            }

            if (str_starts_with($requirement, '^')) {
                $requirement = substr($requirement, 1);
            }

            if (str_ends_with($requirement, '$')) {
                $requirement = substr($requirement, 0, -1);
            }

            $requirements[$name] = $requirement;
        }

        return $requirements;
    }

    private function routeArray(
        string $name,
        array $definition,
        string ...$keys,
    ): array {
        foreach ($keys as $key) {
            if (!isset($definition[$key])) {
                continue;
            }

            if (!is_array($definition[$key])) {
                throw new RoutingException(sprintf(
                    'Route "%s" key "%s" must contain a mapping.',
                    $name,
                    $key,
                ));
            }

            return $definition[$key];
        }

        return [];
    }
}
