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

use Atom\Framework\Health\Endpoint;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EndpointTest extends TestCase
{
    public function testParsesTcpEndpoints(): void
    {
        self::assertEquals(
            new Endpoint('cache', 11211),
            Endpoint::parse('cache', 11211),
        );
        self::assertEquals(
            new Endpoint('::1', 9201),
            Endpoint::parse('tcp://[::1]:9201', 9200),
        );
        self::assertSame(
            '[::1]:9201',
            Endpoint::parse('[::1]:9201', 9200)->socketAddress(),
        );
    }

    /**
     * @dataProvider invalidEndpointProvider
     */
    public function testRejectsInvalidEndpoints(string $endpoint): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Endpoint::parse($endpoint, 9200);
    }

    public static function invalidEndpointProvider(): iterable
    {
        yield 'credentials' => ['user:password@search:9200'];

        yield 'path' => ['search:9200/health'];

        yield 'scheme' => ['https://search:9200'];

        yield 'port' => ['search:70000'];
    }
}
