<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Cache;

final class MemcacheCache extends Cache
{
    private ?\Memcache $memcache = null;

    public function initialize($options = [])
    {
        parent::initialize($options);

        if (!class_exists(\Memcache::class)) {
            throw new CacheException(
                'Memcache must be installed and enabled to use this cache.',
            );
        }

        if ($this->getOption('memcache') instanceof \Memcache) {
            $this->memcache = $this->getOption('memcache');

            return;
        }

        $this->memcache = new \Memcache();

        if ($servers = $this->getOption('servers')) {
            foreach ($servers as $server) {
                $connected = $this->memcache->addServer(
                    $server['host'],
                    $server['port'] ?? 11211,
                    $server['persistent'] ?? true,
                );

                if (!$connected) {
                    throw $this->connectionException(
                        $server['host'],
                        $server['port'] ?? 11211,
                    );
                }
            }

            return;
        }

        $host = $this->getOption('host', 'localhost');
        $port = $this->getOption('port', 11211);
        $method = $this->getOption('persistent', true)
            ? 'pconnect'
            : 'connect';
        $connected = $this->memcache->{$method}(
            $host,
            $port,
            $this->getOption('timeout', 1),
        );

        if (!$connected) {
            throw $this->connectionException($host, $port);
        }
    }

    public function getBackend()
    {
        return $this->memcache;
    }

    public function get($key, $default = null)
    {
        $value = $this->memcache->get($this->key($key));

        return false === $value ? $default : $value;
    }

    public function has($key)
    {
        return false !== $this->memcache->get($this->key($key));
    }

    public function set($key, $data, $lifetime = null)
    {
        $lifetime = (int) $this->getLifetime($lifetime);
        $expiration = 0 === $lifetime ? 0 : time() + $lifetime;

        $this->setMetadata($key, $lifetime);

        if ($this->getOption('storeCacheInfo', false)) {
            $this->updateCacheInformation($key);
        }

        if ($this->memcache->replace(
            $this->key($key),
            $data,
            0,
            $expiration,
        )) {
            return true;
        }

        return $this->memcache->set(
            $this->key($key),
            $data,
            0,
            $expiration,
        );
    }

    public function remove($key)
    {
        $this->memcache->delete($this->metadataKey($key), 0);

        if ($this->getOption('storeCacheInfo', false)) {
            $this->updateCacheInformation($key, true);
        }

        return $this->memcache->delete($this->key($key), 0);
    }

    public function removePattern($pattern)
    {
        if (!$this->getOption('storeCacheInfo', false)) {
            throw new CacheException(
                'The storeCacheInfo option is required for removePattern().',
            );
        }

        $regexp = $this->patternToRegexp(
            $this->getOption('prefix').$pattern,
        );

        foreach ($this->getCacheInformation() as $key) {
            if (preg_match($regexp, $key)) {
                $this->remove(substr(
                    $key,
                    strlen($this->getOption('prefix')),
                ));
            }
        }
    }

    public function clean($mode = self::ALL)
    {
        if (self::ALL === $mode) {
            return $this->memcache->flush();
        }

        return true;
    }

    public function getTimeout($key)
    {
        return $this->metadata($key)['timeout'] ?? 0;
    }

    public function getLastModified($key)
    {
        return $this->metadata($key)['lastModified'] ?? 0;
    }

    public function getMany($keys)
    {
        $prefix = $this->getOption('prefix');
        $prefixed = array_map(
            static fn ($key) => $prefix.$key,
            $keys,
        );
        $values = [];

        foreach ($this->memcache->get($prefixed) ?: [] as $key => $value) {
            $values[substr($key, strlen($prefix))] = $value;
        }

        return $values;
    }

    private function key($key): string
    {
        return $this->getOption('prefix').$key;
    }

    private function metadataKey($key): string
    {
        return $this->getOption('prefix')
            .'_metadata'.self::SEPARATOR.$key;
    }

    private function metadata($key): array
    {
        $metadata = $this->memcache->get($this->metadataKey($key));

        return is_array($metadata) ? $metadata : [];
    }

    private function setMetadata($key, int $lifetime): void
    {
        $now = time();
        $this->memcache->set(
            $this->metadataKey($key),
            [
                'lastModified' => $now,
                'timeout' => 0 === $lifetime ? 0 : $now + $lifetime,
            ],
            0,
            0 === $lifetime ? 0 : $now + $lifetime,
        );
    }

    private function updateCacheInformation(
        $key,
        bool $delete = false,
    ): void {
        $keys = $this->getCacheInformation();
        $cacheKey = $this->key($key);

        if ($delete) {
            $keys = array_values(array_diff($keys, [$cacheKey]));
        } elseif (!in_array($cacheKey, $keys, true)) {
            $keys[] = $cacheKey;
        }

        $this->memcache->set(
            $this->getOption('prefix').'_metadata',
            $keys,
            0,
            0,
        );
    }

    private function getCacheInformation(): array
    {
        $keys = $this->memcache->get(
            $this->getOption('prefix').'_metadata',
        );

        return is_array($keys) ? $keys : [];
    }

    private function connectionException(
        string $host,
        int $port,
    ): CacheException {
        return new CacheException(sprintf(
            'Unable to connect to the memcache server (%s:%d).',
            $host,
            $port,
        ));
    }
}
