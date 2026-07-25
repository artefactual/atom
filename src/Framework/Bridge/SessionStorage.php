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

class SessionStorage extends Storage
{
    protected static bool $sessionIdRegenerated = false;
    protected static bool $sessionStarted = false;

    public function initialize($options = [])
    {
        $cookies = session_get_cookie_params();
        $options = array_replace([
            'session_name' => 'symfony',
            'session_id' => null,
            'auto_start' => true,
            'session_cookie_lifetime' => $cookies['lifetime'],
            'session_cookie_domain' => $cookies['domain'],
            'session_cookie_path' => $cookies['path'],
            'session_cookie_secure' => $cookies['secure'],
            'session_cookie_httponly' => $cookies['httponly'],
            'session_cache_limiter' => null,
        ], $options ?? []);

        parent::initialize($options);

        if (\PHP_SESSION_ACTIVE === session_status()) {
            self::$sessionStarted = true;

            return;
        }

        session_name((string) $this->options['session_name']);

        if (
            !(bool) ini_get('session.use_cookies')
            && null !== $this->options['session_id']
        ) {
            session_id((string) $this->options['session_id']);
        }

        session_set_cookie_params([
            'lifetime' => (int) $this->options[
                'session_cookie_lifetime'
            ],
            'path' => (string) $this->options['session_cookie_path'],
            'domain' => (string) $this->options[
                'session_cookie_domain'
            ],
            'secure' => (bool) $this->options[
                'session_cookie_secure'
            ],
            'httponly' => (bool) $this->options[
                'session_cookie_httponly'
            ],
        ]);

        if (null !== $this->options['session_cache_limiter']) {
            session_cache_limiter(
                (string) $this->options['session_cache_limiter'],
            );
        }

        if ($this->options['auto_start']) {
            session_start();
            self::$sessionStarted = true;
        }
    }

    public function read($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function regenerate($destroy = false)
    {
        if (
            self::$sessionIdRegenerated
            || \PHP_SESSION_ACTIVE !== session_status()
        ) {
            return false;
        }

        $regenerated = session_regenerate_id($destroy);
        self::$sessionIdRegenerated = $regenerated;

        return $regenerated;
    }

    public function remove($key)
    {
        $value = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);

        return $value;
    }

    public function shutdown()
    {
        if (\PHP_SESSION_ACTIVE === session_status()) {
            session_write_close();
        }

        self::$sessionStarted = false;
    }

    public function write($key, $data)
    {
        $_SESSION[$key] = $data;
    }
}
