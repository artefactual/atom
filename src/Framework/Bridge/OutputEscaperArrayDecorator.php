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

final class OutputEscaperArrayDecorator extends OutputEscaperGetterDecorator implements \ArrayAccess, \Countable, \Iterator
{
    private int $remaining = 0;

    public function rewind(): void
    {
        reset($this->value);
        $this->remaining = count($this->value);
    }

    public function current(): mixed
    {
        return self::escape(
            $this->escapingMethod,
            current($this->value),
        );
    }

    public function key(): int|string|null
    {
        return key($this->value);
    }

    public function next(): void
    {
        next($this->value);
        --$this->remaining;
    }

    public function valid(): bool
    {
        return 0 < $this->remaining;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->value[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return self::escape(
            $this->escapingMethod,
            $this->value[$offset],
        );
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BridgeException('Cannot set escaped values.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BridgeException('Cannot unset escaped values.');
    }

    public function count(): int
    {
        return count($this->value);
    }

    public function getRaw(int|string $key): mixed
    {
        return $this->value[$key];
    }
}
