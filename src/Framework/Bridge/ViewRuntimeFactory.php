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

namespace Atom\Framework\Bridge;

use Atom\Framework\Configuration\ViewConfiguration;
use Atom\Framework\Module\ComponentLocator;
use Atom\Framework\Module\LayoutLocator;
use Atom\Framework\Module\TemplateLocator;

final readonly class ViewRuntimeFactory
{
    public function __construct(
        private ComponentLocator $components,
        private TemplateLocator $templates,
        private LayoutLocator $layouts,
        private TemplateRenderer $renderer,
        private ViewConfiguration $configuration,
        private AssetRenderer $assets,
        private ?ViewCacheManager $cacheManager = null,
    ) {}

    public function create(Context $context): ViewRuntime
    {
        return new ViewRuntime(
            $context,
            $this->components,
            $this->templates,
            $this->layouts,
            $this->renderer,
            $this->configuration,
            $this->assets,
            $this->cacheManager,
        );
    }
}
