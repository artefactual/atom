<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

use Atom\Framework\Autoload\ClassMapBuilder;
use Atom\Framework\Autoload\LegacyClassDirectories;
use Atom\Kernel;

error_reporting(\E_ALL & ~\E_DEPRECATED & ~\E_USER_DEPRECATED);

$projectDirectory = getcwd();

require $projectDirectory.'/vendor/composer/autoload.php';

$kernel = new Kernel('test', false);
$kernel->boot();
class_exists('Propel');
$classMap = (new ClassMapBuilder())->build(
    new LegacyClassDirectories($projectDirectory),
);
$errors = [];
$loaded = 0;
$references = [];
$scannedPaths = [];

foreach ($classMap as $class => $path) {
    if (
        str_contains($path, '/vendor/')
        || str_contains($path, '/lib/propel/builder/')
        || str_contains($path, '/lib/propel/generator/')
    ) {
        continue;
    }

    if (!isset($scannedPaths[$path])) {
        foreach (symfonyClassReferences($path) as $reference) {
            $references[$reference] ??= $path;
        }

        $scannedPaths[$path] = true;
    }

    try {
        $available = class_exists($class)
            || interface_exists($class)
            || trait_exists($class)
            || (function_exists('enum_exists') && enum_exists($class));

        if (!$available) {
            $errors[$class] = 'The declaration was not available after load.';
        }
    } catch (\Throwable $error) {
        $errors[$class] = $error::class.': '.$error->getMessage();
    }

    ++$loaded;
}

foreach ($references as $reference => $path) {
    if (
        class_exists($reference)
        || interface_exists($reference)
        || trait_exists($reference)
        || (function_exists('enum_exists') && enum_exists($reference))
    ) {
        continue;
    }

    $errors[$reference] = sprintf(
        'Referenced by %s but unavailable in the production runtime.',
        str_replace($projectDirectory.'/', '', $path),
    );
}

$kernel->shutdown();

if ([] !== $errors) {
    foreach ($errors as $class => $error) {
        fwrite(\STDERR, sprintf("%s: %s\n", $class, $error));
    }

    exit(1);
}

printf("Runtime autoload verification passed (%d declarations)\n", $loaded);

function symfonyClassReferences(string $path): array
{
    $contents = file_get_contents($path);

    if (false === $contents) {
        throw new \RuntimeException(sprintf(
            'Unable to read "%s" while scanning references.',
            $path,
        ));
    }

    $references = [];

    foreach (token_get_all($contents) as $token) {
        if (
            !is_array($token)
            || !in_array($token[0], [
                \T_STRING,
                \T_NAME_QUALIFIED,
                \T_NAME_FULLY_QUALIFIED,
            ], true)
        ) {
            continue;
        }

        $reference = ltrim($token[1], '\\');

        if (1 === preg_match('/^sf[A-Z][A-Za-z0-9_]*$/', $reference)) {
            $references[$reference] = true;
        }
    }

    return array_keys($references);
}
