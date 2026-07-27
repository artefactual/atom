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

use Atom\Framework\Autoload\LegacyClassDirectories;
use Atom\Framework\Autoload\LegacyClassLoader;
use Atom\Framework\Cache\PhpArrayFileCache;

$projectDirectory = dirname(__DIR__);

require $projectDirectory.'/vendor/composer/autoload.php';

$application = 'qubit';
$environment = $argv[1] ?? 'prod';

if (1 !== preg_match('/^[a-z0-9_.-]+$/i', $environment)) {
    throw new InvalidArgumentException(
        'The cache environment must be a safe name.',
    );
}

$directories = new LegacyClassDirectories($projectDirectory);
$loader = new LegacyClassLoader(
    $directories,
    includePaths: $directories->includePaths(),
    classMapCache: new PhpArrayFileCache(
        $projectDirectory.'/cache/'.$application.'/'.$environment
            .'/config/bridge/class-map',
    ),
);
$loader->warmUp();
