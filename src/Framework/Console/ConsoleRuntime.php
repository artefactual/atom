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

namespace Atom\Framework\Console;

use Atom\Framework\Bridge\Context;
use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Database\DatabaseManager;
use Atom\Framework\Database\PropelBootstrap;
use Symfony\Component\Console\Application;

final readonly class ConsoleRuntime
{
    public function __construct(
        private RuntimeConfiguration $configuration,
        private PropelBootstrap $propel,
        private CliContextFactory $contextFactory,
        private EventDispatcher $eventDispatcher,
    ) {}

    public function createApplication(string $projectDirectory): Application
    {
        RuntimeConfiguration::setActive($this->configuration);
        DatabaseManager::setBootstrap($this->propel);
        Context::setInstance($this->contextFactory->create());

        $application = new Application('AtoM', 'Symfony 7.4');
        $formatter = new Formatter();

        foreach (
            (new TaskDiscovery($projectDirectory))->classes(
                $this->configuration->getPlugins(),
            ) as $class
        ) {
            if (!class_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            if (
                $reflection->isAbstract()
                || !$reflection->isSubclassOf(Task::class)
            ) {
                continue;
            }

            $task = $reflection->newInstance(
                $this->eventDispatcher,
                $formatter,
            );
            $application->add(new LegacyTaskCommand(
                $task,
                $this->configuration,
            ));
        }

        return $application;
    }
}
