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

class ParameterHolder implements \Serializable
{
    protected array $parameters = [];

    public function __construct(array $parameters = [])
    {
        $this->add($parameters);
    }

    public function __serialize(): array
    {
        return $this->parameters;
    }

    public function __unserialize(array $data): void
    {
        $this->parameters = $data;
    }

    public function clear(): void
    {
        $this->parameters = [];
    }

    public function &get(int|string $name, mixed $default = null): mixed
    {
        if (array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        return $default;
    }

    public function getRaw(
        int|string $name,
        mixed $default = null,
    ): mixed {
        return $this->get($name, $default);
    }

    public function getNames(): array
    {
        return array_keys($this->parameters);
    }

    public function &getAll(): array
    {
        return $this->parameters;
    }

    public function has(int|string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    public function remove(int|string $name, mixed $default = null): mixed
    {
        if (!$this->has($name)) {
            return $default;
        }

        $value = $this->parameters[$name];
        unset($this->parameters[$name]);

        return $value;
    }

    public function removeNamespace(string $namespace): void
    {
        $prefix = rtrim($namespace, '/').'/';

        foreach (array_keys($this->parameters) as $name) {
            if (str_starts_with((string) $name, $prefix)) {
                unset($this->parameters[$name]);
            }
        }
    }

    public function set(int|string $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function setByRef(int|string $name, mixed &$value): bool
    {
        $this->parameters[$name] = &$value;

        return true;
    }

    public function add(?array $parameters): void
    {
        foreach ($parameters ?? [] as $name => $value) {
            $this->parameters[$name] = $value;
        }
    }

    public function addByRef(array &$parameters): void
    {
        foreach ($parameters as $name => &$value) {
            $this->parameters[$name] = &$value;
        }
    }

    public function serialize(): string
    {
        return serialize($this->parameters);
    }

    public function unserialize(string $data): void
    {
        $this->parameters = unserialize($data);
    }
}
