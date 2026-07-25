<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

declare(strict_types=1);

namespace Atom\Tests\Framework\Configuration;

use Atom\Framework\Configuration\ConfigurationException;
use Atom\Framework\Configuration\RuntimeOptions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RuntimeOptionsTest extends TestCase
{
    public function testDefaultsToPersistentFileSessions(): void
    {
        self::assertSame(
            [
                'storage' => 'file',
                'save_path' => '/srv/atom/var/sessions/prod',
            ],
            (new RuntimeOptions())->session('/srv/atom', 'prod'),
        );
    }

    public function testUsesConfiguredFileSessionPath(): void
    {
        self::assertSame(
            [
                'storage' => 'file',
                'save_path' => '/sessions',
            ],
            (new RuntimeOptions([
                'ATOM_SESSION_STORAGE' => 'file',
                'ATOM_SESSION_PATH' => '/sessions',
                'ATOM_MEMCACHED_HOST' => 'memcached',
            ]))->session('/srv/atom', 'prod'),
        );
    }

    public function testUsesExistingMemcacheEndpointForSharedSessions(): void
    {
        self::assertSame(
            [
                'storage' => 'memcache',
                'host' => 'memcached',
                'port' => 11212,
                'ttl' => 3600,
                'prefix' => 'archive_session:',
            ],
            (new RuntimeOptions([
                'ATOM_MEMCACHED_HOST' => 'tcp://memcached:11212',
                'ATOM_SESSION_TTL' => '3600',
                'ATOM_SESSION_PREFIX' => 'archive_session:',
            ]))->session('/srv/atom', 'prod'),
        );
    }

    public function testParsesIpv6MemcacheEndpoint(): void
    {
        $session = (new RuntimeOptions([
            'ATOM_MEMCACHED_HOST' => '[2001:db8::1]:11211',
        ]))->session('/srv/atom', 'prod');

        self::assertSame('2001:db8::1', $session['host']);
        self::assertSame(11211, $session['port']);
    }

    /**
     * @dataProvider invalidSessionConfigurationProvider
     */
    public function testRejectsInvalidSessionConfiguration(
        array $environment,
        string $message,
    ): void {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage($message);

        (new RuntimeOptions($environment))->session('/srv/atom', 'prod');
    }

    public static function invalidSessionConfigurationProvider(): iterable
    {
        yield 'storage' => [
            ['ATOM_SESSION_STORAGE' => 'database'],
            'ATOM_SESSION_STORAGE',
        ];

        yield 'missing endpoint' => [
            ['ATOM_SESSION_STORAGE' => 'memcache'],
            'ATOM_MEMCACHED_HOST is required',
        ];

        yield 'invalid endpoint' => [
            ['ATOM_MEMCACHED_HOST' => 'https://cache.example.com'],
            'not a valid TCP endpoint',
        ];

        yield 'invalid ttl' => [
            [
                'ATOM_MEMCACHED_HOST' => 'memcached',
                'ATOM_SESSION_TTL' => '0',
            ],
            'ATOM_SESSION_TTL must be a positive integer',
        ];

        yield 'invalid prefix' => [
            [
                'ATOM_MEMCACHED_HOST' => 'memcached',
                'ATOM_SESSION_PREFIX' => 'not safe',
            ],
            'ATOM_SESSION_PREFIX contains characters',
        ];
    }

    public function testReadsAtoMProxyAndHostLists(): void
    {
        $options = new RuntimeOptions([
            'ATOM_TRUSTED_PROXIES' => '10.0.0.0/8, 192.168.1.10',
            'ATOM_TRUSTED_HOSTS' => '^archive\\.example$, ^admin\\.',
        ]);

        self::assertSame(
            ['10.0.0.0/8', '192.168.1.10'],
            $options->trustedProxies(),
        );
        self::assertSame(
            ['^archive\\.example$', '^admin\\.'],
            $options->trustedHosts(),
        );
    }

    public function testSupportsSymfonyProxyEnvironmentFallbacks(): void
    {
        $options = new RuntimeOptions([
            'SYMFONY_TRUSTED_PROXIES' => '127.0.0.1',
            'SYMFONY_TRUSTED_HOSTS' => '^localhost$',
        ]);

        self::assertSame(['127.0.0.1'], $options->trustedProxies());
        self::assertSame(['^localhost$'], $options->trustedHosts());
    }
}
