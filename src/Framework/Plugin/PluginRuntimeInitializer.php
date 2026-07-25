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

namespace Atom\Framework\Plugin;

use Atom\Framework\Bridge\RuntimeConfiguration;

final readonly class PluginRuntimeInitializer
{
    public function __construct(
        private RuntimeConfiguration $configuration,
    ) {}

    public function initialize(): void
    {
        RuntimeConfiguration::setActive($this->configuration);
        $this->initializeSearch();
        $this->initializePluginAdmin();
        $this->initializeOai();
    }

    private function initializeSearch(): void
    {
        if (
            !$this->configuration->isPluginEnabled(
                'arElasticSearchPlugin',
            )
            || !class_exists('arElasticSearchPluginConfiguration')
        ) {
            return;
        }

        \arElasticSearchPluginConfiguration::$config = $this
            ->configuration
            ->loadConfiguration('config/search.yml');
    }

    private function initializePluginAdmin(): void
    {
        if (
            $this->configuration->isPluginEnabled('sfPluginAdminPlugin')
            && class_exists('sfPluginAdminPluginConfiguration')
        ) {
            \sfPluginAdminPluginConfiguration::$pluginNames = PluginRegistry::builtIn(
                $this->configuration->getPlugins(),
            );
        }
    }

    private function initializeOai(): void
    {
        if (
            !$this->configuration->isPluginEnabled('arOaiPlugin')
            || !class_exists('QubitOai')
            || !class_exists('QubitOaiTopLevelSet')
        ) {
            return;
        }

        \QubitOai::addOaiSet(new \QubitOaiTopLevelSet());
    }
}
