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

namespace Atom\Framework\Console;

final readonly class TaskDiscovery
{
    public function __construct(private string $projectDirectory) {}

    public function classes(array $plugins): array
    {
        $directories = [$this->projectDirectory.'/lib/task'];

        foreach ($plugins as $plugin) {
            $directories[] = $this->projectDirectory
                .'/plugins/'.$plugin.'/lib/task';
        }

        $classes = [];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $directory,
                    \FilesystemIterator::SKIP_DOTS,
                ),
            );

            foreach ($files as $file) {
                if (
                    !$file->isFile()
                    || !str_ends_with(
                        $file->getFilename(),
                        'Task.class.php',
                    )
                ) {
                    continue;
                }

                foreach ($this->classesInFile($file->getPathname()) as $class) {
                    $classes[] = $class;
                }
            }
        }

        sort($classes);

        return array_values(array_unique($classes));
    }

    private function classesInFile(string $path): array
    {
        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new \RuntimeException(sprintf(
                'Unable to read task file "%s".',
                $path,
            ));
        }

        $tokens = token_get_all($contents);
        $classes = [];

        foreach ($tokens as $index => $token) {
            if (!is_array($token) || \T_CLASS !== $token[0]) {
                continue;
            }

            for (++$index, $count = count($tokens); $index < $count; ++$index) {
                if (
                    is_array($tokens[$index])
                    && \T_STRING === $tokens[$index][0]
                ) {
                    $classes[] = $tokens[$index][1];

                    break;
                }
            }
        }

        return $classes;
    }
}
