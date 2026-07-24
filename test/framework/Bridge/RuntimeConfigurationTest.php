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

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\RuntimeConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Atom\Framework\Bridge\RuntimeConfiguration
 */
final class RuntimeConfigurationTest extends TestCase
{
    public function testDistinguishesAvailableAndEnabledPluginPaths(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $configuration = new RuntimeConfiguration(
            'qubit',
            'test',
            ['sfIsadPlugin'],
            false,
            $projectDirectory,
        );

        $available = $configuration->getAllPluginPaths();

        self::assertArrayHasKey('arCasPlugin', $available);
        self::assertArrayHasKey('sfPropelPlugin', $available);
        self::assertSame(
            [$projectDirectory.'/plugins/sfIsadPlugin'],
            $configuration->getPluginPaths(),
        );
    }
}
