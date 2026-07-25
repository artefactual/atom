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

final readonly class Probe
{
    private const MODES = ['live', 'ready'];
    private const ROLES = ['auto', 'fpm', 'worker', 'other'];

    public function __construct(
        private array $environment = [],
        private float $timeout = 2.0,
        private ?string $command = null,
    ) {
        if ($timeout <= 0) {
            throw new \InvalidArgumentException(
                'The health-check timeout must be greater than zero.',
            );
        }
    }

    public static function fromGlobals(): self
    {
        $environment = getenv();

        return new self(array_replace(
            is_array($environment) ? $environment : [],
            $_ENV,
            $_SERVER,
        ));
    }

    public function run(string $mode = 'ready'): array
    {
        if (!in_array($mode, self::MODES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Health-check mode must be one of: %s.',
                implode(', ', self::MODES),
            ));
        }

        $role = $this->role();
        $checks = [];
        $this->record(
            $checks,
            'process',
            fn () => $this->checkProcess($role),
        );

        if ('ready' === $mode) {
            $this->record($checks, 'database', $this->checkDatabase(...));
            $this->record(
                $checks,
                'elasticsearch',
                $this->checkElasticsearch(...),
            );

            if ($this->usesMemcacheSessions()) {
                $this->record(
                    $checks,
                    'memcached',
                    $this->checkMemcached(...),
                );
            }

            $this->record(
                $checks,
                'gearmand',
                fn () => $this->checkGearmand('worker' === $role),
            );
        }

        $healthy = !in_array(
            'fail',
            array_column($checks, 'status'),
            true,
        );

        return [
            'status' => $healthy ? 'ok' : 'fail',
            'mode' => $mode,
            'role' => $role,
            'checks' => $checks,
        ];
    }

    private function record(
        array &$checks,
        string $name,
        callable $check,
    ): void {
        $started = hrtime(true);

        try {
            $check();
            $status = 'ok';
            $message = null;
        } catch (\Throwable $exception) {
            $status = 'fail';
            $message = $exception->getMessage();
        }

        $result = [
            'status' => $status,
            'duration_ms' => round(
                (hrtime(true) - $started) / 1_000_000,
                2,
            ),
        ];

        if (null !== $message && '' !== $message) {
            $result['message'] = $message;
        }

        $checks[$name] = $result;
    }

    private function role(): string
    {
        $role = strtolower(
            trim($this->environment['ATOM_HEALTHCHECK_ROLE'] ?? 'auto'),
        );

        if (!in_array($role, self::ROLES, true)) {
            throw new \InvalidArgumentException(
                'ATOM_HEALTHCHECK_ROLE must be auto, fpm, worker, or other.',
            );
        }

        if ('auto' !== $role) {
            return $role;
        }

        $command = $this->processCommand();

        if (
            str_contains($command, 'php-fpm')
            || str_contains($command, 'entrypoint.sh fpm')
        ) {
            return 'fpm';
        }

        if (
            str_contains($command, 'jobs:worker')
            || str_contains($command, 'entrypoint.sh worker')
        ) {
            return 'worker';
        }

        return 'other';
    }

    private function checkProcess(string $role): void
    {
        if ('worker' === $role) {
            if (!str_contains($this->processCommand(), 'jobs:worker')) {
                throw new \RuntimeException(
                    'Worker process is not ready.',
                );
            }

            return;
        }

        if ('fpm' === $role) {
            $socket = $this->connect(new Endpoint('127.0.0.1', 9000));
            fclose($socket);
        }
    }

    private function checkDatabase(): void
    {
        try {
            $connection = new \PDO(
                $this->required('ATOM_MYSQL_DSN'),
                $this->required('ATOM_MYSQL_USERNAME'),
                $this->required('ATOM_MYSQL_PASSWORD'),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => max(1, (int) ceil($this->timeout)),
                ],
            );

            if ('1' !== (string) $connection->query('SELECT 1')->fetchColumn()) {
                throw new \RuntimeException();
            }
        } catch (\Throwable) {
            throw new \RuntimeException('Database is unavailable.');
        }
    }

    private function checkElasticsearch(): void
    {
        $endpoint = Endpoint::parse(
            $this->required('ATOM_ELASTICSEARCH_HOST'),
            9200,
        );
        $socket = $this->connect($endpoint);

        try {
            fwrite(
                $socket,
                "GET /_cluster/health?local=true HTTP/1.1\r\n"
                .'Host: '.$endpoint->socketAddress()."\r\n"
                ."Connection: close\r\n\r\n",
            );
            $response = stream_get_contents($socket);
        } finally {
            fclose($socket);
        }

        $parts = preg_split("/\r?\n\r?\n/", (string) $response, 2);

        if (
            2 !== count($parts)
            || 1 !== preg_match('/^HTTP\/\d(?:\.\d)? 200\b/', $parts[0])
        ) {
            throw new \RuntimeException('Elasticsearch is unavailable.');
        }

        try {
            $body = json_decode(
                $parts[1],
                true,
                flags: \JSON_THROW_ON_ERROR,
            );
        } catch (\JsonException) {
            throw new \RuntimeException(
                'Elasticsearch returned an invalid response.',
            );
        }

        if (!in_array($body['status'] ?? null, ['green', 'yellow'], true)) {
            throw new \RuntimeException(
                'Elasticsearch cluster health is red.',
            );
        }
    }

    private function usesMemcacheSessions(): bool
    {
        $storage = strtolower(trim(
            $this->environment['ATOM_SESSION_STORAGE']
                ?? (isset($this->environment['ATOM_MEMCACHED_HOST'])
                    ? 'memcache'
                    : 'file'),
        ));

        return 'memcache' === $storage;
    }

    private function checkMemcached(): void
    {
        $endpoint = Endpoint::parse(
            $this->required('ATOM_MEMCACHED_HOST'),
            11211,
        );
        $socket = $this->connect($endpoint);

        try {
            fwrite($socket, "version\r\n");
            $response = fgets($socket);
        } finally {
            fclose($socket);
        }

        if (!is_string($response) || !str_starts_with($response, 'VERSION ')) {
            throw new \RuntimeException('Memcached is unavailable.');
        }
    }

    private function checkGearmand(bool $requireWorker): void
    {
        $endpoint = Endpoint::parse(
            $this->required('ATOM_GEARMAND_HOST'),
            4730,
        );
        $socket = $this->connect($endpoint);
        $workers = 0;
        $complete = false;

        try {
            fwrite($socket, "status\r\n");

            while (false !== $line = fgets($socket)) {
                if ('.' === trim($line)) {
                    $complete = true;

                    break;
                }

                $columns = explode("\t", trim($line));
                $workers += (int) ($columns[3] ?? 0);
            }
        } finally {
            fclose($socket);
        }

        if (!$complete) {
            throw new \RuntimeException('Gearmand is unavailable.');
        }

        if ($requireWorker && $workers < 1) {
            throw new \RuntimeException(
                'No Gearman workers are registered.',
            );
        }
    }

    /**
     * @return resource
     */
    private function connect(Endpoint $endpoint)
    {
        $socket = @stream_socket_client(
            'tcp://'.$endpoint->socketAddress(),
            $errorCode,
            $errorMessage,
            $this->timeout,
            \STREAM_CLIENT_CONNECT,
        );

        if (false === $socket) {
            throw new \RuntimeException('Service is unavailable.');
        }

        stream_set_timeout(
            $socket,
            max(1, (int) ceil($this->timeout)),
        );

        return $socket;
    }

    private function required(string $name): string
    {
        $value = $this->environment[$name] ?? null;

        if (!is_string($value) || '' === trim($value)) {
            throw new \RuntimeException(sprintf(
                '%s is not configured.',
                $name,
            ));
        }

        return $value;
    }

    private function processCommand(): string
    {
        if (null !== $this->command) {
            return $this->command;
        }

        if (!is_readable('/proc/1/cmdline')) {
            return '';
        }

        return str_replace(
            "\0",
            ' ',
            (string) file_get_contents('/proc/1/cmdline'),
        );
    }
}
