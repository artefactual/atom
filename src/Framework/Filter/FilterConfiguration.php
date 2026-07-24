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

namespace Atom\Framework\Filter;

use Atom\Framework\Configuration\ModuleConfigurationLoader;

final readonly class FilterConfiguration
{
    private const FRAMEWORK_FILTERS = [
        'rendering',
        'security',
        'culture_header',
        'cache',
        'execution',
    ];

    public function __construct(
        private ModuleConfigurationLoader $configuration,
    ) {}

    public function forModule(string $module): array
    {
        $filters = [];

        foreach (
            $this->configuration->layers($module, 'filters.yml') as $layer
        ) {
            $filters = $this->cascade($filters, $layer);
        }

        $classes = [];

        foreach ($filters as $name => $definition) {
            if (
                in_array($name, self::FRAMEWORK_FILTERS, true)
                || false === ($definition['enable'] ?? true)
            ) {
                continue;
            }

            $class = $definition['class'] ?? null;

            if (is_string($class) && '' !== $class) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    private function cascade(array $inherited, array $layer): array
    {
        $cascaded = [];

        foreach ($layer as $name => $definition) {
            $name = (string) $name;

            if (null === $definition) {
                if (isset($inherited[$name])) {
                    $cascaded[$name] = $inherited[$name];
                }

                continue;
            }

            if (is_string($definition)) {
                $definition = ['class' => $definition];
            }

            if (is_array($definition)) {
                $cascaded[$name] = array_replace(
                    $inherited[$name] ?? [],
                    $definition,
                );
            }
        }

        foreach ($inherited as $name => $definition) {
            $cascaded[$name] ??= $definition;
        }

        return $cascaded;
    }
}
