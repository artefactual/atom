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

namespace Atom\Tests\Framework\Filter;

use Atom\Framework\Filter\IpRangeMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class IpRangeMatcherTest extends TestCase
{
    public function testMatchesAddressesAndRanges(): void
    {
        $matcher = new IpRangeMatcher();
        $limits = [
            '192.0.2.10',
            '2001:db8::10-2001:db8::20',
        ];

        self::assertTrue($matcher->matches('192.0.2.10', $limits));
        self::assertTrue($matcher->matches('2001:db8::15', $limits));
        self::assertFalse($matcher->matches('192.0.2.11', $limits));
        self::assertFalse($matcher->matches('not-an-address', $limits));
    }
}
