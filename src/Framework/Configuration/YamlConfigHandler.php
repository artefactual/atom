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

use Atom\Framework\Bridge\Configuration;
use Symfony\Component\Yaml\Yaml;

abstract class YamlConfigHandler extends ConfigHandler
{
    protected $yamlConfig;

    public static function parseYamls($configFiles): array
    {
        $configurations = array_map(
            self::parseYaml(...),
            (array) $configFiles,
        );

        return (new ConfigurationMerger())
            ->mergeConfigurations($configurations);
    }

    public static function parseYaml($configFile): array
    {
        if (!is_readable($configFile)) {
            throw new ConfigurationException(sprintf(
                'Configuration file "%s" is not readable.',
                $configFile,
            ));
        }

        return Yaml::parseFile($configFile) ?? [];
    }

    public static function flattenConfiguration($config): array
    {
        $config['all'] = (new ConfigurationMerger())->merge(
            (array) ($config['default'] ?? []),
            (array) ($config['all'] ?? []),
        );
        unset($config['default']);

        return $config;
    }

    public static function flattenConfigurationWithEnvironment(
        $config,
    ): array {
        return (new ConfigurationMerger())->forEnvironment(
            (array) $config,
            (string) Configuration::get('sf_environment', 'prod'),
        );
    }

    protected function mergeConfigValue($keyName, $category): array
    {
        return array_merge(
            (array) ($this->yamlConfig['all'][$keyName] ?? []),
            (array) ($this->yamlConfig[$category][$keyName] ?? []),
        );
    }

    protected function getConfigValue(
        $keyName,
        $category,
        $defaultValue = null,
    ): mixed {
        return $this->yamlConfig[$category][$keyName]
            ?? $this->yamlConfig['all'][$keyName]
            ?? $defaultValue;
    }
}
