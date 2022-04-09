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

namespace AccessToMemory;

/**
 * Filesystem path abstraction class.
 */
class Path
{
    public $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function __toString()
    {
        return $this->path;
    }

    /**
     * Check if path exists.
     *
     * @return bool true if path exists
     */
    public function exists()
    {
        return file_exists($this->path);
    }

    /**
     * Check if path references a directory.
     *
     * @return bool true if path references a directory
     */
    public function isDir()
    {
        $this->throwExceptionIfFileNotFound();

        return is_dir($this->path);
    }

    /**
     * Check if path references a file.
     *
     * @return bool true if path references a file
     */
    public function isFile()
    {
        $this->throwExceptionIfFileNotFound();

        return is_file($this->path);
    }

    /**
     * Return directory listing as an array of Path objects.
     *
     * @param array $exclude from list
     *
     * @return array directory contents as Path objects
     */
    public function ls($exclude = ['.', '..'])
    {
        $this->throwExceptionIfNotADir();
        $items = [];

        foreach (scandir($this->path) as $item) {
            // Remove $excluded items
            if (in_array($item, $exclude)) {
                continue;
            }

            // Convert file/dir names to Path objects
            $items[] = new Path($this->path.DIRECTORY_SEPARATOR.$item);
        }

        return $items;
    }

    /**
     * Remove a directory.
     *
     * @param bool $recursive delete a directory and all its contents when true
     */
    public function rmdir($recursive = false)
    {
        $this->throwExceptionIfNotADir();

        if ($recursive) {
            foreach ($this->ls() as $child) {
                $child->delete($recursive);
            }
        }

        if (!empty($this->ls())) {
            throw new \RuntimeException(
                sprintf(
                    "Can't delete %s: directory is not empty",
                    $this->path
                )
            );
        }

        rmdir($this->path);
    }

    /**
     * Delete a file.
     */
    public function unlink()
    {
        $this->throwExceptionIfNotAFile();

        unlink($this->path);
    }

    /**
     * Delete a file or a directory.
     *
     * @param bool $recursive if true, delete a directory and all its contents
     */
    public function delete($recursive = false)
    {
        if (is_dir($this->path)) {
            $this->rmdir($recursive);
        } else {
            $this->unlink();
        }
    }

    protected function throwExceptionIfFileNotFound()
    {
        if (!$this->exists()) {
            throw new \RuntimeException(
                sprintf('File not found: %s', $this->path)
            );
        }
    }

    protected function throwExceptionIfNotADir()
    {
        if (!$this->isDir()) {
            throw new \RuntimeException(
                sprintf('%s is not a directory', $this->path)
            );
        }
    }

    protected function throwExceptionIfNotAFile()
    {
        if (!$this->isFile()) {
            throw new \RuntimeException(
                sprintf('%s is not a file', $this->path)
            );
        }
    }
}
