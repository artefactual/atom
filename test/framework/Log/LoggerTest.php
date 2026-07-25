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

namespace Atom\Tests\Framework\Log;

use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Log\Logger;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class LoggerTest extends TestCase
{
    public function testLegacySubclassCanSetDynamicProperties(): void
    {
        $logger = new class(new EventDispatcher()) extends Logger {
            protected function doLog($message, $priority) {}
        };
        set_error_handler(
            static fn (
                int $severity,
                string $message,
            ): never => throw new \ErrorException(
                $message,
                0,
                $severity,
            ),
        );

        try {
            $logger->legacyProperty = 'value';
        } finally {
            restore_error_handler();
        }

        self::assertSame('value', $logger->legacyProperty);
    }
}
