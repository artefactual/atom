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

namespace Atom\Tests\Framework\Session;

use Atom\Framework\Cache\Cache;
use Atom\Framework\Session\CacheSessionHandler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CacheSessionHandlerTest extends TestCase
{
    public function testStoresRefreshesAndDestroysSessions(): void
    {
        $cache = new InMemorySessionCache();
        $handler = new CacheSessionHandler($cache, 3600);

        self::assertSame('', $handler->read('missing'));
        self::assertTrue($handler->write('session-id', 'first'));
        self::assertSame('first', $handler->read('session-id'));
        self::assertSame(3600, $cache->lifetimes['session-id']);
        self::assertTrue(
            $handler->updateTimestamp('session-id', 'refreshed'),
        );
        self::assertSame('refreshed', $handler->read('session-id'));
        self::assertTrue($handler->destroy('session-id'));
        self::assertTrue($handler->destroy('session-id'));
        self::assertSame(0, $handler->gc(3600));
        self::assertTrue($handler->close());
    }
}

final class InMemorySessionCache extends Cache
{
    public array $lifetimes = [];
    private array $values = [];

    public function get($key, $default = null)
    {
        return $this->values[$key] ?? $default;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->values);
    }

    public function set($key, $data, $lifetime = null)
    {
        $this->values[$key] = $data;
        $this->lifetimes[$key] = $lifetime;

        return true;
    }

    public function remove($key)
    {
        unset($this->values[$key], $this->lifetimes[$key]);

        return true;
    }

    public function removePattern($pattern) {}

    public function clean($mode = self::ALL)
    {
        $this->values = [];
        $this->lifetimes = [];

        return true;
    }

    public function getTimeout($key)
    {
        return 0;
    }

    public function getLastModified($key)
    {
        return 0;
    }
}
