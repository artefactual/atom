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

namespace Atom\Tests\Framework\Bridge;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AssetFunctionsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 3)
            .'/src/Framework/Bridge/functions.php';
    }

    public function testAddsDefaultImageExtensionToBareName(): void
    {
        self::assertSame('/images/preview.png', image_path('preview'));
    }

    public function testAddsDefaultImageExtensionToRootRelativePath(): void
    {
        self::assertSame(
            '/plugins/theme/images/image.png',
            image_path('/plugins/theme/images/image'),
        );
    }

    public function testAddsImageExtensionBeforeQueryString(): void
    {
        self::assertSame(
            '/plugins/theme/images/image.png?v=1',
            image_path('/plugins/theme/images/image?v=1'),
        );
    }

    public function testPreservesExplicitExtensionAndRemoteUrl(): void
    {
        self::assertSame('/images/preview.svg', image_path('/images/preview.svg'));
        self::assertSame(
            'https://example.com/preview',
            image_path('https://example.com/preview'),
        );
    }
}
