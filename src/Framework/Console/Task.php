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

use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Form\Validator;
use Atom\Framework\Form\ValidatorError;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class Task
{
    protected $namespace = '';
    protected $name;
    protected $aliases = [];
    protected $briefDescription = '';
    protected $detailedDescription = '';
    protected $arguments = [];
    protected $options = [];
    protected $dispatcher;
    protected $formatter;
    protected $configuration;
    protected $commandApplication;
    private ?InputInterface $input = null;

    public function __construct(
        EventDispatcher $dispatcher,
        Formatter $formatter,
    ) {
        $this->initialize($dispatcher, $formatter);
        $this->configure();
    }

    public function initialize(
        EventDispatcher $dispatcher,
        Formatter $formatter,
    ): void {
        $this->dispatcher = $dispatcher;
        $this->formatter = $formatter;
    }

    public function getFormatter(): Formatter
    {
        return $this->formatter;
    }

    public function setFormatter(Formatter $formatter): void
    {
        $this->formatter = $formatter;
    }

    public function setIo(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $this->input = $input;
        $this->formatter->setOutput($output);
    }

    public function setConfiguration(
        ?RuntimeConfiguration $configuration = null,
    ): void {
        $this->configuration = $configuration;
    }

    public function setCommandApplication(mixed $application = null): void
    {
        $this->commandApplication = $application;
    }

    public function run(
        array|string $arguments = [],
        array $options = [],
    ): int {
        if (!is_array($arguments)) {
            throw new \InvalidArgumentException(
                'Nested AtoM tasks require an argument array.',
            );
        }

        return $this->invoke($arguments, $options);
    }

    public function invoke(array $arguments, array $options): int
    {
        $result = $this->execute($arguments, $options);

        return is_int($result) ? $result : 0;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function addArguments(array $arguments): void
    {
        $this->arguments = array_merge($this->arguments, $arguments);
    }

    public function addArgument(
        string $name,
        ?int $mode = null,
        string $help = '',
        mixed $default = null,
    ): void {
        $this->arguments[] = new CommandArgument(
            $name,
            $mode,
            $help,
            $default,
        );
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function addOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    public function addOption(
        string $name,
        ?string $shortcut = null,
        ?int $mode = null,
        string $help = '',
        mixed $default = null,
    ): void {
        $this->options[] = new CommandOption(
            $name,
            $shortcut,
            $mode,
            $help,
            $default,
        );
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getName(): string
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        $name = preg_replace('/Task$/', '', static::class);
        $name = preg_replace('/^sf/', '', (string) $name);
        $name = preg_replace('/(?<!^)[A-Z]/', '-$0', (string) $name);

        return strtolower(str_replace('_', '-', (string) $name));
    }

    final public function getFullName(): string
    {
        return '' === $this->namespace
            ? $this->getName()
            : $this->namespace.':'.$this->getName();
    }

    public function getBriefDescription(): string
    {
        return $this->briefDescription;
    }

    public function getDetailedDescription(): string
    {
        return preg_replace(
            '/\[(.+?)\|(\w+)\]/s',
            '$1',
            $this->detailedDescription,
        ) ?? $this->detailedDescription;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function log($messages)
    {
        foreach ((array) $messages as $message) {
            $this->formatter->getOutput()->writeln((string) $message);
        }
    }

    public function logSection(
        string $section,
        string $message,
        ?int $size = null,
        string $style = 'INFO',
    ): void {
        $this->log($this->formatter->formatSection(
            $section,
            $message,
            $size,
            $style,
        ));
    }

    public function logBlock(
        array|string $messages,
        ?string $style = null,
    ): void {
        foreach ((array) $messages as $message) {
            $this->log(' '.(string) $message.' ');
        }
    }

    public function ask(
        array|string $question,
        false|string|null $style = 'QUESTION',
        mixed $default = null,
    ): mixed {
        $input = $this->input ?? new ArrayInput([]);
        $prompt = implode("\n", (array) $question);

        return (new QuestionHelper())->ask(
            $input,
            $this->formatter->getOutput(),
            new Question($prompt.' ', $default),
        );
    }

    public function askConfirmation(
        array|string $question,
        false|string|null $style = 'QUESTION',
        bool $default = true,
    ): bool {
        $input = $this->input ?? new ArrayInput([]);
        $prompt = implode("\n", (array) $question);

        return (bool) (new QuestionHelper())->ask(
            $input,
            $this->formatter->getOutput(),
            new ConfirmationQuestion($prompt.' ', $default),
        );
    }

    public function askAndValidate(
        array|string $question,
        Validator $validator,
        array $options = [],
    ): mixed {
        if (!empty($options['value'])) {
            try {
                return $validator->clean($options['value']);
            } catch (ValidatorError) {
            }
        }

        $attempts = $options['attempts'] ?? null;
        $prompt = implode("\n", (array) $question);
        $input = $this->input ?? new ArrayInput([]);
        $consoleQuestion = new Question($prompt.' ');
        $consoleQuestion->setMaxAttempts(false === $attempts
            ? null
            : $attempts);
        $consoleQuestion->setValidator(
            static fn (mixed $value): mixed => $validator->clean($value),
        );

        return (new QuestionHelper())->ask(
            $input,
            $this->formatter->getOutput(),
            $consoleQuestion,
        );
    }

    protected function configure() {}

    abstract protected function execute(
        $arguments = [],
        $options = [],
    );
}
