<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

use PHPUnit\Framework\TestCase;

/**
 * @covers \Qubit
 *
 * @internal
 */
final class QubitDateTest extends TestCase
{
    /**
     * @dataProvider parseDateProvider
     */
    public function testParsesDates(string $input, ?string $expected): void
    {
        self::assertSame($expected, Qubit::parseDate($input));
    }

    public function parseDateProvider(): array
    {
        return [
            'year' => ['2018', '2018'],
            'short year' => ['769', '769'],
            'year and month' => ['2018-05', '2018-05'],
            'slash-separated year and month' => ['2018/05', '2018-05'],
            'date with time' => ['2018-05-21 12:05:45', '2018-05-21'],
            'unparseable text' => ['Not parseable string', null],
            'overflowing month' => ['2018-13', '2018-01-01'],
            'compact date' => ['20181127', '2018-11-27'],
            'slash-separated date' => ['2018/12/31', '2018-12-31'],
            'natural-language date' => ['January 7, 1975', '1975-01-07'],
        ];
    }

    /**
     * @dataProvider renderDateProvider
     */
    public function testRendersDates(string $input, string $expected): void
    {
        self::assertSame($expected, Qubit::renderDate($input));
    }

    public function renderDateProvider(): array
    {
        return [
            ['1992-00-00', '1992'],
            ['1992-12-00', '1992-12'],
            ['1992-08-00', '1992-08'],
            ['1992-8-00', '1992-8'],
            ['1992-8-0', '1992-8'],
            ['1992-01-02', '1992-01-02'],
            ['1992-01-01', '1992-01-01'],
            ['1992-6-9', '1992-6-9'],
            ['1992-06-9', '1992-06-9'],
            ['1992-6-09', '1992-6-09'],
            ['1992-08-12', '1992-08-12'],
            ['1992-6-16', '1992-6-16'],
            ['1992-06-16', '1992-06-16'],
            ['1992-12-12', '1992-12-12'],
            ['1992-12-6', '1992-12-6'],
            ['1992-12-06', '1992-12-06'],
            ['1992-12-16', '1992-12-16'],
        ];
    }

    public function testRemovesTheRequestPathPrefix(): void
    {
        $request = new class {
            public function getPathInfoPrefix(): string
            {
                return '/aaa/bbb';
            }
        };

        self::assertSame(
            '/ccc/ddd',
            Qubit::pathInfo('/aaa/bbb/ccc/ddd', $request),
        );
    }
}
