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

final readonly class DateFormatter
{
    public function __construct(private string $culture = 'en') {}

    public function format(
        mixed $date,
        array|string|null $format = 'F',
        mixed $inputFormat = null,
        string $charset = 'UTF-8',
    ): ?string {
        if (null === $date) {
            return null;
        }

        $timestamp = $this->timestamp($date);
        [$dateStyle, $timeStyle, $pattern] = $this->styles($format);
        $formatter = new \IntlDateFormatter(
            str_replace('@valencia', '', $this->culture),
            $dateStyle,
            $timeStyle,
            date_default_timezone_get(),
        );

        if (null !== $pattern) {
            $formatter->setPattern($pattern);
        }

        $value = $formatter->format($timestamp);

        if (false === $value) {
            throw new BridgeException(sprintf(
                'Date "%s" could not be formatted.',
                (string) $date,
            ));
        }

        return $value;
    }

    private function timestamp(mixed $date): float|int
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->getTimestamp();
        }

        if (is_int($date) || is_float($date)) {
            return $date;
        }

        $timestamp = strtotime((string) $date);

        if (false === $timestamp) {
            throw new BridgeException(sprintf(
                'Date "%s" could not be parsed.',
                (string) $date,
            ));
        }

        return $timestamp;
    }

    private function styles(array|string|null $format): array
    {
        if (is_array($format)) {
            $format = implode(' ', $format);
        }

        return match ($format ?? 'F') {
            'd' => [\IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, null],
            'D' => [\IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null],
            'p' => [\IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE, null],
            'P' => [\IntlDateFormatter::FULL, \IntlDateFormatter::NONE, null],
            't' => [\IntlDateFormatter::NONE, \IntlDateFormatter::SHORT, null],
            'T' => [\IntlDateFormatter::NONE, \IntlDateFormatter::LONG, null],
            'q' => [\IntlDateFormatter::NONE, \IntlDateFormatter::MEDIUM, null],
            'Q' => [\IntlDateFormatter::NONE, \IntlDateFormatter::FULL, null],
            'f' => [\IntlDateFormatter::LONG, \IntlDateFormatter::SHORT, null],
            'F' => [\IntlDateFormatter::LONG, \IntlDateFormatter::LONG, null],
            'g' => [\IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, null],
            'G' => [\IntlDateFormatter::SHORT, \IntlDateFormatter::LONG, null],
            'i' => [\IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'yyyy-MM-dd'],
            'I' => [\IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'yyyy-MM-dd HH:mm:ss'],
            'M', 'm' => [\IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'MMMM dd'],
            'R', 'r' => [\IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'EEE, dd MMM yyyy HH:mm:ss'],
            's' => [\IntlDateFormatter::NONE, \IntlDateFormatter::NONE, "yyyy-MM-dd'T'HH:mm:ss"],
            'u' => [\IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'yyyy-MM-dd HH:mm:ss z'],
            'U' => [\IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'EEEE dd MMMM yyyy HH:mm:ss'],
            'Y', 'y' => [\IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'yyyy MMMM'],
            default => [\IntlDateFormatter::NONE, \IntlDateFormatter::NONE, (string) $format],
        };
    }
}
