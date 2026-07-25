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

/**
 * @internal
 *
 * @covers \QubitFlatfileImport
 */
final class QubitFlatfileImportTest extends \PHPUnit\Framework\TestCase
{
    public function testAddsCultureToFirstRowWithoutAValue(): void
    {
        $import = new QubitFlatfileImport();
        $import->columnNames = [
            'legacyId',
            'parentId',
            'identifier',
            'title',
            'levelOfDescription',
        ];
        $import->status['row'] = [
            '1',
            '',
            'F1',
            'Test fonds',
            'Fonds',
        ];

        $method = new \ReflectionMethod($import, 'handleCulture');
        $method->setAccessible(true);
        $method->invoke($import);

        $this->assertSame('en', $import->columnValue('culture'));
        $this->assertCount(6, $import->status['row']);
    }
}
