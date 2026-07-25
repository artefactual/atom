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

namespace Atom\Framework\Session;

use Atom\Framework\Cache\Cache;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;

final class CacheSessionHandler extends AbstractSessionHandler
{
    public function __construct(
        private readonly Cache $cache,
        private readonly int $ttl,
    ) {}

    public function close(): bool
    {
        return true;
    }

    public function updateTimestamp(
        #[\SensitiveParameter] string $sessionId,
        string $data,
    ): bool {
        return $this->doWrite($sessionId, $data);
    }

    public function gc(int $maxlifetime): false|int
    {
        return 0;
    }

    protected function doRead(
        #[\SensitiveParameter] string $sessionId,
    ): string {
        return (string) $this->cache->get($sessionId, '');
    }

    protected function doWrite(
        #[\SensitiveParameter] string $sessionId,
        string $data,
    ): bool {
        return (bool) $this->cache->set(
            $sessionId,
            $data,
            $this->ttl,
        );
    }

    protected function doDestroy(
        #[\SensitiveParameter] string $sessionId,
    ): bool {
        return !$this->cache->has($sessionId)
            || (bool) $this->cache->remove($sessionId);
    }
}
