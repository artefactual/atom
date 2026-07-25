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

abstract class Storage
{
    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->initialize($options);

        if ($this->options['auto_shutdown']) {
            register_shutdown_function($this->shutdown(...));
        }
    }

    public function initialize($options = [])
    {
        $this->options = array_replace([
            'auto_shutdown' => true,
        ], $options ?? []);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    abstract public function read($key);

    abstract public function regenerate($destroy = false);

    abstract public function remove($key);

    abstract public function shutdown();

    abstract public function write($key, $data);
}
