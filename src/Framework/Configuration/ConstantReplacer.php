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

final class ConstantReplacer
{
    public function replace(mixed $value, array $constants): mixed
    {
        $constants = array_change_key_case($constants, CASE_LOWER);

        return $this->replaceValue($value, $constants);
    }

    private function replaceValue(mixed $value, array $constants): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->replaceValue($item, $constants);
            }

            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        return preg_replace_callback(
            '/%(.+?)%/',
            static function (array $matches) use ($constants): string {
                $name = strtolower($matches[1]);

                if (!array_key_exists($name, $constants)) {
                    return $matches[0];
                }

                $replacement = $constants[$name];

                return is_scalar($replacement) || null === $replacement
                    ? (string) $replacement
                    : $matches[0];
            },
            $value,
        );
    }
}
