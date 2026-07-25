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

namespace Atom\Framework\Database;

use Atom\Framework\Utility\Inflector;

final readonly class PropelSchemaConverter
{
    private const TABLE_SECTIONS = [
        '_behaviors' => 'behaviors',
        '_propel_behaviors' => 'propel_behaviors',
        '_inheritance' => 'inheritance',
        '_nestedSet' => 'nestedSet',
        '_foreignKeys' => 'foreignKeys',
        '_indexes' => 'indexes',
        '_uniques' => 'uniques',
    ];

    public function oldToNew(array $schema): array
    {
        if ([] === $schema) {
            return [];
        }

        $connection = array_key_first($schema);

        if (
            null === $connection
            || !is_array($schema[$connection] ?? null)
        ) {
            return [];
        }

        $converted = ['connection' => $connection];
        $classes = [];

        foreach ($schema[$connection] as $table => $parameters) {
            if (!is_array($parameters)) {
                continue;
            }

            if ('_attributes' === $table) {
                $converted = array_merge($converted, $parameters);

                continue;
            }

            if ('_propel_behaviors' === $table) {
                $converted['propel_behaviors'] = $parameters;

                continue;
            }

            $attributes = $parameters['_attributes'] ?? [];
            unset($parameters['_attributes']);
            $class = (string) ($attributes['phpName'] ?? Inflector::camelize(
                (string) $table,
            ));
            unset($attributes['phpName']);

            $definition = $attributes;
            $definition['tableName'] = $table;
            $definition['columns'] = [];

            foreach ($parameters as $name => $value) {
                if (isset(self::TABLE_SECTIONS[$name])) {
                    $definition[self::TABLE_SECTIONS[$name]] = $value;
                } else {
                    $definition['columns'][$name] = $value;
                }
            }

            $classes[$class] = $definition;
        }

        $converted['classes'] = $classes;

        return $converted;
    }
}
