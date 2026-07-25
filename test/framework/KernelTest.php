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

use Atom\Framework\Bridge\User;
use Atom\Framework\Routing\RouteCompiler;
use Atom\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
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
        $kernel = $this->kernel();

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
            self::assertTrue($container->getParameter('app_b5_theme'));
            self::assertContains(
                'sfIsadPlugin',
                $container->getParameter('sf_enabled_modules'),
            );
            self::assertSame(
                'elasticsearch',
                \arElasticSearchPluginConfiguration::$config['server'][
                    'host'
                ],
            );
            self::assertSame(
                [
                    'cache_limiter' => '0',
                    'name' => 'symfony',
                    'cookie_secure' => 'auto',
                    'cookie_httponly' => true,
                    'cookie_samesite' => 'lax',
                ],
                $container->getParameter('session.storage.options'),
            );

            $router = $container->get('router');
            self::assertInstanceOf(RouterInterface::class, $router);
            $match = $router->match('/peanut/fileList');
            self::assertSame(
                'informationobject/action',
                $match['_route'],
            );
            self::assertSame('informationobject', $match['module']);
            self::assertSame(
                'QubitResourceRoute',
                $match[RouteCompiler::CLASS_ATTRIBUTE],
            );
            self::assertSame('peanut', $match['slug']);
            self::assertSame('fileList', $match['action']);
            self::assertArrayHasKey('_controller', $match);
        } finally {
            $kernel->shutdown();
        }
    }

    public function testDispatchesUnchangedAtoMAction(): void
    {
        $kernel = $this->kernel();

        try {
            $request = Request::create(
                '/default/privacyMessageDismiss',
                'POST',
            );
            $response = $kernel->handle($request);

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('', $response->getContent());
            self::assertTrue(
                $kernel
                    ->getContainer()
                    ->get(User::class)
                    ->getAttribute('privacy_message_dismissed'),
            );
            self::assertSame(
                'default',
                $kernel
                    ->getContainer()
                    ->get(User::class)
                    ->getAttribute(
                        'moduleName',
                        null,
                        'sfHistoryPlugin',
                    ),
            );
        } finally {
            $kernel->shutdown();
        }
    }

    public function testConfiguresTrustedIngress(): void
    {
        $_SERVER['ATOM_TRUSTED_PROXIES'] = '10.0.0.0/8';
        $_SERVER['ATOM_TRUSTED_HOSTS'] = '^archive\\.example$';
        $kernel = $this->kernel();

        try {
            $kernel->boot();

            self::assertSame(
                ['10.0.0.0/8'],
                Request::getTrustedProxies(),
            );
            self::assertSame(
                ['{^archive\\.example$}i'],
                Request::getTrustedHosts(),
            );

            $request = Request::create('/', 'GET', server: [
                'REMOTE_ADDR' => '10.1.2.3',
                'HTTP_HOST' => 'archive.example',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.8',
                'HTTP_X_FORWARDED_PROTO' => 'https',
            ]);

            self::assertTrue($request->isSecure());
            self::assertSame('203.0.113.8', $request->getClientIp());
            self::assertSame('archive.example', $request->getHost());
        } finally {
            unset(
                $_SERVER['ATOM_TRUSTED_PROXIES'],
                $_SERVER['ATOM_TRUSTED_HOSTS'],
            );
            Request::setTrustedProxies([], -1);
            Request::setTrustedHosts([]);
            $kernel->shutdown();
        }
    }

    public function testSelectsOidcUserWhenPluginIsActive(): void
    {
        $previous = getenv('ATOM_ACTIVATE_OIDC_PLUGIN');
        putenv('ATOM_ACTIVATE_OIDC_PLUGIN=true');
        $kernel = $this->kernel();

        try {
            $kernel->boot();

            self::assertInstanceOf(
                'oidcUser',
                $kernel->getContainer()->get(User::class),
            );
            self::assertFalse(class_exists('sfCoreAutoload', false));
        } finally {
            false === $previous
                ? putenv('ATOM_ACTIVATE_OIDC_PLUGIN')
                : putenv('ATOM_ACTIVATE_OIDC_PLUGIN='.$previous);
            $kernel->shutdown();
        }
    }

    private function kernel(): Kernel
    {
        return new class('test', true, $this->cacheDirectory) extends Kernel {
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

            protected function getContainerClass(): string
            {
                return parent::getContainerClass()
                    .substr(md5($this->testCacheDirectory), 0, 12);
            }
        };
    }
}
