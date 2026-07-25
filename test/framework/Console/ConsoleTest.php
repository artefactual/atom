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

namespace AccessToMemory\test\framework\Console;

use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Console\CommandArgument;
use Atom\Framework\Console\CommandOption;
use Atom\Framework\Console\Formatter;
use Atom\Framework\Console\LegacyTaskCommand;
use Atom\Framework\Console\Task;
use Atom\Framework\Console\TaskDiscovery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
final class ConsoleTest extends TestCase
{
    public function testLegacyTaskDefinitionRunsThroughSymfonyConsole(): void
    {
        $task = new class(new EventDispatcher(), new Formatter()) extends Task {
            public array $received = [];

            protected function configure()
            {
                $this->namespace = 'test';
                $this->name = 'echo';
                $this->briefDescription = 'Echo a configured value';
                $this->addArguments([
                    new CommandArgument(
                        'message',
                        CommandArgument::REQUIRED,
                        'Message to echo',
                    ),
                ]);
                $this->addOptions([
                    new CommandOption(
                        'upper',
                        'u',
                        CommandOption::PARAMETER_NONE,
                        'Use uppercase',
                    ),
                ]);
            }

            protected function execute($arguments = [], $options = [])
            {
                $this->received = [$arguments, $options];
                $message = $options['upper']
                    ? strtoupper($arguments['message'])
                    : $arguments['message'];
                $this->logSection('test', $message);
            }
        };
        $configuration = new RuntimeConfiguration(
            'qubit',
            'cli',
            [],
            false,
            dirname(__DIR__, 3),
        );
        $application = new Application();
        $application->add(new LegacyTaskCommand($task, $configuration));
        $tester = new CommandTester($application->find('test:echo'));

        self::assertSame(0, $tester->execute([
            'message' => 'hello',
            '--upper' => true,
        ]));
        self::assertStringContainsString('HELLO', $tester->getDisplay());
        self::assertSame('hello', $task->received[0]['message']);
        self::assertTrue($task->received[1]['upper']);
        self::assertFalse($task->received[1]['quiet']);
    }

    public function testTaskDiscoveryFindsRenamedTaskClass(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $classes = (new TaskDiscovery($projectDirectory))->classes([]);

        self::assertContains('csvEventRecordImportTask', $classes);
        self::assertContains('QubitUpgradeSqlTask', $classes);
        self::assertContains('arBaseTask', $classes);
    }
}
