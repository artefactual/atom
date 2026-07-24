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
use Atom\Framework\Module\ComponentDescriptor;
use Atom\Framework\Module\ComponentLocator;
use Atom\Framework\Module\LayoutLocator;
use Atom\Framework\Module\ModuleException;
use Atom\Framework\Module\TemplateLocator;

final class ViewRuntime
{
    private array $componentSlots = [];
    private bool $hasLayout = true;
    private bool $httpMetasIncluded = false;
    private bool $javaScriptsIncluded = false;
    private string $layout = 'layout';
    private bool $metasIncluded = false;
    private array $moduleStack = [];
    private ?string $preparedTitle = null;
    private array $slotStack = [];
    private array $slots = [];
    private bool $stylesheetsIncluded = false;

    public function __construct(
        private readonly Context $context,
        private readonly ComponentLocator $components,
        private readonly TemplateLocator $templates,
        private readonly LayoutLocator $layouts,
        private readonly TemplateRenderer $renderer,
        private readonly ViewConfiguration $configuration,
        private readonly AssetRenderer $assets,
    ) {}

    public function begin(
        string $module,
        string $action,
        string $view,
        bool|string|null $layout = null,
    ): void {
        $this->componentSlots = [];
        $this->hasLayout = true;
        $this->httpMetasIncluded = false;
        $this->javaScriptsIncluded = false;
        $this->layout = 'layout';
        $this->metasIncluded = false;
        $this->moduleStack = [$module];
        $this->slotStack = [];
        $this->slots = [];
        $this->stylesheetsIncluded = false;
        $configuration = $this->configuration->for(
            $module,
            $action,
            $view,
        );
        $this->hasLayout = (bool) (
            $configuration['has_layout'] ?? true
        );
        $this->layout = (string) (
            $configuration['layout'] ?? 'layout'
        );
        $this->componentSlots = is_array(
            $configuration['components'] ?? null,
        ) ? $configuration['components'] : [];

        if (is_string($layout)) {
            $this->layout = $layout;
            $this->hasLayout = true;
        } elseif (false === $layout) {
            $this->hasLayout = false;
        }

        $metas = $configuration['metas'] ?? [];

        if (is_array($metas)) {
            foreach ($metas as $name => $value) {
                if ('title' === $name) {
                    $currentTitle = $this->context
                        ->getResponse()
                        ->getTitle();

                    if (
                        '' === $currentTitle
                        || $currentTitle === $this->preparedTitle
                    ) {
                        $this->context->getResponse()->setTitle(
                            (string) $value,
                        );
                    }
                } else {
                    $this->context->getResponse()->addMeta(
                        (string) $name,
                        (string) $value,
                    );
                }
            }
        }

        $httpMetas = $configuration['http_metas'] ?? [];

        if (is_array($httpMetas)) {
            foreach ($httpMetas as $name => $value) {
                $this->context->getResponse()->addHttpMeta(
                    (string) $name,
                    (string) $value,
                );
            }
        }

        $this->addConfiguredAssets(
            $configuration['stylesheets'] ?? [],
            $this->context->getResponse()->addStylesheet(...),
        );
        $this->addConfiguredAssets(
            $configuration['javascripts'] ?? [],
            $this->context->getResponse()->addJavaScript(...),
        );
    }

    public function prepare(string $module, string $action): void
    {
        $configuration = $this->configuration->for(
            $module,
            $action,
            View::SUCCESS,
        );
        $metas = $configuration['metas'] ?? [];
        $title = is_array($metas) ? ($metas['title'] ?? null) : null;

        if (null === $title) {
            return;
        }

        $this->preparedTitle = (string) $title;
        $this->context->getResponse()->setTitle($this->preparedTitle);
    }

    public function render(
        string $path,
        array $variables,
        ?string $module = null,
    ): string {
        $module ??= $this->currentModule();
        $this->moduleStack[] = $module;

        try {
            return $this->renderer->render(
                $path,
                $variables,
                $this->context,
            );
        } finally {
            array_pop($this->moduleStack);
        }
    }

    public function decorate(string $content, array $variables): string
    {
        if (!$this->hasLayout) {
            return $content;
        }

        $path = $this->layouts->find($this->layout);

        if (null === $path) {
            throw new ModuleException(sprintf(
                'Layout "%s" was not found.',
                $this->layout,
            ));
        }

        $html = $this->render(
            $path,
            ['sf_content' => new SafeValue($content)] + $variables,
        );

        return $this->assets->inject(
            $html,
            $this->context->getResponse(),
            $this->stylesheetsIncluded,
            $this->javaScriptsIncluded,
            $this->metasIncluded,
            $this->httpMetasIncluded,
        );
    }

    public function decorateWith(false|string $layout): void
    {
        if (false === $layout) {
            $this->hasLayout = false;

            return;
        }

        $this->layout = preg_replace('/\.php$/i', '', $layout) ?? $layout;
        $this->hasLayout = true;
    }

    public function getPartial(string $name, array $variables = []): string
    {
        $variables = OutputEscaper::unescape($variables);
        [$module, $partial] = $this->splitPartialName($name);
        $format = $this->context->getRequest()->getRequestFormat();
        $path = 'global' === $module
            ? $this->layouts->findPartial($partial)
            : $this->templates->findPartial($module, $partial, $format);

        if (null === $path && $module === $this->currentModule()) {
            $path = $this->layouts->findPartial($partial);
        }

        if (null === $path) {
            throw new ModuleException(sprintf(
                'Partial "%s" was not found.',
                $name,
            ));
        }

        return $this->render($path, $variables, $module);
    }

    public function getComponent(
        string $module,
        string $component,
        array $variables = [],
    ): string {
        $variables = OutputEscaper::unescape($variables);
        $descriptor = $this->components->find($module, $component);

        if (null === $descriptor) {
            throw new ModuleException(sprintf(
                'Component "%s/%s" was not found.',
                $module,
                $component,
            ));
        }

        require_once $descriptor->path;
        $class = $this->resolveComponentClass($descriptor);
        $instance = new $class($this->context, $module, $component);

        foreach ($variables as $name => $value) {
            $instance->{(string) $name} = $value;
        }

        $result = $instance->execute($this->context->getRequest());

        if (View::NONE === $result) {
            return '';
        }

        $path = $this->templates->findPartial(
            $module,
            $component,
            $this->context->getRequest()->getRequestFormat(),
        );

        if (null === $path) {
            throw new ModuleException(sprintf(
                'Template for component "%s/%s" was not found.',
                $module,
                $component,
            ));
        }

        return $this->render(
            $path,
            $instance->getVarHolder()->getAll(),
            $module,
        );
    }

    public function hasComponentSlot(string $name): bool
    {
        return isset($this->componentSlots[$name])
            && [] !== $this->componentSlots[$name];
    }

    public function getComponentSlot(
        string $name,
        array $variables = [],
    ): string {
        if (!array_key_exists($name, $this->componentSlots)) {
            throw new BridgeException(sprintf(
                'Component slot "%s" is not configured.',
                $name,
            ));
        }

        $component = $this->componentSlots[$name];

        if (!is_array($component) || 2 > count($component)) {
            return '';
        }

        $configuredVariables = $component[2] ?? [];

        return $this->getComponent(
            (string) $component[0],
            (string) $component[1],
            array_replace(
                is_array($configuredVariables)
                    ? $configuredVariables
                    : [],
                $variables,
            ),
        );
    }

    public function startSlot(
        string $name,
        mixed $value = null,
    ): void {
        if (in_array($name, $this->slotStack, true)) {
            throw new BridgeException(sprintf(
                'Slot "%s" is already being captured.',
                $name,
            ));
        }

        if (null !== $value) {
            $this->slots[$name] = (string) $value;

            return;
        }

        $this->slotStack[] = $name;
        ob_start();
    }

    public function endSlot(): void
    {
        $name = array_pop($this->slotStack);

        if (null === $name) {
            throw new BridgeException('No slot is being captured.');
        }

        $this->slots[$name] = (string) ob_get_clean();
    }

    public function hasSlot(string $name): bool
    {
        return array_key_exists($name, $this->slots);
    }

    public function getSlot(string $name, string $default = ''): string
    {
        return $this->slots[$name] ?? $default;
    }

    public function getStylesheets(): string
    {
        $this->stylesheetsIncluded = true;

        return $this->assets->stylesheets($this->context->getResponse());
    }

    public function getJavaScripts(): string
    {
        $this->javaScriptsIncluded = true;

        return $this->assets->javaScripts($this->context->getResponse());
    }

    public function getMetas(): string
    {
        $this->metasIncluded = true;

        return $this->assets->metas($this->context->getResponse());
    }

    public function getHttpMetas(): string
    {
        $this->httpMetasIncluded = true;

        return $this->assets->httpMetas(
            $this->context->getResponse(),
        );
    }

    private function splitPartialName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$module, $partial] = explode('/', $name, 2);

            return [$module, $partial];
        }

        return [$this->currentModule(), $name];
    }

    private function currentModule(): string
    {
        return (string) end($this->moduleStack);
    }

    private function addConfiguredAssets(
        mixed $assets,
        callable $add,
    ): void {
        if (!is_array($assets)) {
            return;
        }

        foreach ($assets as $source => $options) {
            if (is_int($source)) {
                $source = $options;
                $options = [];
            }

            if (!is_string($source)) {
                continue;
            }

            $options = is_array($options) ? $options : [];
            $position = (string) ($options['position'] ?? '');
            $add($source, $position, $options);
        }
    }

    /**
     * @return class-string
     */
    private function resolveComponentClass(
        ComponentDescriptor $descriptor,
    ): string {
        $pluginClass = $descriptor->module.'_'.$descriptor->class;

        if (class_exists($pluginClass, false)) {
            return $pluginClass;
        }

        if (class_exists($descriptor->class, false)) {
            return $descriptor->class;
        }

        throw new ModuleException(sprintf(
            'Component file "%s" did not define class "%s".',
            $descriptor->path,
            $descriptor->class,
        ));
    }
}
