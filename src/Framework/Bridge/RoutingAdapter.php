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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final readonly class RoutingAdapter
{
    public function __construct(
        private RouterInterface $router,
        private RequestAdapter $request,
    ) {}

    public function generate(
        ?string $route,
        array $parameters = [],
        bool $absolute = false,
    ): string {
        $route ??= $this->routeFor($parameters);

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

    private function routeFor(array $parameters): string
    {
        if (isset($parameters['sf_route'])) {
            return (string) $parameters['sf_route'];
        }

        if (isset($parameters['slug'], $parameters['template'])) {
            return 'slug;template';
        }

        if (isset($parameters['slug'], $parameters['module'])) {
            return isset($parameters['action'])
                ? 'slug/default'
                : 'slug;default_index';
        }

        return isset($parameters['action']) ? 'default' : 'default_index';
    }
}
