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

namespace Atom\Tests\Framework\Plugin;

use Atom\Framework\Plugin\PdoPluginSettingsReader;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class PdoPluginSettingsReaderTest extends TestCase
{
    public function testReadsSerializedPluginSetting(): void
    {
        $connection = new \PDO('sqlite::memory:');
        $connection->exec(
            'CREATE TABLE setting (
                id INTEGER PRIMARY KEY,
                name TEXT,
                source_culture TEXT
            )',
        );
        $connection->exec(
            'CREATE TABLE setting_i18n (
                id INTEGER,
                culture TEXT,
                value TEXT
            )',
        );
        $connection->exec(
            "INSERT INTO setting VALUES (1, 'plugins', 'en')",
        );
        $statement = $connection->prepare(
            'INSERT INTO setting_i18n VALUES (1, ?, ?)',
        );
        $statement->execute([
            'en',
            serialize(['sfIsadPlugin', 'arRestApiPlugin']),
        ]);
        $reader = new PdoPluginSettingsReader(
            ['propel' => ['param' => ['dsn' => 'sqlite::memory:']]],
            static fn (array $parameters): \PDO => $connection,
        );

        self::assertSame(
            ['sfIsadPlugin', 'arRestApiPlugin'],
            $reader->read(),
        );
    }

    public function testReturnsNullWhenDatabaseIsUnavailable(): void
    {
        $reader = new PdoPluginSettingsReader(
            ['propel' => ['param' => ['dsn' => 'unavailable:']]],
            static function (array $parameters): \PDO {
                throw new \PDOException('Unavailable');
            },
        );

        self::assertNull($reader->read());
    }
}
