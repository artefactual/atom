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

use Atom\Framework\Cache\PhpArrayFileCache;
use Atom\Framework\Configuration\ConfigurationException;
use Atom\Framework\Configuration\ConfigurationPathResolver;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class ConfigurationPathResolverTest extends TestCase
{
    public function testResolvesConfigurationFromLeastToMostSpecific(): void
    {
        vfsStream::setup('project', null, [
            'framework' => [
                'config' => ['view.yml' => 'framework'],
            ],
            'plugins' => [
                'FirstPlugin' => [
                    'config' => ['view.yml' => 'plugin global'],
                    'modules' => [
                        'example' => [
                            'config' => ['view.yml' => 'plugin module'],
                        ],
                    ],
                ],
            ],
            'config' => ['view.yml' => 'project'],
            'modules' => [
                'example' => [
                    'config' => ['view.yml' => 'project module'],
                ],
            ],
            'apps' => [
                'qubit' => [
                    'config' => ['view.yml' => 'application'],
                    'modules' => [
                        'example' => [
                            'config' => ['view.yml' => 'application module'],
                        ],
                    ],
                ],
            ],
        ]);

        $resolver = new ConfigurationPathResolver(
            'vfs://project',
            'qubit',
            ['FirstPlugin'],
            'vfs://project/framework',
        );

        self::assertSame([
            'vfs://project/framework/config/view.yml',
            'vfs://project/plugins/FirstPlugin/config/view.yml',
            'vfs://project/config/view.yml',
            'vfs://project/modules/example/config/view.yml',
            'vfs://project/apps/qubit/config/view.yml',
            'vfs://project/plugins/FirstPlugin/modules/example/config/view.yml',
            'vfs://project/apps/qubit/modules/example/config/view.yml',
        ], $resolver->resolve('modules/example/config/view.yml'));
    }

    public function testRejectsPathsOutsideProject(): void
    {
        $resolver = new ConfigurationPathResolver(
            'vfs://project',
            'qubit',
        );

        $this->expectException(ConfigurationException::class);
        $resolver->resolve('../secrets.yml');
    }

    public function testReusesPersistedProductionPaths(): void
    {
        $projectDirectory = sys_get_temp_dir()
            .'/atom-configuration-paths-'.bin2hex(random_bytes(8));
        $configurationPath = $projectDirectory.'/config/settings.yml';
        $cacheDirectory = $projectDirectory.'/cache';
        $filesystem = new Filesystem();
        $filesystem->dumpFile($configurationPath, 'all: []');

        try {
            self::assertSame(
                [$configurationPath],
                (new ConfigurationPathResolver(
                    $projectDirectory,
                    'qubit',
                    cache: new PhpArrayFileCache($cacheDirectory),
                ))->resolve('config/settings.yml'),
            );

            unlink($configurationPath);

            self::assertSame(
                [$configurationPath],
                (new ConfigurationPathResolver(
                    $projectDirectory,
                    'qubit',
                    cache: new PhpArrayFileCache($cacheDirectory),
                ))->resolve('config/settings.yml'),
            );
        } finally {
            $filesystem->remove($projectDirectory);
        }
    }
}
