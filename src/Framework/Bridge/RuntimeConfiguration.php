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

namespace Atom\Framework\Bridge;

use Atom\Framework\Configuration\ApplicationConfiguration;
use Atom\Framework\Configuration\ConfigurationMerger;
use Atom\Framework\Configuration\ConfigurationPathResolver;
use Atom\Framework\Configuration\ConstantReplacer;
use Atom\Framework\Configuration\HybridYamlFileLoader;
use Atom\Framework\Configuration\ParameterCompiler;

final class RuntimeConfiguration
{
    private const STANDARD_HELPERS = [
        'Asset',
        'Cache',
        'Date',
        'Debug',
        'Escaping',
        'Helper',
        'I18N',
        'Javascript',
        'Number',
        'Partial',
        'Tag',
        'Text',
        'Url',
    ];

    private static ?self $active = null;

    public function __construct(
        private readonly string $application,
        private readonly string $environment,
        private readonly array $plugins,
        private readonly bool $debug,
        private readonly string $projectDirectory,
    ) {}

    public static function setActive(?self $configuration): void
    {
        self::$active = $configuration;
    }

    public static function getApplicationConfiguration(
        string $application = 'qubit',
        string $environment = 'cli',
        bool $debug = false,
        mixed ...$ignored,
    ): self {
        if (null === self::$active) {
            throw new BridgeException(
                'No AtoM runtime configuration is active.',
            );
        }

        return self::$active;
    }

    public function getApplication(): string
    {
        return $this->application;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isPluginEnabled(string $plugin): bool
    {
        return in_array($plugin, $this->plugins, true);
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function loadHelpers(array|string $helpers): void
    {
        foreach ((array) $helpers as $helper) {
            if (is_array($helper)) {
                $this->loadHelpers($helper);

                continue;
            }

            if (
                !is_string($helper)
                || 1 !== preg_match('/^[a-z0-9_]+$/i', $helper)
            ) {
                throw new BridgeException(
                    'Helper names must be safe strings.',
                );
            }

            if (in_array($helper, self::STANDARD_HELPERS, true)) {
                continue;
            }

            $path = rtrim($this->projectDirectory, '/\\')
                .'/lib/helper/'.$helper.'Helper.php';

            if (!is_readable($path)) {
                throw new BridgeException(sprintf(
                    'Helper "%s" was not found.',
                    $helper,
                ));
            }

            require_once $path;
        }
    }

    public function getPlugins(): array
    {
        return $this->plugins;
    }

    public function getRootDir(): string
    {
        return $this->projectDirectory;
    }

    public function getConfigPaths(string $path): array
    {
        return $this->pathResolver()->resolve($path);
    }

    public function getPluginPaths(): array
    {
        return $this->getAllPluginPaths();
    }

    public function getAllPluginPaths(): array
    {
        return array_combine(
            $this->plugins,
            array_map(
                fn (string $plugin): string => $this->projectDirectory
                    .'/plugins/'.$plugin,
                $this->plugins,
            ),
        ) ?: [];
    }

    public function getPluginSubPaths(string $path): array
    {
        return array_values(array_filter(array_map(
            static fn (string $pluginPath): string => rtrim(
                $pluginPath,
                '/\\',
            ).'/'.ltrim($path, '/\\'),
            $this->getAllPluginPaths(),
        ), is_dir(...)));
    }

    public function loadConfiguration(string $path): array
    {
        return (new ApplicationConfiguration(
            $this->pathResolver(),
            new HybridYamlFileLoader(),
            new ConfigurationMerger(),
            new ConstantReplacer(),
            new ParameterCompiler(),
            $this->environment,
            Configuration::getAll(),
        ))->load($path);
    }

    private function pathResolver(): ConfigurationPathResolver
    {
        return new ConfigurationPathResolver(
            $this->projectDirectory,
            $this->application,
            $this->plugins,
        );
    }
}
