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

namespace Atom\Tests\Framework\Health;

use Atom\Framework\Health\Probe;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ProbeTest extends TestCase
{
    public function testRunsDependencyFreeLivenessCheck(): void
    {
        $result = (new Probe([
            'ATOM_HEALTHCHECK_ROLE' => 'other',
        ]))->run('live');

        self::assertSame('ok', $result['status']);
        self::assertSame('other', $result['role']);
        self::assertSame(['process'], array_keys($result['checks']));
    }

    public function testReportsMissingReadinessConfiguration(): void
    {
        $result = (new Probe([
            'ATOM_HEALTHCHECK_ROLE' => 'other',
        ]))->run();

        self::assertSame('fail', $result['status']);
        self::assertSame(
            ['process', 'database', 'elasticsearch', 'gearmand'],
            array_keys($result['checks']),
        );
        self::assertSame(
            'fail',
            $result['checks']['database']['status'],
        );
    }

    public function testWaitsForWorkerProcessToStart(): void
    {
        $starting = (new Probe(
            command: 'bash docker/entrypoint.sh worker',
        ))->run('live');
        $running = (new Probe(
            command: 'php symfony jobs:worker',
        ))->run('live');

        self::assertSame('worker', $starting['role']);
        self::assertSame('fail', $starting['status']);
        self::assertSame('worker', $running['role']);
        self::assertSame('ok', $running['status']);
    }

    public function testSkipsMemcachedForFileSessions(): void
    {
        $result = (new Probe([
            'ATOM_HEALTHCHECK_ROLE' => 'other',
            'ATOM_SESSION_STORAGE' => 'file',
            'ATOM_MEMCACHED_HOST' => 'cache:11211',
        ]))->run();

        self::assertArrayNotHasKey('memcached', $result['checks']);
    }

    public function testRejectsUnknownModes(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new Probe())->run('unknown');
    }
}
