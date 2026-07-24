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

final class ApcCache extends Cache
{
    public function initialize($options = [])
    {
        parent::initialize($options);

        if (!function_exists('apcu_store')) {
            throw new CacheException(
                'APCu must be installed and enabled to use this cache.',
            );
        }
    }

    public function get($key, $default = null)
    {
        $value = apcu_fetch($this->key($key), $success);

        return $success ? $value : $default;
    }

    public function has($key)
    {
        return apcu_exists($this->key($key));
    }

    public function set($key, $data, $lifetime = null)
    {
        $lifetime = (int) $this->getLifetime($lifetime);
        $now = time();
        $stored = apcu_store($this->key($key), $data, $lifetime);

        if ($stored) {
            apcu_store($this->metadataKey($key), [
                'lastModified' => $now,
                'timeout' => 0 === $lifetime ? 0 : $now + $lifetime,
            ], $lifetime);
        }

        return $stored;
    }

    public function remove($key)
    {
        apcu_delete($this->metadataKey($key));

        return apcu_delete($this->key($key));
    }

    public function removePattern($pattern)
    {
        $information = apcu_cache_info();
        $entries = $information['cache_list'] ?? [];
        $regexp = $this->patternToRegexp(
            $this->getOption('prefix').$pattern,
        );

        foreach ($entries as $entry) {
            $key = $entry['info'] ?? $entry['key'] ?? null;

            if (is_string($key) && preg_match($regexp, $key)) {
                apcu_delete($key);
            }
        }
    }

    public function clean($mode = self::ALL)
    {
        if (self::ALL === $mode) {
            return apcu_clear_cache();
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
        $metadata = apcu_fetch($this->metadataKey($key), $success);

        return $success && is_array($metadata) ? $metadata : [];
    }
}
