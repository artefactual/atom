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

final readonly class Database
{
    public function __construct(private string $name) {}

    public function getConnection(): \PropelPDO
    {
        return \Propel::getConnection(
            'propel' === $this->name ? null : $this->name,
        );
    }
}
