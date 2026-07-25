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

use Atom\Framework\Bridge\Context;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Console\ConsoleRuntime;
use Atom\Framework\Database\DatabaseManager;
use Atom\Kernel;

$projectDirectory = dirname(__DIR__, 2);

require $projectDirectory.'/vendor/composer/autoload.php';

$kernel = new Kernel('test', true);
$kernel->boot();
$kernel
    ->getContainer()
    ->get(ConsoleRuntime::class)
    ->createApplication($projectDirectory);
new DatabaseManager();

register_shutdown_function(static function () use ($kernel): void {
    Context::setInstance(null);
    RuntimeConfiguration::setActive(null);
    DatabaseManager::setBootstrap(null);
    $kernel->shutdown();
});
