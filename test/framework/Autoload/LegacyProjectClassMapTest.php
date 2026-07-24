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

namespace Atom\Tests\Framework\Autoload;

use Atom\Framework\Autoload\LegacyClassDirectories;
use Atom\Framework\Autoload\LegacyClassLoader;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class LegacyProjectClassMapTest extends TestCase
{
    public function testIndexesAtoMAndPropelWithoutSymfonyRuntime(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $loader = new LegacyClassLoader(
            new LegacyClassDirectories($projectDirectory),
        );

        self::assertSame(
            $projectDirectory.'/lib/model/QubitInformationObject.php',
            $loader->findFile('QubitInformationObject'),
        );
        self::assertSame(
            $projectDirectory
                .'/apps/qubit/modules/informationobject/actions/'
                .'indexAction.class.php',
            $loader->findFile('InformationObjectIndexAction'),
        );
        self::assertSame(
            $projectDirectory
                .'/apps/qubit/modules/user/actions/editAction.class.php',
            $loader->findFile('UserEditAction'),
        );
        self::assertStringEndsWith(
            '/sfPropelPlugin/lib/vendor/propel/util/Criteria.php',
            $loader->findFile('Criteria'),
        );
        self::assertSame(
            $projectDirectory.'/vendor/parsedown/ParsedownExtra.php',
            $loader->findFile('ParsedownExtra'),
        );
        self::assertSame(
            $projectDirectory
                .'/vendor/net_gearman/Net/Gearman/Worker.php',
            $loader->findFile('Net_Gearman_Worker'),
        );
        self::assertSame(
            $projectDirectory.'/vendor/easyrdf/EasyRdf/Graph.php',
            $loader->findFile('EasyRdf_Graph'),
        );
        self::assertNull($loader->findFile('sfAction'));
        self::assertSame([
            $projectDirectory
                .'/vendor/symfony/lib/plugins/sfPropelPlugin/lib/vendor',
        ], (new LegacyClassDirectories(
            $projectDirectory,
        ))->includePaths());
    }

    public function testLoadsGeneratedModelsWithoutSymfonyRuntime(): void
    {
        $loader = new LegacyClassLoader(
            new LegacyClassDirectories(dirname(__DIR__, 3)),
        );
        $loader->register();

        try {
            self::assertTrue(class_exists('Criteria'));
            self::assertTrue(class_exists('QubitInformationObject'));
            self::assertNull($loader->findFile('sfContext'));
        } finally {
            $loader->unregister();
        }
    }
}
