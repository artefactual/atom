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

use Atom\Framework\Bridge\BridgeRegistrar;
use Atom\Framework\Bridge\OutputEscaper;
use Atom\Framework\Bridge\OutputEscaperArrayDecorator;
use Atom\Framework\Bridge\OutputEscaperObjectDecorator;
use Atom\Framework\Bridge\SafeValue;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OutputEscaperTest extends TestCase
{
    protected function setUp(): void
    {
        (new BridgeRegistrar())->register();
    }

    public function testEscapesArrayValuesAndExposesRawData(): void
    {
        $escaped = OutputEscaper::escape('esc_specialchars', [
            'title' => '<Record>',
            'nested' => ['value' => '"quoted"'],
        ]);

        self::assertInstanceOf(
            OutputEscaperArrayDecorator::class,
            $escaped,
        );
        self::assertSame('&lt;Record&gt;', $escaped['title']);
        self::assertSame(
            '&quot;quoted&quot;',
            $escaped['nested']['value'],
        );
        self::assertSame(
            [
                'title' => '<Record>',
                'nested' => ['value' => '"quoted"'],
            ],
            $escaped->getRawValue(),
        );
    }

    public function testEscapesObjectPropertiesAndMethodResults(): void
    {
        $value = new class {
            public string $title = '<Record>';

            public function label(): string
            {
                return '"Label"';
            }
        };
        $escaped = OutputEscaper::escape('esc_specialchars', $value);

        self::assertInstanceOf(
            OutputEscaperObjectDecorator::class,
            $escaped,
        );
        self::assertSame('&lt;Record&gt;', $escaped->title);
        self::assertSame('&quot;Label&quot;', $escaped->label());
        self::assertSame($value::class, $escaped->getClass());
        self::assertSame($value, OutputEscaper::unescape($escaped));
    }

    public function testPreservesSafeValues(): void
    {
        self::assertSame(
            '<strong>Safe</strong>',
            OutputEscaper::escape(
                'esc_specialchars',
                new SafeValue('<strong>Safe</strong>'),
            ),
        );
    }
}
