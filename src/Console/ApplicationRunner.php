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

namespace Atom\Console;

use Atom\Framework\Bridge\Context;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Console\ConsoleRuntime;
use Atom\Framework\Database\DatabaseManager;
use Atom\Kernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ApplicationRunner
{
    public function run(
        string $projectDirectory,
        ?InputInterface $input = null,
        ?OutputInterface $output = null,
    ): int {
        $kernel = new Kernel('cli', false);

        try {
            $kernel->boot();
            $runtime = $kernel->getContainer()->get(ConsoleRuntime::class);

            return $runtime
                ->createApplication($projectDirectory)
                ->run($input, $output);
        } finally {
            Context::setInstance(null);
            RuntimeConfiguration::setActive(null);
            DatabaseManager::setBootstrap(null);
            $kernel->shutdown();
        }
    }
}
