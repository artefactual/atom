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

class SessionTestStorage extends Storage
{
    protected string $sessionId;
    protected array $sessionData = [];

    public function initialize($options = [])
    {
        if (!isset($options['session_path'])) {
            throw new \InvalidArgumentException(
                'The "session_path" option is required.',
            );
        }

        parent::initialize(array_replace([
            'session_id' => null,
        ], $options));

        $this->sessionId = (string) (
            $this->options['session_id']
            ?? $_SERVER['session_id']
            ?? md5(uniqid((string) mt_rand(), true))
        );
        $path = $this->sessionPath();

        if (is_file($path)) {
            $data = unserialize((string) file_get_contents($path));
            $this->sessionData = is_array($data) ? $data : [];
        }
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function read($key)
    {
        return $this->sessionData[$key] ?? null;
    }

    public function regenerate($destroy = false)
    {
        $this->sessionId = md5(uniqid((string) mt_rand(), true));

        return true;
    }

    public function remove($key)
    {
        $value = $this->sessionData[$key] ?? null;
        unset($this->sessionData[$key]);

        return $value;
    }

    public function shutdown()
    {
        $directory = (string) $this->options['session_path'];

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents(
            $this->sessionPath(),
            serialize($this->sessionData),
        );
    }

    public function write($key, $data)
    {
        $this->sessionData[$key] = $data;
    }

    private function sessionPath(): string
    {
        return rtrim(
            (string) $this->options['session_path'],
            '/\\',
        ).'/'.$this->sessionId.'.session';
    }
}
