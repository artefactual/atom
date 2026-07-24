<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Framework\Bridge;

final class Event implements \ArrayAccess
{
    private bool $processed = false;
    private mixed $returnValue = null;

    public function __construct(
        private readonly object $subject,
        private readonly string $name,
        private array $parameters = [],
    ) {}

    public function getSubject(): object
    {
        return $this->subject;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): void
    {
        $this->processed = $processed;
    }

    public function getReturnValue(): mixed
    {
        return $this->returnValue;
    }

    public function setReturnValue(mixed $value): void
    {
        $this->returnValue = $value;
        $this->processed = true;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->parameters);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->parameters[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->parameters[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->parameters[$offset]);
    }
}
