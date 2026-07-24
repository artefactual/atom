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

namespace Atom\Framework\Database;

class DatabaseManager
{
    private static ?PropelBootstrap $bootstrap = null;

    public function __construct(mixed $configuration = null)
    {
        $this->loadConfiguration();
    }

    public static function setBootstrap(?PropelBootstrap $bootstrap): void
    {
        self::$bootstrap = $bootstrap;
    }

    public function loadConfiguration(): void
    {
        if (null === self::$bootstrap) {
            throw new \RuntimeException(
                'The AtoM database runtime has not been configured.',
            );
        }

        self::$bootstrap->initialize();
    }

    public function getDatabase(string $name): Database
    {
        return new Database($name);
    }
}
