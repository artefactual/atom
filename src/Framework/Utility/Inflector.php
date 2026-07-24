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

namespace Atom\Framework\Utility;

final class Inflector
{
    public static function camelize(mixed $value): string
    {
        return strtr(ucwords(strtr((string) $value, [
            '/' => '::',
            '_' => ' ',
            '-' => ' ',
            '.' => '_ ',
        ])), [' ' => '']);
    }

    public static function underscore(mixed $value): string
    {
        $value = str_replace('::', '/', (string) $value);
        $value = preg_replace(
            [
                '/([A-Z]+)([A-Z][a-z])/',
                '/([a-z\d])([A-Z])/',
            ],
            ['$1_$2', '$1_$2'],
            $value,
        );

        return strtolower((string) $value);
    }

    public static function demodulize(mixed $value): string
    {
        return preg_replace('/^.*::/', '', (string) $value)
            ?? (string) $value;
    }

    public static function foreign_key(
        mixed $value,
        bool $separateWithUnderscore = true,
    ): string {
        return self::underscore(self::demodulize($value))
            .($separateWithUnderscore ? '_id' : 'id');
    }

    public static function tableize(mixed $value): string
    {
        return self::underscore($value);
    }

    public static function classify(mixed $value): string
    {
        return self::camelize($value);
    }

    public static function humanize(mixed $value): string
    {
        $value = (string) $value;

        if (str_ends_with($value, '_id')) {
            $value = substr($value, 0, -3);
        }

        return ucfirst(str_replace('_', ' ', $value));
    }
}
