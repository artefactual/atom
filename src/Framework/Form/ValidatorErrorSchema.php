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

namespace Atom\Framework\Form;

final class ValidatorErrorSchema extends ValidatorError implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private array $globalErrors = [];
    private array $namedErrors = [];

    public function __construct(Validator $validator, iterable $errors = [])
    {
        parent::__construct($validator, '');
        $this->addErrors($errors);
    }

    public function addError(
        ValidatorError $error,
        int|string|null $name = null,
    ): static {
        if (null === $name || is_int($name)) {
            if ($error instanceof self) {
                return $this->addErrors($error);
            }

            $this->globalErrors[] = $error;

            return $this;
        }

        if (!isset($this->namedErrors[$name])) {
            $this->namedErrors[$name] = $error;

            return $this;
        }

        $existing = $this->namedErrors[$name];
        $schema = $existing instanceof self
            ? $existing
            : new self($existing->getValidator(), [$existing]);
        $schema->addError($error);
        $this->namedErrors[$name] = $schema;

        return $this;
    }

    public function addErrors(iterable $errors): static
    {
        if ($errors instanceof self) {
            foreach ($errors->getGlobalErrors() as $error) {
                $this->addError($error);
            }

            foreach ($errors->getNamedErrors() as $name => $error) {
                $this->addError($error, (string) $name);
            }

            return $this;
        }

        foreach ($errors as $name => $error) {
            if ($error instanceof ValidatorError) {
                $this->addError($error, $name);
            }
        }

        return $this;
    }

    public function getErrors(): array
    {
        return $this->globalErrors + $this->namedErrors;
    }

    public function getNamedErrors(): array
    {
        return $this->namedErrors;
    }

    public function getGlobalErrors(): array
    {
        return $this->globalErrors;
    }

    public function count(): int
    {
        return count($this->globalErrors) + count($this->namedErrors);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->globalErrors;

        yield from $this->namedErrors;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->namedErrors[$offset]);
    }

    public function offsetGet(mixed $offset): ?ValidatorError
    {
        return $this->namedErrors[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof ValidatorError) {
            throw new \InvalidArgumentException(
                'A form error must be a ValidatorError.',
            );
        }

        $this->addError($value, $offset);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->namedErrors[$offset]);
    }
}
