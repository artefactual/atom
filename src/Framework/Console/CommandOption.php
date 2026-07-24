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

final readonly class CommandOption
{
    public const PARAMETER_NONE = 1;
    public const PARAMETER_REQUIRED = 2;
    public const PARAMETER_OPTIONAL = 4;
    public const IS_ARRAY = 8;

    private string $name;
    private ?string $shortcut;

    public function __construct(
        string $name,
        ?string $shortcut = null,
        private ?int $mode = null,
        private string $help = '',
        private mixed $default = null,
    ) {
        $this->name = str_starts_with($name, '--')
            ? substr($name, 2)
            : $name;
        $shortcut = null === $shortcut ? null : ltrim($shortcut, '-');
        $this->shortcut = '' === $shortcut ? null : $shortcut;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    public function acceptParameter(): bool
    {
        return $this->isParameterRequired()
            || $this->isParameterOptional();
    }

    public function isParameterRequired(): bool
    {
        return self::PARAMETER_REQUIRED === (
            self::PARAMETER_REQUIRED
            & ($this->mode ?? self::PARAMETER_NONE)
        );
    }

    public function isParameterOptional(): bool
    {
        return self::PARAMETER_OPTIONAL === (
            self::PARAMETER_OPTIONAL
            & ($this->mode ?? self::PARAMETER_NONE)
        );
    }

    public function isArray(): bool
    {
        return self::IS_ARRAY === (
            self::IS_ARRAY & ($this->mode ?? self::PARAMETER_NONE)
        );
    }

    public function getDefault(): mixed
    {
        if (!$this->acceptParameter()) {
            return false;
        }

        return $this->default ?? ($this->isArray() ? [] : null);
    }

    public function getHelp(): string
    {
        return $this->help;
    }
}
