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

namespace Atom\Tests\Framework\Cache;

use Atom\Framework\Cache\PhpArrayFileCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class PhpArrayFileCacheTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir()
            .'/atom-php-array-cache-'.bin2hex(random_bytes(8));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->directory);
    }

    public function testSharesRememberedArraysAcrossInstances(): void
    {
        $calls = 0;
        $factory = static function () use (&$calls): array {
            ++$calls;

            return ['fixture' => true];
        };

        self::assertSame(
            ['fixture' => true],
            (new PhpArrayFileCache($this->directory))->remember(
                'fixture',
                $factory,
            ),
        );
        self::assertSame(
            ['fixture' => true],
            (new PhpArrayFileCache($this->directory))->remember(
                'fixture',
                $factory,
            ),
        );
        self::assertSame(1, $calls);
    }

    public function testRebuildsStaleDevelopmentEntries(): void
    {
        $dependency = $this->directory.'/settings.yml';
        (new Filesystem())->dumpFile($dependency, 'enabled: true');
        $cache = new PhpArrayFileCache($this->directory.'/cache');

        self::assertSame(
            ['version' => 1],
            $cache->remember(
                'settings',
                static fn (): array => ['version' => 1],
                [$dependency],
                true,
            ),
        );

        touch($dependency, time() + 2);

        self::assertSame(
            ['version' => 2],
            $cache->remember(
                'settings',
                static fn (): array => ['version' => 2],
                [$dependency],
                true,
            ),
        );
    }
}
