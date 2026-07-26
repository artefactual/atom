<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

use PHPUnit\Framework\TestCase;

/**
 * @covers \sfWebBrowser
 *
 * @internal
 */
final class sfWebBrowserTest extends TestCase
{
    public function testInitializesAndResetsBrowserState(): void
    {
        $browser = new sfWebBrowser([], 'sfFopenAdapter');

        self::assertSame('', $browser->getUserAgent());
        self::assertSame('', $browser->getResponseText());
        self::assertSame('', $browser->getResponseCode());
        self::assertSame([], $browser->getResponseHeaders());

        $browser
            ->setUserAgent('AtoM test')
            ->setResponseText('response body')
            ->setResponseCode('HTTP/1.1 204 No Content')
            ->setResponseHeaders(['Content-Type: text/plain']);

        self::assertSame('AtoM test', $browser->getUserAgent());
        self::assertSame('response body', $browser->getResponseText());
        self::assertSame('204', $browser->getResponseCode());
        self::assertSame(
            'text/plain',
            $browser->getResponseHeader('content-type'),
        );

        $browser->restart();

        self::assertSame('', $browser->getUserAgent());
        self::assertSame('', $browser->getResponseText());
        self::assertSame('', $browser->getResponseCode());
        self::assertSame([], $browser->getResponseHeaders());
    }

    public function testRejectsUnsupportedSchemes(): void
    {
        $browser = new sfWebBrowser([], 'sfFopenAdapter');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'sfWebBrowser handles only http and https requests',
        );

        $browser->get('htp://example.test');
    }
}
