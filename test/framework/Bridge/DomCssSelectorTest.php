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

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\DomCssSelector;
use Atom\Kernel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DomCssSelectorTest extends TestCase
{
    public function testSupportsTheLegacyWebBrowserConsumer(): void
    {
        $kernel = new Kernel('test', false);

        try {
            $kernel->boot();
            $browser = new \sfWebBrowser([], 'sfFopenAdapter');
            $browser
                ->setResponseHeaders(['Content-Type: text/html'])
                ->setResponseText(
                    '<html><body><span class="item">One</span></body></html>',
                );

            $selector = $browser->getResponseDomCssSelector();

            self::assertInstanceOf(DomCssSelector::class, $selector);
            self::assertSame(
                'One',
                $selector->matchSingle('span.item')->getValue(),
            );
        } finally {
            $kernel->shutdown();
        }
    }

    public function testSelectsAndIteratesOverDomNodes(): void
    {
        $document = new \DOMDocument();
        $document->loadHTML(<<<'HTML'
            <div id="content">
              <span class="item" data-kind="first">One</span>
              <span class="item" data-kind="second">Two</span>
            </div>
            HTML);
        $selector = new DomCssSelector($document);
        $items = $selector->matchAll('div#content > span.item');

        self::assertCount(2, $items);
        self::assertSame(['One', 'Two'], $items->getValues());
        self::assertSame(
            'Two',
            $selector
                ->matchSingle('span[data-kind="second"]')
                ->getValue(),
        );
        self::assertSame(
            ['One', 'Two'],
            array_map(
                static fn (\DOMNode $node): ?string => $node->nodeValue,
                iterator_to_array($items),
            ),
        );
    }
}
