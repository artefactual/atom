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

namespace Atom\Framework\Bridge;

class OutputEscaperObjectDecorator extends OutputEscaperGetterDecorator implements \Countable
{
    public function __call(string $method, array $arguments): mixed
    {
        $escapingMethod = $this->escapingMethod;
        $lastArgument = end($arguments);

        if (
            is_string($lastArgument)
            && str_starts_with($lastArgument, 'esc_')
        ) {
            $escapingMethod = (string) array_pop($arguments);
        }

        return self::escape(
            $escapingMethod,
            $this->value->{$method}(...$arguments),
        );
    }

    public function __toString(): string
    {
        return (string) self::escape(
            $this->escapingMethod,
            (string) $this->value,
        );
    }

    public function __isset(string $name): bool
    {
        return isset($this->value->{$name});
    }

    public function count(): int
    {
        return $this->value instanceof \Countable
            ? count($this->value)
            : 1;
    }

    public function getRaw(int|string $key): mixed
    {
        if (!is_callable([$this->value, 'get'])) {
            throw new BridgeException(
                'Object does not have a callable get() method.',
            );
        }

        return $this->value->get($key);
    }

    public function getClass(): string
    {
        return $this->value::class;
    }
}
