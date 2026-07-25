<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Bridge;

use Atom\Framework\Configuration\ConfigurationException;
use Atom\Framework\Security\SecurityConfiguration;

final readonly class UserFactory
{
    public function create(
        string $class,
        SecurityConfiguration $security,
    ): User {
        if (User::class === $class || 'myUser' === $class) {
            return new User($security);
        }

        if (
            1 !== preg_match(
                '/^[a-z_][a-z0-9_\\\\]*$/i',
                $class,
            )
            || !class_exists($class)
            || !is_subclass_of($class, User::class)
        ) {
            throw new ConfigurationException(sprintf(
                'Configured user class "%s" must extend sfUser.',
                $class,
            ));
        }

        return new $class($security);
    }
}
