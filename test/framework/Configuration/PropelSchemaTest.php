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

namespace Atom\Tests\Framework\Configuration;

use Atom\Framework\Utility\Yaml;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PropelSchemaTest extends TestCase
{
    public function testAllPropelSchemasUseValidYaml(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $schemas = [$projectDirectory.'/config/schema.yml'];

        foreach (
            glob($projectDirectory.'/plugins/*/config/*schema.yml') ?: [] as $schema
        ) {
            $schemas[] = $schema;
        }

        foreach ($schemas as $schema) {
            self::assertIsArray(
                Yaml::load($schema),
                sprintf('Failed to parse %s.', $schema),
            );
        }
    }
}
