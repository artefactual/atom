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

use Atom\Framework\Cache\Cache;
use Atom\Framework\Configuration\ModuleConfigurationLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ViewCacheManager
{
    private array $componentOptions = [];

    public function __construct(
        private readonly Cache $cache,
        private readonly ModuleConfigurationLoader $configuration,
        private readonly RequestStack $requests,
    ) {}

    public function getComponent(
        string $module,
        string $component,
        array $variables,
    ): ?string {
        $request = $this->requests->getCurrentRequest();
        $options = $this->options($module, $component);
        $key = $this->key(
            $request,
            $module,
            $component,
            $variables,
            $options,
        );

        if (!$this->isCacheable($request, $options) || null === $key) {
            return null;
        }

        $content = $this->cache->get($key);

        return is_string($content) ? $content : null;
    }

    public function setComponent(
        string $module,
        string $component,
        array $variables,
        string $content,
    ): string {
        $request = $this->requests->getCurrentRequest();
        $options = $this->options($module, $component);
        $key = $this->key(
            $request,
            $module,
            $component,
            $variables,
            $options,
        );

        if ($this->isCacheable($request, $options) && null !== $key) {
            $this->cache->set(
                $key,
                $content,
                (int) $options['lifetime'],
            );
        }

        return $content;
    }

    public function remove(string $internalUri): bool
    {
        $parameters = $this->partialParameters($internalUri);
        $request = $this->requests->getCurrentRequest();

        if (null === $parameters || null === $request) {
            return false;
        }

        $cacheKey = (string) $parameters['sf_cache_key'];
        $key = $this->keyPrefix(
            $request,
            (string) $parameters['module'],
            ltrim((string) $parameters['action'], '_'),
        );

        if (str_contains($cacheKey, '*')) {
            $this->cache->removePattern($key.'**');

            return true;
        }

        $this->cache->removePattern(
            $key.'**:'.hash('sha256', $cacheKey),
        );

        return true;
    }

    private function options(string $module, string $component): array
    {
        $key = $module.'/'.$component;

        if (isset($this->componentOptions[$key])) {
            return $this->componentOptions[$key];
        }

        $configuration = $this->configuration->load(
            $module,
            'cache.yml',
        );
        $defaults = $configuration['default'] ?? [];
        $componentOptions = $configuration['_'.$component] ?? [];
        $options = array_replace(
            is_array($defaults) ? $defaults : [],
            is_array($componentOptions) ? $componentOptions : [],
        );
        $enabled = filter_var(
            $options['enabled'] ?? false,
            \FILTER_VALIDATE_BOOLEAN,
        );
        $lifetime = filter_var(
            $options['lifetime'] ?? 0,
            \FILTER_VALIDATE_INT,
        );
        $options['enabled'] = $enabled;
        $options['lifetime'] = false === $lifetime
            ? 0
            : max(0, $lifetime);
        $options['contextual'] = filter_var(
            $options['contextual'] ?? false,
            \FILTER_VALIDATE_BOOLEAN,
        );

        return $this->componentOptions[$key] = $options;
    }

    private function isCacheable(
        ?Request $request,
        array $options,
    ): bool {
        return null !== $request
            && $request->isMethod('GET')
            && true === $options['enabled']
            && 0 < $options['lifetime'];
    }

    private function key(
        ?Request $request,
        string $module,
        string $component,
        array $variables,
        array $options,
    ): ?string {
        if (null === $request) {
            return null;
        }

        if (array_key_exists('sf_cache_key', $variables)) {
            $cacheKey = (string) $variables['sf_cache_key'];
        } else {
            try {
                $cacheKey = serialize($variables);
            } catch (\Throwable) {
                return null;
            }
        }

        $context = true === $options['contextual']
            ? hash('sha256', $request->getRequestUri())
            : 'global';

        return $this->keyPrefix($request, $module, $component)
            .$context.':'.hash('sha256', $cacheKey);
    }

    private function keyPrefix(
        Request $request,
        string $module,
        string $component,
    ): string {
        return implode(':', [
            'view',
            hash('sha256', strtolower($request->getHost())),
            'component',
            $module,
            '_'.$component,
            '',
        ]);
    }

    private function partialParameters(string $internalUri): ?array
    {
        if (!str_starts_with($internalUri, '@sf_cache_partial?')) {
            return null;
        }

        parse_str(
            substr($internalUri, strlen('@sf_cache_partial?')),
            $parameters,
        );

        foreach (['module', 'action', 'sf_cache_key'] as $name) {
            if (
                !isset($parameters[$name])
                || !is_string($parameters[$name])
            ) {
                return null;
            }
        }

        if (
            1 !== preg_match('/^[a-z0-9_]+$/i', $parameters['module'])
            || 1 !== preg_match('/^_?[a-z0-9_]+$/i', $parameters['action'])
        ) {
            return null;
        }

        return $parameters;
    }
}
