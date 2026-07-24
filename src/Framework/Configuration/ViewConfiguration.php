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

namespace Atom\Framework\Configuration;

final readonly class ViewConfiguration
{
    public function __construct(
        private ModuleConfigurationLoader $loader,
        private ConfigurationMerger $merger,
    ) {}

    public function for(
        string $module,
        string $action,
        string $view,
    ): array {
        $configuration = $this->loader->load($module, 'view.yml');
        $name = $action.$view;

        return $this->merger->merge(
            $this->section($configuration, 'default'),
            $this->section($configuration, 'all'),
            $this->section($configuration, $name),
        );
    }

    private function section(array $configuration, string $name): array
    {
        $section = $configuration[$name] ?? [];

        return is_array($section) ? $section : [];
    }
}
