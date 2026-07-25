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

use Atom\Framework\Plugin\PluginRuntimeParameters;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PluginRuntimeParametersTest extends TestCase
{
    public function testTranslatesPluginInitializationSideEffects(): void
    {
        $parameters = (new PluginRuntimeParameters())->apply(
            ['sf_enabled_modules' => ['default']],
            [
                'arDominionB5Plugin',
                'sfIsadPlugin',
                'arRestApiPlugin',
            ],
            '/srv/atom',
        );

        self::assertSame(
            ['default', 'sfIsadPlugin', 'api'],
            $parameters['sf_enabled_modules'],
        );
        self::assertTrue($parameters['app_b5_theme']);
        self::assertSame(
            ['/srv/atom/plugins/arDominionB5Plugin/templates'],
            $parameters['sf_decorator_dirs'],
        );
    }
}
