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

use Atom\Framework\Configuration\ApplicationConfiguration;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;

final readonly class RouteConfigurationLoader
{
    public function __construct(
        private ApplicationConfiguration $configuration,
        private RouteCompiler $compiler,
    ) {}

    public function load(
        string $configPath = 'config/routing.yml',
    ): RouteCollection {
        $routes = $this->compiler->compile(
            $this->configuration->loadRaw($configPath),
        );

        foreach ($this->configuration->paths($configPath) as $path) {
            $routes->addResource(new FileResource($path));
        }

        return $routes;
    }
}
