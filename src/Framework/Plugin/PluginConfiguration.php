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

use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Bridge\RuntimeConfiguration;

class PluginConfiguration
{
    protected EventDispatcher $dispatcher;
    protected RuntimeConfiguration $configuration;
    protected string $rootDir;
    protected string $name;

    public function __construct(
        RuntimeConfiguration $configuration,
        string $rootDirectory = '',
        string $name = '',
    ) {
        $this->configuration = $configuration;
        $this->dispatcher = new EventDispatcher();
        $this->rootDir = $rootDirectory;
        $this->name = $name;
    }

    public function initialize() {}

    public function initializeAutoload() {}
}
