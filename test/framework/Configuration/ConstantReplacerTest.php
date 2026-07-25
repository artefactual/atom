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

namespace Atom\Tests\Framework\Configuration;

use Atom\Framework\Configuration\ConstantReplacer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ConstantReplacerTest extends TestCase
{
    public function testRecursivelyReplacesKnownConstants(): void
    {
        $replacer = new ConstantReplacer();

        self::assertSame([
            'path' => '/srv/atom/cache',
            'nested' => [
                'application' => 'qubit',
                'unknown' => '%NOT_DEFINED%',
            ],
            'enabled' => true,
        ], $replacer->replace([
            'path' => '%SF_ROOT_DIR%/cache',
            'nested' => [
                'application' => '%sf_app%',
                'unknown' => '%NOT_DEFINED%',
            ],
            'enabled' => true,
        ], [
            'sf_root_dir' => '/srv/atom',
            'SF_APP' => 'qubit',
        ]));
    }
}
