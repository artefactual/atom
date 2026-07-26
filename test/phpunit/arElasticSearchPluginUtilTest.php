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
 * @covers \arElasticSearchPluginUtil
 *
 * @internal
 */
final class arElasticSearchPluginUtilTest extends TestCase
{
    private mixed $originalEscapeQueries;

    protected function setUp(): void
    {
        $this->originalEscapeQueries = sfConfig::get('app_escape_queries');
    }

    protected function tearDown(): void
    {
        sfConfig::set(
            'app_escape_queries',
            $this->originalEscapeQueries,
        );
    }

    /**
     * @dataProvider escapeTermProvider
     */
    public function testEscapesConfiguredQueryCharacters(
        string $setting,
        string $input,
        string $expected,
    ): void {
        sfConfig::set('app_escape_queries', $setting);

        self::assertSame(
            $expected,
            arElasticSearchPluginUtil::escapeTerm($input),
        );
    }

    public function escapeTermProvider(): array
    {
        return [
            'disabled' => [
                '',
                'FO1/23-BAR\\456',
                'FO1/23-BAR\\456',
            ],
            'backslash and slash' => [
                '\\,/',
                'FO1/23-BAR\\456',
                'FO1\\/23-BAR\\\\456',
            ],
            'unordered setting' => [
                '/,\\',
                'FO1/23-BAR\\456',
                'FO1\\/23-BAR\\\\456',
            ],
            'empty entries' => [
                '/, ,[,]',
                'FO1/23-BAR[456]',
                'FO1\\/23-BAR\\[456\\]',
            ],
            'whitespace' => [
                ' / , [ , ] ',
                'FO1/23-BAR[456]',
                'FO1\\/23-BAR\\[456\\]',
            ],
        ];
    }
}
