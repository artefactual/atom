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

final class ParameterCompiler
{
    public function compile(array $configuration, string $prefix = ''): array
    {
        $prefix = strtolower($prefix);
        $parameters = [];

        foreach ($configuration as $category => $values) {
            $category = (string) $category;

            if (!is_array($values)) {
                $parameters[$prefix.strtolower($category)] = $values;

                continue;
            }

            $categoryPrefix = str_starts_with($category, '.')
                ? $prefix
                : $prefix.$category.'_';

            foreach ($values as $key => $value) {
                $parameters[$categoryPrefix.$key] = $value;
            }
        }

        return $parameters;
    }
}
