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
 */
final class RuntimeConfigurationTest extends TestCase
{
    protected function tearDown(): void
    {
        RuntimeConfiguration::setActive(null);
    }

    public function testReportsWhetherAConfigurationIsActive(): void
    {
        RuntimeConfiguration::setActive(null);
        self::assertFalse(RuntimeConfiguration::hasActive());
        RuntimeConfiguration::setActive(new RuntimeConfiguration());
        self::assertTrue(RuntimeConfiguration::hasActive());
    }

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
        self::assertArrayNotHasKey('sfPropelPlugin', $available);
        self::assertSame(
            [$projectDirectory.'/plugins/sfIsadPlugin'],
            $configuration->getPluginPaths(),
        );
    }

    public function testLoadsLegacyModuleParameters(): void
    {
        $configuration = new RuntimeConfiguration(
            'qubit',
            'test',
            ['arOaiPlugin'],
            false,
            dirname(__DIR__, 3),
        );

        self::assertSame(
            ['verb'],
            $configuration->loadParameters(
                'modules/arOaiPlugin/config/module.yml',
                'mod_aroaiplugin_',
            )['mod_aroaiplugin_IdentifyAllowed'],
        );
        self::assertSame(
            ['identifier', 'metadataPrefix'],
            $configuration->loadParameters(
                'modules/arOaiPlugin/config/module.yml',
                'mod_aroaiplugin_',
            )['mod_aroaiplugin_GetRecordMandatory'],
        );
    }
}
