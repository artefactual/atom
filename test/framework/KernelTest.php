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

namespace Atom\Tests\Framework;

use Atom\Framework\Routing\RouteCompiler;
use Atom\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class KernelTest extends TestCase
{
    private string $cacheDirectory;

    protected function setUp(): void
    {
        $this->cacheDirectory = sys_get_temp_dir()
            .'/atom-sf7-kernel-'.bin2hex(random_bytes(8));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->cacheDirectory);
    }

    public function testBootsWithAtoMConfigurationAndRoutes(): void
    {
        $kernel = new class('test', true, $this->cacheDirectory) extends Kernel {
            public function __construct(
                string $environment,
                bool $debug,
                private readonly string $testCacheDirectory,
            ) {
                parent::__construct($environment, $debug);
            }

            public function getCacheDir(): string
            {
                return $this->testCacheDirectory;
            }

            public function getBuildDir(): string
            {
                return $this->testCacheDirectory;
            }
        };

        try {
            $kernel->boot();
            $container = $kernel->getContainer();

            self::assertSame(
                dirname(__DIR__, 2),
                $container->getParameter('sf_root_dir'),
            );
            self::assertSame(
                'qubit',
                $container->getParameter('sf_app'),
            );
            self::assertSame(
                'test',
                $container->getParameter('sf_environment'),
            );
            self::assertTrue(
                $container->getParameter('sf_escaping_strategy'),
            );

            $router = $container->get('router');
            self::assertInstanceOf(RouterInterface::class, $router);
            self::assertSame([
                '_route' => 'informationobject/action',
                'module' => 'informationobject',
                RouteCompiler::CLASS_ATTRIBUTE => 'QubitResourceRoute',
                'slug' => 'peanut',
                'action' => 'fileList',
            ], $router->match('/peanut/fileList'));
        } finally {
            $kernel->shutdown();
        }
    }
}
