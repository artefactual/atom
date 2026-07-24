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

use Atom\Framework\Configuration\ConfigurationMerger;
use Atom\Framework\Configuration\ModuleConfigurationLoader;
use Atom\Framework\Configuration\ViewConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ViewConfigurationTest extends TestCase
{
    public function testMergesApplicationAndModuleViewConventions(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $configuration = new ViewConfiguration(
            new ModuleConfigurationLoader(
                $projectDirectory,
                'qubit',
                ['sfIsadPlugin'],
            ),
            new ConfigurationMerger(),
        );

        $view = $configuration->for(
            'sfIsadPlugin',
            'index',
            'Success',
        );

        self::assertTrue($view['has_layout']);
        self::assertSame('layout', $view['layout']);
        self::assertSame(
            ['sfIsadPlugin', 'stylesheet'],
            $view['components']['css'],
        );
        self::assertArrayHasKey(
            '/vendor/imageflow/imageflow.packed.css',
            $view['stylesheets'],
        );
    }
}
