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

abstract class Cache
{
    public const OLD = 1;
    public const ALL = 2;
    public const SEPARATOR = ':';

    protected array $options = [];

    public function __construct($options = [])
    {
        $this->initialize($options);
    }

    public function initialize($options = [])
    {
        $this->options = array_replace([
            'automatic_cleaning_factor' => 1000,
            'lifetime' => 86400,
            'prefix' => md5(__DIR__),
        ], $options);
        $this->options['prefix'] .= self::SEPARATOR;
    }

    abstract public function get($key, $default = null);

    abstract public function has($key);

    abstract public function set($key, $data, $lifetime = null);

    abstract public function remove($key);

    abstract public function removePattern($pattern);

    abstract public function clean($mode = self::ALL);

    abstract public function getTimeout($key);

    abstract public function getLastModified($key);

    public function getMany($keys)
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }

        return $values;
    }

    public function getLifetime($lifetime)
    {
        return null === $lifetime
            ? $this->getOption('lifetime')
            : $lifetime;
    }

    public function getBackend()
    {
        throw new CacheException(
            'This cache class does not have a backend object.',
        );
    }

    public function getOption($name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    public function setOption($name, $value)
    {
        return $this->options[$name] = $value;
    }

    protected function patternToRegexp($pattern): string
    {
        $separator = preg_quote(self::SEPARATOR, '#');
        $regexp = str_replace(
            ['\\*\\*', '\\*'],
            ['.+?', '[^'.$separator.']+'],
            preg_quote((string) $pattern, '#'),
        );

        return '#^'.$regexp.'$#';
    }
}
