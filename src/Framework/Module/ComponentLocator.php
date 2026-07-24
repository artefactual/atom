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

namespace Atom\Framework\Module;

final readonly class ComponentLocator
{
    public function __construct(private ModuleDirectories $directories) {}

    public function find(
        string $module,
        string $component,
    ): ?ComponentDescriptor {
        $this->validateName($module, 'module');
        $this->validateName($component, 'component');

        foreach ($this->directories->actions($module) as $directory) {
            $path = $directory.'/'.$component.'Component.class.php';

            if (is_readable($path)) {
                return new ComponentDescriptor(
                    $module,
                    $component,
                    $module.$component.'Component',
                    $path,
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
