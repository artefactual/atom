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

class User
{
    public mixed $user = null;

    private ParameterHolder $attributes;
    private ParameterHolder $flashes;
    private bool $authenticated = false;
    private string $culture = 'en';
    private array $credentials = [];

    public function __construct()
    {
        $this->attributes = new ParameterHolder();
        $this->flashes = new ParameterHolder();
    }

    public function getAttribute(
        string $name,
        mixed $default = null,
        string $namespace = 'symfony/user/sfUser/attributes',
    ): mixed {
        return $this->attributes->get($namespace.'/'.$name, $default);
    }

    public function setAttribute(
        string $name,
        mixed $value,
        string $namespace = 'symfony/user/sfUser/attributes',
    ): void {
        $this->attributes->set($namespace.'/'.$name, $value);
    }

    public function hasAttribute(
        string $name,
        string $namespace = 'symfony/user/sfUser/attributes',
    ): bool {
        return $this->attributes->has($namespace.'/'.$name);
    }

    public function getAttributeHolder(): ParameterHolder
    {
        return $this->attributes;
    }

    public function setFlash(string $name, mixed $value): void
    {
        $this->flashes->set($name, $value);
    }

    public function getFlash(string $name, mixed $default = null): mixed
    {
        return $this->flashes->remove($name, $default);
    }

    public function hasFlash(string $name): bool
    {
        return $this->flashes->has($name);
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function setAuthenticated(bool $authenticated): void
    {
        $this->authenticated = $authenticated;
    }

    public function getCulture(): string
    {
        return $this->culture;
    }

    public function setCulture(string $culture): void
    {
        $this->culture = $culture;
    }

    public function addCredential(array|string $credentials): void
    {
        foreach ((array) $credentials as $credential) {
            $this->credentials[(string) $credential] = true;
        }
    }

    public function hasCredential(
        array|string $credentials,
        bool $useAnd = true,
    ): bool {
        $matches = array_map(
            fn (string $credential): bool => isset(
                $this->credentials[$credential],
            ),
            (array) $credentials,
        );

        return $useAnd
            ? !in_array(false, $matches, true)
            : in_array(true, $matches, true);
    }

    public function isAdministrator(): bool
    {
        return $this->hasCredential('administrator');
    }

    public function hasGroup(int|string $group): bool
    {
        return false;
    }
}
