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

use Atom\Framework\Bridge\AssetRenderer;
use Atom\Framework\Bridge\ResponseAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversNothing
 */
final class AssetRendererTest extends TestCase
{
    public function testRendersAssetsInConfiguredPositionOrder(): void
    {
        $response = new ResponseAdapter(new Response());
        $response->addJavaScript('application');
        $response->addJavaScript('runtime', 'first', ['defer' => true]);
        $response->addJavaScript('analytics', 'last');
        $response->addStylesheet(
            'print',
            'last',
            ['media' => 'print'],
        );
        $renderer = new AssetRenderer();

        self::assertSame(
            '<script defer="defer" src="/js/runtime.js"></script>'."\n"
                .'<script src="/js/application.js"></script>'."\n"
                .'<script src="/js/analytics.js"></script>'."\n",
            $renderer->javaScripts($response),
        );
        self::assertSame(
            '<link media="print" rel="stylesheet"'
                .' href="/css/print.css" />'."\n",
            $renderer->stylesheets($response),
        );
    }

    public function testEscapesConfiguredMetadata(): void
    {
        $response = new ResponseAdapter(new Response());
        $response->addHttpMeta('X-UA-Compatible', 'IE=edge');
        $response->addMeta('description', 'AtoM & archives');
        $renderer = new AssetRenderer();

        self::assertSame(
            '<meta http-equiv="X-UA-Compatible" content="IE=edge" />'
                ."\n",
            $renderer->httpMetas($response),
        );
        self::assertSame(
            '<meta name="description"'
                .' content="AtoM &amp; archives" />'."\n",
            $renderer->metas($response),
        );
    }
}
