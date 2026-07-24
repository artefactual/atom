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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Framework\Autoload;

final class LegacyClassLoader
{
    private ?array $classMap = null;
    private bool $registered = false;
    private ?string $originalIncludePath = null;

    public function __construct(
        private readonly iterable $directories,
        private readonly ClassMapBuilder $classMapBuilder = new ClassMapBuilder(),
        private readonly iterable $includePaths = [],
    ) {}

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registerIncludePaths();
        spl_autoload_register([$this, 'load']);
        $this->registered = true;
    }

    public function unregister(): void
    {
        if (!$this->registered) {
            return;
        }

        spl_autoload_unregister([$this, 'load']);

        if (null !== $this->originalIncludePath) {
            set_include_path($this->originalIncludePath);
            $this->originalIncludePath = null;
        }

        $this->registered = false;
    }

    public function load(string $class): void
    {
        if (null !== $path = $this->findFile($class)) {
            require_once $path;
        }
    }

    public function findFile(string $class): ?string
    {
        $this->classMap ??= $this->classMapBuilder->build(
            $this->directories,
        );

        return $this->classMap[strtolower(ltrim($class, '\\'))] ?? null;
    }

    private function registerIncludePaths(): void
    {
        $paths = [];

        foreach ($this->includePaths as $path) {
            if (is_string($path) && is_dir($path)) {
                $paths[] = $path;
            }
        }

        if ([] === $paths) {
            return;
        }

        $this->originalIncludePath = get_include_path();
        set_include_path(implode(\PATH_SEPARATOR, [
            ...$paths,
            $this->originalIncludePath,
        ]));
    }
}
