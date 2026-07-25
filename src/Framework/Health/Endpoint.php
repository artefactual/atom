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

namespace Atom\Framework\Health;

final readonly class Endpoint
{
    public function __construct(
        public string $host,
        public int $port,
    ) {
        if ('' === trim($host)) {
            throw new \InvalidArgumentException(
                'A service endpoint must include a host.',
            );
        }

        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException(
                'A service endpoint must include a valid port.',
            );
        }
    }

    public static function parse(string $endpoint, int $defaultPort): self
    {
        $uri = str_contains($endpoint, '://')
            ? $endpoint
            : 'tcp://'.$endpoint;
        $parts = parse_url($uri);

        if (
            false === $parts
            || !isset($parts['host'])
            || '' === trim((string) $parts['host'])
            || 'tcp' !== ($parts['scheme'] ?? 'tcp')
            || [] !== array_intersect(
                ['user', 'pass', 'query', 'fragment'],
                array_keys($parts),
            )
            || ('/' !== ($parts['path'] ?? '/'))
        ) {
            throw new \InvalidArgumentException(
                'The service endpoint is not a valid TCP endpoint.',
            );
        }

        $host = trim((string) $parts['host'], '[]');

        return new self($host, (int) ($parts['port'] ?? $defaultPort));
    }

    public function socketAddress(): string
    {
        $host = str_contains($this->host, ':')
            ? '['.$this->host.']'
            : $this->host;

        return sprintf('%s:%d', $host, $this->port);
    }
}
