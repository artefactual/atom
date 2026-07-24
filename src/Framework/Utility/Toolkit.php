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

use Atom\Framework\Configuration\ConfigurationMerger;

final class Toolkit
{
    public static function arrayDeepMerge(array ...$arrays): array
    {
        return (new ConfigurationMerger())->merge(...$arrays);
    }

    public static function pregtr(
        mixed $value,
        array $patterns,
    ): string {
        return preg_replace(
            array_keys($patterns),
            array_values($patterns),
            (string) $value,
        ) ?? (string) $value;
    }

    public static function replaceConstants(mixed $value): mixed
    {
        return (new \Atom\Framework\Configuration\ConstantReplacer())
            ->replace(
                $value,
                \Atom\Framework\Bridge\Configuration::getAll(),
            );
    }

    public static function isPathAbsolute(mixed $path): bool
    {
        $path = (string) $path;

        return str_starts_with($path, '/')
            || 1 === preg_match('/^[a-z]:[\\\\\/]/i', $path);
    }
}
