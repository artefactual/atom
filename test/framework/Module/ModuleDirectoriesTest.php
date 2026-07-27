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

namespace Atom\Tests\Framework\Module;

use Atom\Framework\Cache\PhpArrayFileCache;
use Atom\Framework\Module\ModuleDirectories;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class ModuleDirectoriesTest extends TestCase
{
    public function testPreservesPluginThenApplicationPrecedence(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $directories = new ModuleDirectories(
            $projectDirectory,
            'qubit',
            ['arOidcPlugin'],
        );

        self::assertSame([
            $projectDirectory
                .'/plugins/arOidcPlugin/modules/user/actions',
            $projectDirectory.'/apps/qubit/modules/user/actions',
        ], $directories->actions('user'));
    }

    public function testReusesPersistedProductionDirectories(): void
    {
        $projectDirectory = sys_get_temp_dir()
            .'/atom-module-directories-'.bin2hex(random_bytes(8));
        $actionsDirectory = $projectDirectory
            .'/apps/qubit/modules/example/actions';
        $cacheDirectory = $projectDirectory.'/cache';
        $filesystem = new Filesystem();
        $filesystem->mkdir($actionsDirectory);

        try {
            self::assertSame(
                [$actionsDirectory],
                (new ModuleDirectories(
                    $projectDirectory,
                    'qubit',
                    cache: new PhpArrayFileCache($cacheDirectory),
                ))->actions('example'),
            );

            $filesystem->remove($actionsDirectory);

            self::assertSame(
                [$actionsDirectory],
                (new ModuleDirectories(
                    $projectDirectory,
                    'qubit',
                    cache: new PhpArrayFileCache($cacheDirectory),
                ))->actions('example'),
            );
        } finally {
            $filesystem->remove($projectDirectory);
        }
    }
}
