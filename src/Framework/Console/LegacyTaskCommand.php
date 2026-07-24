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

use Atom\Framework\Bridge\RuntimeConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class LegacyTaskCommand extends Command
{
    private const GLOBAL_OPTIONS = [
        'help',
        'silent',
        'quiet',
        'verbose',
        'version',
        'ansi',
        'no-ansi',
        'no-interaction',
    ];

    public function __construct(
        private readonly Task $task,
        private readonly RuntimeConfiguration $configuration,
    ) {
        parent::__construct($task->getFullName());
    }

    protected function configure(): void
    {
        $this->setDescription($this->task->getBriefDescription());
        $this->setHelp($this->task->getDetailedDescription());
        $this->setAliases($this->task->getAliases());

        foreach ($this->task->getArguments() as $argument) {
            $mode = $argument->isRequired()
                ? InputArgument::REQUIRED
                : InputArgument::OPTIONAL;

            if ($argument->isArray()) {
                $mode |= InputArgument::IS_ARRAY;
            }

            $this->addArgument(
                $argument->getName(),
                $mode,
                $argument->getHelp(),
                $argument->isRequired()
                    ? null
                    : $argument->getDefault(),
            );
        }

        foreach ($this->task->getOptions() as $option) {
            if (in_array($option->getName(), self::GLOBAL_OPTIONS, true)) {
                continue;
            }

            $mode = InputOption::VALUE_NONE;

            if ($option->isParameterRequired()) {
                $mode = InputOption::VALUE_REQUIRED;
            } elseif ($option->isParameterOptional()) {
                $mode = InputOption::VALUE_OPTIONAL;
            }

            if ($option->isArray()) {
                $mode |= InputOption::VALUE_IS_ARRAY;
            }

            $this->addOption(
                $option->getName(),
                $option->getShortcut(),
                $mode,
                $option->getHelp(),
                InputOption::VALUE_NONE === $mode
                    ? null
                    : $option->getDefault(),
            );
        }
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $arguments = [];

        foreach ($this->task->getArguments() as $argument) {
            $arguments[$argument->getName()] = $input->getArgument(
                $argument->getName(),
            );
        }

        $options = [];

        foreach ($this->task->getOptions() as $option) {
            $name = $option->getName();
            $options[$name] = $input->hasOption($name)
                ? $input->getOption($name)
                : $option->getDefault();
        }

        if (true === ($options['application'] ?? null)) {
            $options['application'] = $this->configuration->getApplication();
        }

        $this->task->setConfiguration($this->configuration);
        $this->task->setIo($input, $output);

        return $this->task->invoke($arguments, $options);
    }
}
