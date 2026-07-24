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

final readonly class PluginRuntimeParameters
{
    private const MODULES = [
        'arCasPlugin' => ['arCasPlugin'],
        'arDacsPlugin' => ['arDacsPlugin'],
        'arOaiPlugin' => ['arOaiPlugin'],
        'arOidcPlugin' => ['arOidcPlugin'],
        'arRestApiPlugin' => ['api'],
        'arStorageServicePlugin' => [
            'arStorageServiceSettings',
            'arStorageService',
        ],
        'qtSwordPlugin' => ['qtSwordPlugin'],
        'sfDcPlugin' => ['sfDcPlugin'],
        'sfEacPlugin' => ['sfEacPlugin'],
        'sfEadPlugin' => ['sfEadPlugin'],
        'sfIsaarPlugin' => ['sfIsaarPlugin'],
        'sfIsadPlugin' => ['sfIsadPlugin'],
        'sfIsdfPlugin' => ['sfIsdfPlugin'],
        'sfIsdiahPlugin' => ['sfIsdiahPlugin'],
        'sfModsPlugin' => ['sfModsPlugin'],
        'sfPluginAdminPlugin' => ['sfPluginAdminPlugin'],
        'sfRadPlugin' => ['sfRadPlugin'],
        'sfSkosPlugin' => ['sfSkosPlugin'],
        'sfTranslatePlugin' => ['sfTranslatePlugin'],
    ];

    public function apply(
        array $parameters,
        array $plugins,
        string $projectDirectory,
    ): array {
        $modules = $parameters['sf_enabled_modules'] ?? [];

        if (!is_array($modules)) {
            $modules = [];
        }

        foreach ($plugins as $plugin) {
            array_push($modules, ...(self::MODULES[$plugin] ?? []));
        }

        $parameters['sf_enabled_modules'] = array_values(
            array_unique($modules),
        );
        $parameters['app_b5_theme'] = in_array(
            'arDominionB5Plugin',
            $plugins,
            true,
        );
        $parameters['sf_decorator_dirs'] = $parameters['app_b5_theme']
            ? [
                rtrim($projectDirectory, '/\\')
                    .'/plugins/arDominionB5Plugin/templates',
            ]
            : [];

        return $parameters;
    }
}
