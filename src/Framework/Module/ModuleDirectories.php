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

namespace Atom\Framework\Module;

final readonly class ModuleDirectories
{
    private string $projectDirectory;

    public function __construct(
        string $projectDirectory,
        private string $application,
        private array $plugins = [],
    ) {
        $this->projectDirectory = rtrim($projectDirectory, '/\\');
        $this->validateName($this->application, 'application');

        foreach ($this->plugins as $plugin) {
            if (!is_string($plugin)) {
                throw new ModuleException('Plugin names must be strings.');
            }

            $this->validateName($plugin, 'plugin');
        }
    }

    public function actions(string $module): array
    {
        return $this->moduleDirectories($module, 'actions');
    }

    public function templates(string $module): array
    {
        return $this->moduleDirectories($module, 'templates');
    }

    private function moduleDirectories(
        string $module,
        string $subdirectory,
    ): array {
        $this->validateName($module, 'module');
        $directories = [];

        foreach ($this->plugins as $plugin) {
            $directory = $this->projectDirectory.'/plugins/'.$plugin
                .'/modules/'.$module.'/'.$subdirectory;

            if (is_dir($directory)) {
                $directories[] = $directory;
            }
        }

        $applicationDirectory = $this->projectDirectory
            .'/apps/'.$this->application.'/modules/'.$module
            .'/'.$subdirectory;

        if (is_dir($applicationDirectory)) {
            $directories[] = $applicationDirectory;
        }

        return $directories;
    }

    private function validateName(string $name, string $type): void
    {
        if (1 !== preg_match('/^[a-z0-9_.-]+$/i', $name)) {
            throw new ModuleException(sprintf(
                'Invalid %s name "%s".',
                $type,
                $name,
            ));
        }
    }
}
