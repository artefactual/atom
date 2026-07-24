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

namespace Atom\Framework\Configuration;

final class ConfigurationMerger
{
    public function mergeConfigurations(array $configurations): array
    {
        $merged = [];

        foreach ($configurations as $configuration) {
            $configuration = array_filter(
                $configuration,
                static fn (mixed $value): bool => null !== $value,
            );
            $merged = $this->merge($merged, $configuration);
        }

        return $merged;
    }

    public function forEnvironment(
        array $configuration,
        string $environment,
    ): array {
        return $this->merge(
            $this->environmentValues($configuration, 'default'),
            $this->environmentValues($configuration, 'all'),
            $this->environmentValues($configuration, $environment),
        );
    }

    public function merge(array ...$arrays): array
    {
        $merged = [];

        foreach ($arrays as $array) {
            $merged = $this->mergePair($merged, $array);
        }

        return $merged;
    }

    private function environmentValues(
        array $configuration,
        string $environment,
    ): array {
        $values = $configuration[$environment] ?? [];

        return is_array($values) ? $values : [];
    }

    private function mergePair(array $first, array $second): array
    {
        $merged = [];

        foreach ($first + $second as $key => $value) {
            $inFirst = array_key_exists($key, $first);
            $inSecond = array_key_exists($key, $second);

            if (
                $inFirst
                && $inSecond
                && is_array($first[$key])
                && is_array($second[$key])
            ) {
                $merged[$key] = $this->mergePair(
                    $first[$key],
                    $second[$key],
                );
            } elseif ($inSecond) {
                $merged[$key] = $second[$key];
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
