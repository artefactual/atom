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

use Atom\Framework\Autoload\LegacyClassLoader;
use Atom\Framework\Cache\PhpArrayFileCache;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class LegacyClassLoaderTest extends TestCase
{
    public function testLoadsMappedClasses(): void
    {
        vfsStream::setup('root', null, [
            'LoaderFixture.php' => <<<'PHP'
                <?php

                class AtomLegacyLoaderFixture {}
                PHP,
        ]);

        $loader = new LegacyClassLoader(['vfs://root']);
        $loader->register();

        try {
            self::assertTrue(class_exists('AtomLegacyLoaderFixture'));
        } finally {
            $loader->unregister();
        }
    }

    public function testReusesPersistedClassMap(): void
    {
        vfsStream::setup('root', null, [
            'classes' => [
                'LoaderFixture.php' => <<<'PHP'
                    <?php

                    class AtomPersistedLoaderFixture {}
                    PHP,
            ],
        ]);
        $cacheDirectory = sys_get_temp_dir()
            .'/atom-legacy-class-cache-'.bin2hex(random_bytes(8));
        $cache = new PhpArrayFileCache($cacheDirectory);
        $path = 'vfs://root/classes/LoaderFixture.php';

        try {
            self::assertSame(
                $path,
                (new LegacyClassLoader(
                    ['vfs://root/classes'],
                    classMapCache: $cache,
                ))->findFile('AtomPersistedLoaderFixture'),
            );

            unlink($path);

            self::assertSame(
                $path,
                (new LegacyClassLoader(
                    new class implements \IteratorAggregate {
                        public function getIterator(): \Traversable
                        {
                            throw new \RuntimeException(
                                'The cached map should avoid a source scan.',
                            );
                        }
                    },
                    classMapCache: $cache,
                ))->findFile('AtomPersistedLoaderFixture'),
            );
        } finally {
            (new Filesystem())->remove($cacheDirectory);
        }
    }
}
