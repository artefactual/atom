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

use Atom\Framework\Database\PropelSchemaConverter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PropelSchemaConverterTest extends TestCase
{
    public function testConvertsLegacySchemaSections(): void
    {
        $schema = [
            'propel' => [
                '_attributes' => ['package' => 'lib.model'],
                '_propel_behaviors' => ['timestampable' => []],
                'example_record' => [
                    '_attributes' => [
                        'phpName' => 'QubitExample',
                        'idMethod' => 'native',
                    ],
                    'id' => [
                        'type' => 'integer',
                        'primaryKey' => true,
                    ],
                    '_indexes' => [
                        'example_record_id' => ['id'],
                    ],
                ],
            ],
        ];

        self::assertSame([
            'connection' => 'propel',
            'package' => 'lib.model',
            'propel_behaviors' => ['timestampable' => []],
            'classes' => [
                'QubitExample' => [
                    'idMethod' => 'native',
                    'tableName' => 'example_record',
                    'columns' => [
                        'id' => [
                            'type' => 'integer',
                            'primaryKey' => true,
                        ],
                    ],
                    'indexes' => [
                        'example_record_id' => ['id'],
                    ],
                ],
            ],
        ], (new PropelSchemaConverter())->oldToNew($schema));
    }

    public function testDerivesClassNameFromTable(): void
    {
        $schema = [
            'propel' => [
                'example_record' => [
                    'id' => ['type' => 'integer'],
                ],
            ],
        ];

        self::assertArrayHasKey(
            'ExampleRecord',
            (new PropelSchemaConverter())->oldToNew($schema)['classes'],
        );
    }
}
