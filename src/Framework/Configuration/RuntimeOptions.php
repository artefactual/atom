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

namespace Atom\Framework\Configuration;

final readonly class RuntimeOptions
{
    public function __construct(private array $environment = []) {}

    public static function fromGlobals(): self
    {
        $environment = getenv();

        return new self(array_replace(
            is_array($environment) ? $environment : [],
            $_ENV,
            $_SERVER,
        ));
    }

    public function session(
        string $projectDirectory,
        string $environment,
    ): array {
        $endpoint = $this->value('ATOM_MEMCACHED_HOST');
        $storage = strtolower(
            $this->value('ATOM_SESSION_STORAGE')
                ?? (null === $endpoint ? 'file' : 'memcache'),
        );

        if ('file' === $storage) {
            return [
                'storage' => 'file',
                'save_path' => $this->value('ATOM_SESSION_PATH')
                    ?? rtrim($projectDirectory, '/\\')
                        .'/var/sessions/'.$environment,
            ];
        }

        if ('memcache' !== $storage) {
            throw new ConfigurationException(sprintf(
                'ATOM_SESSION_STORAGE must be "file" or "memcache", "%s"'
                    .' given.',
                $storage,
            ));
        }

        if (null === $endpoint) {
            throw new ConfigurationException(
                'ATOM_MEMCACHED_HOST is required for memcache sessions.',
            );
        }

        [$host, $port] = $this->endpoint($endpoint);
        $ttl = $this->positiveInteger('ATOM_SESSION_TTL', 86400);
        $prefix = $this->value('ATOM_SESSION_PREFIX') ?? 'atom_session_';

        if (1 !== preg_match('/^[a-z0-9_.:-]+$/i', $prefix)) {
            throw new ConfigurationException(
                'ATOM_SESSION_PREFIX contains characters that are not safe'
                    .' in a memcache key.',
            );
        }

        return [
            'storage' => 'memcache',
            'host' => $host,
            'port' => $port,
            'ttl' => $ttl,
            'prefix' => $prefix,
        ];
    }

    public function trustedProxies(): array
    {
        return $this->list(
            'ATOM_TRUSTED_PROXIES',
            'SYMFONY_TRUSTED_PROXIES',
        );
    }

    public function trustedHosts(): array
    {
        return $this->list(
            'ATOM_TRUSTED_HOSTS',
            'SYMFONY_TRUSTED_HOSTS',
        );
    }

    private function endpoint(string $endpoint): array
    {
        $uri = str_contains($endpoint, '://')
            ? $endpoint
            : 'tcp://'.$endpoint;
        $parts = parse_url($uri);

        if (
            false === $parts
            || !isset($parts['host'])
            || '' === $parts['host']
            || [] !== array_intersect(
                ['user', 'pass', 'query', 'fragment'],
                array_keys($parts),
            )
            || ('tcp' !== ($parts['scheme'] ?? 'tcp'))
            || ('/' !== ($parts['path'] ?? '/'))
        ) {
            throw new ConfigurationException(sprintf(
                'ATOM_MEMCACHED_HOST "%s" is not a valid TCP endpoint.',
                $endpoint,
            ));
        }

        $port = $parts['port'] ?? 11211;

        if ($port < 1 || $port > 65535) {
            throw new ConfigurationException(
                'ATOM_MEMCACHED_HOST contains an invalid port.',
            );
        }

        return [trim($parts['host'], '[]'), $port];
    }

    private function positiveInteger(string $name, int $default): int
    {
        $value = $this->value($name);

        if (null === $value) {
            return $default;
        }

        $integer = filter_var($value, \FILTER_VALIDATE_INT);

        if (false === $integer || $integer < 1) {
            throw new ConfigurationException(sprintf(
                '%s must be a positive integer.',
                $name,
            ));
        }

        return $integer;
    }

    private function list(string $name, string $fallback): array
    {
        $value = $this->value($name) ?? $this->value($fallback);

        if (null === $value) {
            return [];
        }

        return array_values(array_filter(
            array_map(trim(...), explode(',', $value)),
            static fn (string $item): bool => '' !== $item,
        ));
    }

    private function value(string $name): ?string
    {
        $value = $this->environment[$name] ?? null;

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }
}
