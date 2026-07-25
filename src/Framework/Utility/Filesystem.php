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

namespace Atom\Framework\Utility;

final class Filesystem
{
    public function relativeSymlink(
        string $originDirectory,
        string $targetDirectory,
        bool $copyOnWindows = false,
    ): void {
        $originDirectory = realpath($originDirectory)
            ?: throw new \RuntimeException(sprintf(
                'Link source "%s" was not found.',
                $originDirectory,
            ));

        if ('\\' === \DIRECTORY_SEPARATOR && $copyOnWindows) {
            $this->mirror($originDirectory, $targetDirectory);

            return;
        }

        $parent = dirname($targetDirectory);

        if (!is_dir($parent) && !mkdir($parent, 0777, true) && !is_dir($parent)) {
            throw new \RuntimeException(sprintf(
                'Unable to create link directory "%s".',
                $parent,
            ));
        }

        $relativeOrigin = $this->relativePath($parent, $originDirectory);
        $this->symlink($relativeOrigin, $targetDirectory);
    }

    public function symlink(
        string $originDirectory,
        string $targetDirectory,
    ): void {
        if (is_link($targetDirectory)) {
            if (readlink($targetDirectory) === $originDirectory) {
                return;
            }

            if (!unlink($targetDirectory)) {
                throw new \RuntimeException(sprintf(
                    'Unable to replace symbolic link "%s".',
                    $targetDirectory,
                ));
            }
        } elseif (file_exists($targetDirectory)) {
            throw new \RuntimeException(sprintf(
                'Link target "%s" already exists.',
                $targetDirectory,
            ));
        }

        if (!symlink($originDirectory, $targetDirectory)) {
            throw new \RuntimeException(sprintf(
                'Unable to create symbolic link "%s".',
                $targetDirectory,
            ));
        }
    }

    private function mirror(
        string $originDirectory,
        string $targetDirectory,
    ): void {
        if (
            !is_dir($targetDirectory)
            && !mkdir($targetDirectory, 0777, true)
            && !is_dir($targetDirectory)
        ) {
            throw new \RuntimeException(sprintf(
                'Unable to create directory "%s".',
                $targetDirectory,
            ));
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $originDirectory,
                \FilesystemIterator::SKIP_DOTS,
            ),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $item) {
            $relative = substr(
                $item->getPathname(),
                strlen($originDirectory) + 1,
            );
            $target = $targetDirectory.\DIRECTORY_SEPARATOR.$relative;

            if ($item->isDir()) {
                if (!is_dir($target) && !mkdir($target, 0777, true)) {
                    throw new \RuntimeException(sprintf(
                        'Unable to create directory "%s".',
                        $target,
                    ));
                }

                continue;
            }

            if (!copy($item->getPathname(), $target)) {
                throw new \RuntimeException(sprintf(
                    'Unable to copy "%s" to "%s".',
                    $item->getPathname(),
                    $target,
                ));
            }
        }
    }

    private function relativePath(string $from, string $to): string
    {
        $fromParts = preg_split(
            '~[\\\\/]+~',
            rtrim($from, '\\/'),
        ) ?: [];
        $toParts = preg_split(
            '~[\\\\/]+~',
            rtrim($to, '\\/'),
        ) ?: [];

        while (
            [] !== $fromParts
            && [] !== $toParts
            && $fromParts[0] === $toParts[0]
        ) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        return implode(\DIRECTORY_SEPARATOR, [
            ...array_fill(0, count($fromParts), '..'),
            ...$toParts,
        ]);
    }
}
