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

final readonly class CommandArgument
{
    public const REQUIRED = 1;
    public const OPTIONAL = 2;
    public const IS_ARRAY = 4;

    public function __construct(
        private string $name,
        private ?int $mode = null,
        private string $help = '',
        private mixed $default = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function isRequired(): bool
    {
        return self::REQUIRED === (
            self::REQUIRED & ($this->mode ?? self::OPTIONAL)
        );
    }

    public function isArray(): bool
    {
        return self::IS_ARRAY === (
            self::IS_ARRAY & ($this->mode ?? self::OPTIONAL)
        );
    }

    public function getDefault(): mixed
    {
        return $this->default ?? ($this->isArray() ? [] : null);
    }

    public function getHelp(): string
    {
        return $this->help;
    }
}
