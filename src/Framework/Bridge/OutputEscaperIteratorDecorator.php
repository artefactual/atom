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

final class OutputEscaperIteratorDecorator extends OutputEscaperObjectDecorator implements \ArrayAccess, \Iterator
{
    private \IteratorIterator $iterator;

    public function __construct(
        string $escapingMethod,
        \Traversable $value,
    ) {
        parent::__construct($escapingMethod, $value);
        $this->iterator = new \IteratorIterator($value);
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    public function current(): mixed
    {
        return self::escape(
            $this->escapingMethod,
            $this->iterator->current(),
        );
    }

    public function key(): mixed
    {
        return $this->iterator->key();
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
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
}
