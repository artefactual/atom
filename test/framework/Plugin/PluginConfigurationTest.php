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

namespace Atom\Tests\Framework\Plugin;

use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Plugin\PluginConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PluginConfigurationTest extends TestCase
{
    public function testExposesThePluginIdentity(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $configuration = new RuntimeConfiguration(
            'qubit',
            'test',
            [],
            false,
            $projectDirectory,
        );
        $plugin = new PluginConfiguration(
            $configuration,
            $projectDirectory.'/plugins/arCasPlugin',
            'arCasPlugin',
        );

        self::assertSame('arCasPlugin', $plugin->getName());
        self::assertSame(
            $projectDirectory.'/plugins/arCasPlugin',
            $plugin->getRootDir(),
        );
    }
}
