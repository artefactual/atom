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

use Atom\Framework\Bridge\CultureInfo;
use Atom\Framework\Bridge\DateFormatter;
use Atom\Framework\Bridge\NumberFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class InternationalizationTest extends TestCase
{
    public function testProvidesCultureNamesAndDirectionFromSymfonyIntl(): void
    {
        $culture = CultureInfo::getInstance('fr');

        self::assertSame('français', $culture->getLanguage('fr'));
        self::assertSame('Canada', $culture->getCountry('CA'));
        self::assertSame('ltr', $culture->direction);
        self::assertSame(
            'rtl',
            CultureInfo::getInstance('ar')->direction,
        );
        self::assertTrue(CultureInfo::validCulture('pt_BR'));
        self::assertTrue(CultureInfo::validCulture('ca@valencia'));
    }

    public function testFormatsLegacyDateAndNumberPatternsWithIntl(): void
    {
        $timezone = date_default_timezone_get();
        date_default_timezone_set('UTC');

        try {
            $date = new DateFormatter('en');

            self::assertSame(
                '2026-07-24',
                $date->format('2026-07-24 09:30:00', 'i'),
            );
            self::assertSame(
                '2026-07-24T09:30:00',
                $date->format('2026-07-24 09:30:00', 's'),
            );
            self::assertSame(
                '1,234.5',
                (new NumberFormatter('en'))->format(1234.5),
            );
        } finally {
            date_default_timezone_set($timezone);
        }
    }
}
