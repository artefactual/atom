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

final readonly class TemplateLocator
{
    public function __construct(private ModuleDirectories $directories) {}

    public function find(
        string $module,
        string $action,
        string $view = 'Success',
    ): ?string {
        $this->validateName($action, 'action');
        $this->validateName($view, 'view');
        $filename = $action.$view.'.php';

        foreach ($this->directories->templates($module) as $directory) {
            $path = $directory.'/'.$filename;

            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    public function findPartial(
        string $module,
        string $partial,
    ): ?string {
        $partial = preg_replace('/\.php$/i', '', $partial) ?? $partial;
        $partial = ltrim($partial, '_');
        $this->validateName($partial, 'partial');
        $filename = '_'.$partial.'.php';

        foreach ($this->directories->templates($module) as $directory) {
            $path = $directory.'/'.$filename;

            if (is_readable($path)) {
                return $path;
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
