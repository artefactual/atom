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

final readonly class ActionLocator
{
    public function __construct(private ModuleDirectories $directories) {}

    public function find(string $module, string $action): ?ActionDescriptor
    {
        $this->validateName($module, 'module');
        $this->validateName($action, 'action');

        foreach ($this->directories->actions($module) as $directory) {
            $path = $directory.'/'.$action.'Action.class.php';

            if (is_readable($path)) {
                return new ActionDescriptor(
                    $module,
                    $action,
                    $module.$action.'Action',
                    $path,
                );
            }

            $path = $directory.'/actions.class.php';

            if (is_readable($path)) {
                return new ActionDescriptor(
                    $module,
                    $action,
                    $module.'Actions',
                    $path,
                    true,
                );
            }
        }

        return null;
    }

    private function validateName(string $name, string $type): void
    {
        if (1 !== preg_match('/^[a-z0-9_]+$/i', $name)) {
            throw new ModuleException(sprintf(
                'Invalid %s name "%s".',
                $type,
                $name,
            ));
        }
    }
}
