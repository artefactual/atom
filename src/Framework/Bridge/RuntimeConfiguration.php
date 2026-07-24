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

final readonly class RuntimeConfiguration
{
    public function __construct(
        private string $application,
        private string $environment,
        private array $plugins,
    ) {}

    public function getApplication(): string
    {
        return $this->application;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isPluginEnabled(string $plugin): bool
    {
        return in_array($plugin, $this->plugins, true);
    }

    public function loadHelpers(array|string $helpers): void {}
}
