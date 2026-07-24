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

namespace Atom\Framework\Bridge;

final readonly class NumberFormatter
{
    public function __construct(private string $culture = 'en') {}

    public function format(
        float|int $number,
        ?string $pattern = null,
        ?string $currency = null,
    ): string {
        $style = 'c' === $pattern
            ? \NumberFormatter::CURRENCY
            : \NumberFormatter::DECIMAL;
        $formatter = new \NumberFormatter(
            str_replace('@valencia', '', $this->culture),
            $style,
        );
        $value = \NumberFormatter::CURRENCY === $style
            ? $formatter->formatCurrency($number, $currency ?? 'USD')
            : $formatter->format($number);

        if (false === $value) {
            throw new BridgeException('The number could not be formatted.');
        }

        return $value;
    }
}
