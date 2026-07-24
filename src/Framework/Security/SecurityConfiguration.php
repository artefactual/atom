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

namespace Atom\Framework\Security;

use Atom\Framework\Configuration\ModuleConfigurationLoader;

final readonly class SecurityConfiguration
{
    public function __construct(
        private ModuleConfigurationLoader $configuration,
    ) {}

    public function forModule(string $module): array
    {
        $configuration = $this->configuration->load(
            $module,
            'security.yml',
        );
        $normalized = [];

        foreach ($configuration as $action => $values) {
            if (is_array($values)) {
                $normalized[strtolower((string) $action)] =
                    array_change_key_case($values, \CASE_LOWER);
            }
        }

        return $normalized;
    }

    public function forAction(string $module, string $action): array
    {
        $configuration = $this->forModule($module);

        return array_replace(
            $configuration['default'] ?? [],
            $configuration['all'] ?? [],
            $configuration[strtolower($action)] ?? [],
        );
    }
}
