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

namespace Atom\Tests\Framework\Translation;

use Atom\Framework\Translation\PhpMessageExtractor;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PhpMessageExtractorTest extends TestCase
{
    public function testExtractsLiteralLegacyMessages(): void
    {
        $contents = <<<'PHP'
            <?php
            __('A message');
            $translator->__("Another \"message\"");
            format_number_choice('Plural message', ['%count%' => 2], 2);
            __('Ignored '.$dynamic);
            PHP;

        self::assertSame([
            'A message',
            'Another "message"',
            'Plural message',
            'Ignored ',
        ], (new PhpMessageExtractor())->extract($contents));
    }
}
