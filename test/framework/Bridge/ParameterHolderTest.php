<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 */

declare(strict_types=1);

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\ParameterHolder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ParameterHolderTest extends TestCase
{
    public function testReturnsUnmodifiedTemplateValues(): void
    {
        $value = new \stdClass();
        $holder = new ParameterHolder(['value' => $value]);

        self::assertSame($value, $holder->getRaw('value'));
        self::assertSame('fallback', $holder->getRaw(
            'missing',
            'fallback',
        ));
    }
}
