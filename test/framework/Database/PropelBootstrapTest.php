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

namespace Atom\Tests\Framework\Database;

use Atom\Framework\Database\PropelBootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PropelBootstrapTest extends TestCase
{
    public function testCompilesAtoMDatabaseConfiguration(): void
    {
        $configuration = (new PropelBootstrap([]))->compile([
            'propel' => [
                'class' => 'sfPropelDatabase',
                'param' => [
                    'dsn' => 'mysql:host=database;dbname=atom',
                    'username' => 'atom',
                    'password' => 'secret',
                    'encoding' => 'utf8mb4',
                    'persistent' => true,
                    'pooling' => true,
                    'classname' => 'PropelPDO',
                ],
            ],
        ]);

        self::assertSame('propel', $configuration['datasources']['default']);
        self::assertSame(
            'mysql',
            $configuration['datasources']['propel']['adapter'],
        );
        self::assertSame(
            'mysql:host=database;dbname=atom',
            $configuration['datasources']['propel']['connection']['dsn'],
        );
        self::assertSame(
            ['value' => true],
            $configuration['datasources']['propel']['connection']['options']['ATTR_PERSISTENT'],
        );
        self::assertTrue($configuration['instance-pooling']);
    }

    public function testSkipsInitializationWithoutDatabaseConfiguration(): void
    {
        self::assertFalse((new PropelBootstrap([]))->initialize());
    }
}
