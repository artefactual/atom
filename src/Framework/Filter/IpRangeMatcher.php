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

final readonly class IpRangeMatcher
{
    public function matches(string $address, array $limits): bool
    {
        $addressBinary = @inet_pton($address);

        if (false === $addressBinary) {
            return false;
        }

        foreach ($limits as $item) {
            $range = preg_split('/[,-]/', (string) $item);

            if (false === $range || !in_array(count($range), [1, 2], true)) {
                continue;
            }

            $first = @inet_pton(trim($range[0]));

            if (
                false !== $first
                && 1 === count($range)
                && hash_equals($first, $addressBinary)
            ) {
                return true;
            }

            if (2 === count($range)) {
                $last = @inet_pton(trim($range[1]));

                if (
                    false !== $first
                    && false !== $last
                    && strlen($addressBinary) === strlen($first)
                    && strlen($addressBinary) === strlen($last)
                    && $addressBinary >= $first
                    && $addressBinary <= $last
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
