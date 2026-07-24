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

final readonly class LegacyClassDirectories implements \IteratorAggregate
{
    private string $projectDirectory;

    public function __construct(string $projectDirectory)
    {
        $this->projectDirectory = rtrim($projectDirectory, '/\\');
    }

    public function getIterator(): \Traversable
    {
        yield from $this->all();
    }

    public function all(): array
    {
        $directories = [];

        // Propel remains the ORM for the existing generated model classes.
        // Deliberately index only its runtime, never the Symfony 1.x runtime.
        $this->appendIfDirectory(
            $directories,
            $this->projectDirectory
                .'/vendor/symfony/lib/plugins/sfPropelPlugin/lib/vendor/propel',
        );

        foreach ($this->pluginDirectories() as $pluginDirectory) {
            $this->appendIfDirectory(
                $directories,
                $pluginDirectory.'/lib',
            );
            $this->appendModuleDirectories(
                $directories,
                $pluginDirectory.'/modules',
            );
        }

        $this->appendIfDirectory(
            $directories,
            $this->projectDirectory.'/lib',
        );
        $this->appendIfDirectory(
            $directories,
            $this->projectDirectory.'/apps/qubit/lib',
        );
        $this->appendModuleDirectories(
            $directories,
            $this->projectDirectory.'/apps/qubit/modules',
        );

        return array_values(array_unique($directories));
    }

    public function includePaths(): array
    {
        $directory = $this->projectDirectory
            .'/vendor/symfony/lib/plugins/sfPropelPlugin/lib/vendor';

        return is_dir($directory) ? [$directory] : [];
    }

    private function appendModuleDirectories(
        array &$directories,
        string $modulesDirectory,
    ): void {
        $modules = glob($modulesDirectory.'/*', \GLOB_ONLYDIR) ?: [];
        sort($modules);

        foreach ($modules as $moduleDirectory) {
            $this->appendIfDirectory(
                $directories,
                $moduleDirectory.'/lib',
            );
            $this->appendIfDirectory(
                $directories,
                $moduleDirectory.'/actions',
            );
            $this->appendIfDirectory(
                $directories,
                $moduleDirectory.'/view',
            );
        }
    }

    private function appendIfDirectory(
        array &$directories,
        string $directory,
    ): void {
        if (is_dir($directory)) {
            $directories[] = $directory;
        }
    }

    private function pluginDirectories(): array
    {
        $plugins = glob(
            $this->projectDirectory.'/plugins/*Plugin',
            \GLOB_ONLYDIR,
        ) ?: [];
        sort($plugins);

        return $plugins;
    }
}
