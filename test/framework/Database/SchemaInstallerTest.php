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

namespace Atom\Tests\Framework\Database;

use Atom\Framework\Database\SchemaInstaller;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SchemaInstallerTest extends TestCase
{
    public function testSplitsStatementsOutsideQuotedValues(): void
    {
        $sql = <<<'SQL'
            # Leading comment with a semicolon;
            CREATE TABLE `example;table` (`value` VARCHAR(255));
            INSERT INTO `example;table` VALUES ('one;two'), ("three;four");
            -- Trailing comment;
            SQL;

        self::assertSame([
            'CREATE TABLE `example;table` (`value` VARCHAR(255))',
            <<<'SQL'
                INSERT INTO `example;table` VALUES ('one;two'), ("three;four")
                SQL,
        ], (new SchemaInstaller())->statements($sql));
    }

    public function testPreservesEscapedAndDoubledQuotes(): void
    {
        $sql = <<<'SQL'
            INSERT INTO example VALUES ('it''s; valid');
            INSERT INTO example VALUES ('escaped \'; value');
            SQL;

        self::assertCount(
            2,
            (new SchemaInstaller())->statements($sql),
        );
    }
}
