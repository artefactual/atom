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

final readonly class RuntimeConfiguration
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

    public function __construct(
        private string $application,
        private string $environment,
        private array $plugins,
        private bool $debug,
        private string $projectDirectory,
    ) {}

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
}
