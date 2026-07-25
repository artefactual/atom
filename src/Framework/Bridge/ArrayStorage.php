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

final class ArrayStorage extends Storage
{
    private array $values = [];

    public function read($key)
    {
        return $this->values[$key] ?? null;
    }

    public function regenerate($destroy = false)
    {
        if ($destroy) {
            $this->values = [];
        }

        return true;
    }

    public function remove($key)
    {
        $value = $this->values[$key] ?? null;
        unset($this->values[$key]);

        return $value;
    }

    public function shutdown() {}

    public function write($key, $data)
    {
        $this->values[$key] = $data;
    }
}
