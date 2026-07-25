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

namespace Atom\Tests\Framework\Utility;

use Atom\Framework\Utility\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FilesystemTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/atom-filesystem-'.bin2hex(
            random_bytes(8),
        );
        mkdir($this->directory.'/plugin/web', 0777, true);
        mkdir($this->directory.'/public', 0777, true);
        file_put_contents($this->directory.'/plugin/web/asset.css', 'asset');
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->directory,
                \FilesystemIterator::SKIP_DOTS,
            ),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if ($item->isLink() || $item->isFile()) {
                unlink($item->getPathname());
            } else {
                rmdir($item->getPathname());
            }
        }

        rmdir($this->directory);
    }

    public function testCreatesIdempotentRelativeSymlink(): void
    {
        $filesystem = new Filesystem();
        $target = $this->directory.'/public/plugin';
        $filesystem->relativeSymlink(
            $this->directory.'/plugin/web',
            $target,
        );
        $filesystem->relativeSymlink(
            $this->directory.'/plugin/web',
            $target,
        );

        self::assertTrue(is_link($target));
        self::assertSame('../plugin/web', readlink($target));
        self::assertSame('asset', file_get_contents($target.'/asset.css'));
    }

    public function testRefusesToReplaceRegularTarget(): void
    {
        $target = $this->directory.'/public/plugin';
        file_put_contents($target, 'keep');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already exists');

        (new Filesystem())->relativeSymlink(
            $this->directory.'/plugin/web',
            $target,
        );
    }
}
