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

use Atom\Framework\Configuration\ConfigurationMerger;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ConfigurationMergerTest extends TestCase
{
    public function testRecursivelyMergesLaterConfiguration(): void
    {
        $merger = new ConfigurationMerger();

        self::assertSame([
            'first' => true,
            'nested' => [
                'retained' => 'first',
                'overridden' => 'second',
                'added' => 'second',
            ],
            'list' => ['replacement', 'retained'],
            'second' => true,
        ], $merger->merge(
            [
                'first' => true,
                'nested' => [
                    'retained' => 'first',
                    'overridden' => 'first',
                ],
                'list' => ['first', 'retained'],
            ],
            [
                'nested' => [
                    'overridden' => 'second',
                    'added' => 'second',
                ],
                'list' => ['replacement'],
                'second' => true,
            ],
        ));
    }

    public function testMergesDefaultAllAndEnvironmentInOrder(): void
    {
        $merger = new ConfigurationMerger();

        self::assertSame([
            'value' => 'test',
            'default' => true,
            'all' => true,
            'test' => true,
        ], $merger->forEnvironment([
            'default' => ['value' => 'default', 'default' => true],
            'all' => ['value' => 'all', 'all' => true],
            'test' => ['value' => 'test', 'test' => true],
            'prod' => ['value' => 'prod'],
        ], 'test'));
    }

    public function testIgnoresNullEnvironmentSections(): void
    {
        $merger = new ConfigurationMerger();

        self::assertSame(
            ['all' => ['retained' => true]],
            $merger->mergeConfigurations([
                ['all' => ['retained' => true]],
                ['all' => null],
            ]),
        );
    }
}
