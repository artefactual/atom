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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Qubit
 */
class QubitTest extends TestCase
{
    private $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testSaveTemporaryFilePreservesSafeExtension(): void
    {
        $path = $this->saveTemporaryFile('document.pdf', 'contents');

        $this->assertMatchesRegularExpression('/^QUBIT.+\.pdf$/', basename($path));
        $this->assertSame('contents', file_get_contents($path));
    }

    public function testSaveTemporaryFileStripsUnsafeExtensionSuffix(): void
    {
        $path = $this->saveTemporaryFile('document.pdf;id', 'contents');

        $this->assertMatchesRegularExpression('/^QUBIT.+\.pdf$/', basename($path));
        $this->assertStringNotContainsString(';', basename($path));
    }

    public function testSaveTemporaryFileAllowsExtensionlessNames(): void
    {
        $path = $this->saveTemporaryFile('document', 'contents');

        $this->assertMatchesRegularExpression('/^QUBIT[^.]+$/', basename($path));
        $this->assertSame('contents', file_get_contents($path));
    }

    private function saveTemporaryFile(string $name, string $contents): string
    {
        $path = Qubit::saveTemporaryFile($name, $contents);
        $this->assertNotFalse($path);
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
