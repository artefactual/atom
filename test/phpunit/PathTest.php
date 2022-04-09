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

use AccessToMemory\Path;
use org\bovigo\vfs\vfsStream;

// require_once 'lib/Path.class.php';

/**
 * @covers \AccessToMemory\Path
 *
 * @internal
 */
class PathTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        // define virtual file system
        $directory = [
            'subdir' => [
                'foo.txt' => 'foo',
            ],
            'empty_dir' => [],
            'bar.txt' => 'bar',
        ];

        // setup and cache the virtual file system
        $this->vfs = vfsStream::setup('root', null, $directory);
    }

    public function testSetPathFromConstructor()
    {
        $path = new Path('testdir');
        $this->assertSame('testdir', $path->path);
    }

    public function testExists()
    {
        // Directory
        $path = new Path($this->vfs->url().'/empty_dir');
        $this->assertTrue($path->exists());

        // File
        $path = new Path($this->vfs->url().'/bar.txt');
        $this->assertTrue($path->exists());

        // Should return false for non-existent file
        $path = new Path($this->vfs->url().'/not_here');
        $this->assertFalse($path->exists());
    }

    public function testToString()
    {
        $string = $this->vfs->url().'/subdir';
        $path = new Path($string);

        $this->assertEquals($string, (string) $path);
    }

    public function testIsDir()
    {
        $path = new Path($this->vfs->url().'/subdir');
        $this->assertTrue($path->isDir());

        $path = new Path($this->vfs->url().'/bar.txt');
        $this->assertFalse($path->isDir());

        // File not found
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File not found');
        $path = new Path($this->vfs->url().'/not_here');
        $path->isDir();
    }

    public function testIsFile()
    {
        $path = new Path($this->vfs->url().'/bar.txt');
        $this->assertTrue($path->isFile());

        $path = new Path($this->vfs->url().'/subdir');
        $this->assertFalse($path->isFile());

        // File not found
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File not found');
        $path = new Path($this->vfs->url().'/not_here');
        $path->isFile();
    }

    public function testLs()
    {
        $checklist = [
            new Path($this->vfs->url().'/subdir'),
            new Path($this->vfs->url().'/empty_dir'),
            new Path($this->vfs->url().'/bar.txt'),
        ];

        // Non-empty dir
        $path = new Path($this->vfs->url());
        $this->assertEqualsCanonicalizing($checklist, $path->ls());

        // Empty dir
        $path = new Path($this->vfs->url().'/empty_dir');
        $this->assertEquals([], $path->ls());
    }

    public function testRmdirEmpty()
    {
        $path = new Path($this->vfs->url().'/empty_dir');
        $path->rmdir();
        $this->assertFalse($this->vfs->hasChild('empty_dir'));
    }

    public function testErrorRmdirNotEmpty()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('directory is not empty');

        $path = new Path($this->vfs->url().'/subdir');
        $path->rmdir();
        $this->assertTrue($this->vfs->hasChild('subdir'));
    }

    public function testRmdirRecursive()
    {
        $path = new Path($this->vfs->url().'/subdir');
        $path->rmdir(true);
        $this->assertFalse($this->vfs->hasChild('subdir'));
    }

    public function testErrorRmdirOnFile()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not a directory');

        $path = new Path($this->vfs->url().'/bar.txt');
        $path->rmdir();
        $this->assertTrue($this->vfs->hasChild('bar.txt'));
    }

    public function testErrorRmdirFileNotFound()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File not found');

        $path = new Path($this->vfs->url().'/unknown');
        $path->rmdir();
    }

    public function testUnlink()
    {
        $path = new Path($this->vfs->url().'/bar.txt');
        $path->unlink();
        $this->assertFalse($this->vfs->hasChild('bar.txt'));
    }

    public function testErrorUnlinkOnDir()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not a file');

        $path = new Path($this->vfs->url().'/empty_dir');
        $path->unlink();
        $this->assertTrue($this->vfs->hasChild('empty_dir'));
    }

    public function testErrorUnlinkFileNotFound()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File not found');

        $path = new Path($this->vfs->url().'/unknown.txt');
        $path->unlink();
    }

    public function testDelete()
    {
        // Delete a file
        $path = new Path($this->vfs->url().'/bar.txt');
        $path->delete();
        $this->assertFalse($this->vfs->hasChild('bar.txt'));

        // Delete a dir recursively
        $path = new Path($this->vfs->url().'/subdir');
        $path->delete(true);
        $this->assertFalse($this->vfs->hasChild('subdir'));
    }
}
