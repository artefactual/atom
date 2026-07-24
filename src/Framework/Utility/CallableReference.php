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

namespace Atom\Framework\Utility;

final readonly class CallableReference
{
    public function __construct(private mixed $callable) {}

    public function getCallable(): mixed
    {
        return $this->callable;
    }

    public function call(mixed ...$arguments): mixed
    {
        if (!is_callable($this->callable)) {
            throw new \InvalidArgumentException(
                'The configured value is not callable.',
            );
        }

        return ($this->callable)(...$arguments);
    }
}
