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

namespace Atom\Framework\Cache;

final readonly class PhpArrayFileCache
{
    public function __construct(private string $directory) {}

    public function remember(
        string $key,
        callable $factory,
        array $dependencies = [],
        bool $validateDependencies = false,
    ): array {
        $path = $this->path($key);
        $dependencies = array_values(array_unique($dependencies));

        if (
            null !== $value = $this->read(
                $path,
                $key,
                $dependencies,
                $validateDependencies,
            )
        ) {
            return $value;
        }

        $this->ensureDirectory();
        $lock = fopen($path.'.lock', 'c');

        if (false === $lock) {
            throw new CacheException(sprintf(
                'Unable to open cache lock "%s".',
                $path.'.lock',
            ));
        }

        try {
            if (!flock($lock, \LOCK_EX)) {
                throw new CacheException(sprintf(
                    'Unable to lock cache file "%s".',
                    $path,
                ));
            }

            if (
                null !== $value = $this->read(
                    $path,
                    $key,
                    $dependencies,
                    $validateDependencies,
                )
            ) {
                return $value;
            }

            $value = $factory();

            if (!is_array($value)) {
                throw new CacheException(sprintf(
                    'Cache factory for "%s" must return an array.',
                    $key,
                ));
            }

            $this->write($path, [
                'key' => $key,
                'dependencies' => $this->dependencyTimes($dependencies),
                'value' => $value,
            ]);

            return $value;
        } finally {
            flock($lock, \LOCK_UN);
            fclose($lock);
        }
    }

    private function path(string $key): string
    {
        return rtrim($this->directory, '/\\')
            .'/'.hash('sha256', $key).'.php';
    }

    private function read(
        string $path,
        string $key,
        array $dependencies,
        bool $validateDependencies,
    ): ?array {
        if (!is_file($path)) {
            return null;
        }

        try {
            $record = (static fn (string $path): mixed => include $path)(
                $path,
            );
        } catch (\Throwable) {
            return null;
        }

        if (
            !is_array($record)
            || ($record['key'] ?? null) !== $key
            || !is_array($record['value'] ?? null)
        ) {
            return null;
        }

        if (
            $validateDependencies
            && ($record['dependencies'] ?? null)
                !== $this->dependencyTimes($dependencies)
        ) {
            return null;
        }

        return $record['value'];
    }

    private function dependencyTimes(array $dependencies): array
    {
        $times = [];

        foreach ($dependencies as $dependency) {
            if (!is_string($dependency)) {
                throw new CacheException(
                    'Cache dependencies must be file paths.',
                );
            }

            clearstatcache(true, $dependency);
            $times[$dependency] = filemtime($dependency);
        }

        return $times;
    }

    private function ensureDirectory(): void
    {
        if (
            !is_dir($this->directory)
            && !mkdir($this->directory, 0775, true)
            && !is_dir($this->directory)
        ) {
            throw new CacheException(sprintf(
                'Unable to create cache directory "%s".',
                $this->directory,
            ));
        }
    }

    private function write(string $path, array $record): void
    {
        $temporaryPath = tempnam($this->directory, 'cache-');
        $contents = "<?php\n\nreturn "
            .var_export($record, true)
            .";\n";

        if (
            false === $temporaryPath
            || false === file_put_contents(
                $temporaryPath,
                $contents,
                \LOCK_EX,
            )
            || !rename($temporaryPath, $path)
        ) {
            if (is_string($temporaryPath) && is_file($temporaryPath)) {
                unlink($temporaryPath);
            }

            throw new CacheException(sprintf(
                'Unable to write cache file "%s".',
                $path,
            ));
        }

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }
    }
}
