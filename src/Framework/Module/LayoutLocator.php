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

final readonly class LayoutLocator
{
    private string $projectDirectory;

    public function __construct(
        string $projectDirectory,
        private string $application,
        private array $decoratorDirectories = [],
    ) {
        $this->projectDirectory = rtrim($projectDirectory, '/\\');
        $this->validateName($application, 'application');
    }

    public function find(string $layout): ?string
    {
        return $this->findFile($layout, false);
    }

    public function findPartial(string $partial): ?string
    {
        return $this->findFile($partial, true);
    }

    private function findFile(string $name, bool $partial): ?string
    {
        $name = preg_replace('/\.php$/i', '', $name) ?? $name;
        $name = ltrim($name, '_');
        $this->validateName($name, $partial ? 'partial' : 'layout');
        $filename = ($partial ? '_' : '').$name.'.php';
        $directories = array_merge(
            $this->decoratorDirectories,
            [
                $this->projectDirectory
                    .'/apps/'.$this->application.'/templates',
            ],
        );

        foreach ($directories as $directory) {
            $path = rtrim((string) $directory, '/\\').'/'.$filename;

            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
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
