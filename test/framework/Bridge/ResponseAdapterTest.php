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

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\ResponseAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class ResponseAdapterTest extends TestCase
{
    public function testProvidesLegacyHeaderControls(): void
    {
        $response = new Response();
        $response->headers->set('X-Existing', 'value');
        $adapter = new ResponseAdapter($response);

        $adapter->clearHttpheaders();
        $adapter->setHeaderOnly(true);

        self::assertFalse($response->headers->has('X-Existing'));
        self::assertTrue($adapter->isHeaderOnly());
    }

    public function testAcceptsLegacyStatusText(): void
    {
        $response = new Response();
        $adapter = new ResponseAdapter($response);

        $adapter->setStatusCode(418, 'Compatibility');

        self::assertSame(418, $response->getStatusCode());
        self::assertStringContainsString(
            '418 Compatibility',
            (string) $response,
        );
    }
}
