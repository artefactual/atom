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
 * @covers \sfSkosUniqueRelations
 *
 * @internal
 */
final class sfSkosUniqueRelationsTest extends TestCase
{
    /**
     * @dataProvider relationProvider
     */
    public function testStoresOnlyUniqueUnorderedPairs(
        array $relations,
        array $expected,
    ): void {
        $uniqueRelations = new sfSkosUniqueRelations();

        foreach ($relations as [$first, $second]) {
            $uniqueRelations->insert($first, $second);
        }

        self::assertSame($expected, $uniqueRelations->getAll());
        self::assertCount(count($expected), $uniqueRelations);
        self::assertSame($expected, iterator_to_array($uniqueRelations, false));

        foreach ($expected as [$first, $second]) {
            self::assertTrue($uniqueRelations->exists($first, $second));
            self::assertTrue($uniqueRelations->exists($second, $first));
        }
    }

    public function relationProvider(): array
    {
        return [
            [
                [[1, 2], [1, 3], [1, 4], [2, 3], [4, 1]],
                [[1, 2], [1, 3], [1, 4], [2, 3]],
            ],
            [
                [[10, 20], [3, 3], [3, 3]],
                [[10, 20], [3, 3]],
            ],
        ];
    }
}
