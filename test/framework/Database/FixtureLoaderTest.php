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

namespace Atom\Tests\Framework\Database;

use Atom\Framework\Database\FixtureLoader;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FixtureLoaderTest extends TestCase
{
    public function testFindsSortedUniqueYamlFiles(): void
    {
        vfsStream::setup('root', null, [
            'fixtures' => [
                'b.yml' => 'b: true',
                'a.yml' => 'a: true',
                'ignored.txt' => 'ignored',
            ],
        ]);
        $directory = vfsStream::url('root/fixtures');

        self::assertSame([
            $directory.'/a.yml',
            $directory.'/b.yml',
        ], (new FixtureLoader('/project'))->files([
            $directory,
            $directory.'/a.yml',
            '/missing',
        ]));
    }
}
