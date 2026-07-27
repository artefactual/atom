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

namespace Atom\Framework\Configuration;

use Atom\Framework\Cache\PhpArrayFileCache;

final class ConfigurationPathResolver
{
    private string $projectDirectory;
    private string $frameworkDirectory;
    private array $resolved = [];

    public function __construct(
        string $projectDirectory,
        private readonly string $application,
        private readonly array $plugins = [],
        ?string $frameworkDirectory = null,
        private readonly ?PhpArrayFileCache $cache = null,
    ) {
        $this->projectDirectory = rtrim($projectDirectory, '/\\');
        $this->frameworkDirectory = rtrim(
            $frameworkDirectory
                ?? $this->projectDirectory.'/src/Framework/Resources',
            '/\\',
        );

        $this->validateName($this->application, 'application');

        foreach ($this->plugins as $plugin) {
            if (!is_string($plugin)) {
                throw new ConfigurationException(
                    'Plugin names must be strings.',
                );
            }

            $this->validateName($plugin, 'plugin');
        }
    }

    public function resolve(string $configPath): array
    {
        $configPath = $this->normalizeConfigPath($configPath);

        return $this->resolved[$configPath] ??= null === $this->cache
            ? $this->resolveUncached($configPath)
            : $this->cache->remember(
                'configuration-paths-v1:'.hash('sha256', serialize([
                    $this->projectDirectory,
                    $this->application,
                    $this->plugins,
                    $this->frameworkDirectory,
                    $configPath,
                ])),
                fn (): array => $this->resolveUncached($configPath),
            );
    }

    private function resolveUncached(string $configPath): array
    {
        $globalConfigPath = basename(dirname($configPath))
            .'/'.basename($configPath);

        $candidates = [
            $this->frameworkDirectory.'/'.$globalConfigPath,
        ];

        foreach ($this->pluginDirectories() as $pluginDirectory) {
            $candidates[] = $pluginDirectory.'/'.$globalConfigPath;
        }

        $candidates[] = $this->projectDirectory.'/'.$globalConfigPath;
        $candidates[] = $this->projectDirectory.'/'.$configPath;
        $candidates[] = $this->applicationDirectory()
            .'/'.$globalConfigPath;

        foreach ($this->pluginDirectories() as $pluginDirectory) {
            $candidates[] = $pluginDirectory.'/'.$configPath;
        }

        $candidates[] = $this->applicationDirectory().'/'.$configPath;

        return array_values(array_filter(
            array_unique($candidates),
            is_readable(...),
        ));
    }

    private function applicationDirectory(): string
    {
        return $this->projectDirectory.'/apps/'.$this->application;
    }

    private function pluginDirectories(): array
    {
        return array_map(
            fn (string $plugin): string => $this->projectDirectory
                .'/plugins/'.$plugin,
            $this->plugins,
        );
    }

    private function normalizeConfigPath(string $configPath): string
    {
        $configPath = str_replace('\\', '/', $configPath);

        if (
            '' === $configPath
            || str_starts_with($configPath, '/')
            || preg_match('/^[a-z]:\//i', $configPath)
            || in_array('..', explode('/', $configPath), true)
        ) {
            throw new ConfigurationException(sprintf(
                'Configuration path "%s" must be a relative project path.',
                $configPath,
            ));
        }

        return ltrim($configPath, './');
    }

    private function validateName(string $name, string $type): void
    {
        if (1 !== preg_match('/^[a-z0-9_.-]+$/i', $name)) {
            throw new ConfigurationException(sprintf(
                'Invalid %s name "%s".',
                $type,
                $name,
            ));
        }
    }
}
