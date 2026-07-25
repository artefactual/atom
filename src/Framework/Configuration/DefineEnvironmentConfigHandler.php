<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Configuration;

final class DefineEnvironmentConfigHandler extends YamlConfigHandler
{
    public function execute($configFiles)
    {
        $prefix = strtolower((string) $this->parameterHolder->get(
            'prefix',
            '',
        ));
        $configuration = self::replaceConstants(
            self::flattenConfigurationWithEnvironment(
                self::parseYamls($configFiles),
            ),
        );
        $values = [];

        foreach ($configuration as $category => $entries) {
            $category = (string) $category;
            $categoryPrefix = str_starts_with($category, '.')
                ? $prefix
                : $prefix.strtolower($category).'_';

            if (!is_array($entries)) {
                $values[$prefix.strtolower($category)] = $entries;

                continue;
            }

            foreach ($entries as $name => $value) {
                $values[$categoryPrefix.$name] = $value;
            }
        }

        if ([] === $values) {
            return '';
        }

        return "<?php\nsfConfig::add("
            .var_export($values, true)
            .");\n";
    }
}
