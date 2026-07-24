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

use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml
{
    public static function load(mixed $input): mixed
    {
        $input = (string) $input;

        return is_file($input)
            ? SymfonyYaml::parseFile($input)
            : SymfonyYaml::parse($input);
    }

    public static function dump(
        mixed $value,
        int $inline = 2,
        int $indent = 4,
    ): string {
        return SymfonyYaml::dump($value, $inline, $indent);
    }
}
