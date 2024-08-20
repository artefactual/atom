<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/lib/query/arSolrAbstractQuery.class.php';

require_once 'plugins/arSolrPlugin/lib/query/arSolrTermsQuery.class.php';

require_once 'plugins/arSolrPlugin/lib/query/arSolrIdsQuery.class.php';

/**
 * @internal
 *
 * @covers \arSolrIdsQuery
 * @covers \arSolrTermsQuery
 */
class ArSolrIdsQueryTest extends TestCase
{
    public function testCreateEmptySolrIdsQuery()
    {
        $this->idsQuery = new arSolrIdsQuery();
        $this->assertTrue($this->idsQuery instanceof arSolrIdsQuery, 'Assert plugin object is arSolrIdsQuery.');
    }

    public function createSolrIdsQueryProvider(): array
    {
        return [
            'New arSolrIdsQuery with no ids' => [
                'ids' => null,
                'expectedField' => 'id',
                'expectedValue' => null,
            ],
            'New arSolrIdsQuery with empty ids' => [
                'ids' => [],
                'expectedField' => 'id',
                'expectedValue' => [],
            ],
            'New arSolrIdsQuery with one id' => [
                'ids' => [123],
                'expectedField' => 'id',
                'expectedValue' => [123],
            ],
            'New arSolrIdsQuery with multiple ids' => [
                'ids' => [123, 456],
                'expectedField' => 'id',
                'expectedValue' => [123, 456],
            ],
        ];
    }

    /**
     * @dataProvider createSolrIdsQueryProvider
     *
     * @param mixed $ids
     * @param mixed $expectedField
     * @param mixed $expectedValue
     */
    public function testCreateSolrIdsQuery($ids, $expectedField, $expectedValue)
    {
        $this->idsQuery = new arSolrIdsQuery($ids);
        $actualField = $this->idsQuery->getTermField();
        $actualValues = $this->idsQuery->getIds();

        $this->assertTrue($this->idsQuery instanceof arSolridsQuery, 'Assert plugin object is arSolrIdsQuery.');
        $this->assertSame($expectedField, $actualField, 'Passed field does not match expected field.');
        $this->assertSame($expectedValue, $actualValues, 'Passed value does not match expected value.');
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Generate ids query with single id' => [
                'ids' => [123],
                'type' => 'test_type',
                'expected' => [
                    'query' => [
                        'edismax' => [
                            'query' => 'test_type.id:(123)',
                        ],
                    ],
                    'offset' => 0,
                    'limit' => 10,
                ],
            ],
            'Generate ids query with multiple ids' => [
                'ids' => [123, 345, 456],
                'type' => 'test_type',
                'expected' => [
                    'query' => [
                        'edismax' => [
                            'query' => 'test_type.id:(123 OR 345 OR 456)',
                        ],
                    ],
                    'offset' => 0,
                    'limit' => 10,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getQueryParamsProvider
     *
     * @param mixed $ids
     * @param mixed $type
     * @param mixed $expected
     */
    public function testGetQueryParams($ids, $type, $expected)
    {
        $this->idsQuery = new arSolrIdsQuery($ids);
        $this->idsQuery->setType($type);

        $actual = $this->idsQuery->getQueryParams();

        $this->assertSame($expected, $actual, 'Params passed do not match expected.');
    }

    public function getQueryParamsExceptionProvider(): array
    {
        return [
            'Generate ids query with missing type' => [
                'ids' => [123],
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field \'type\' is not set.',
            ],
            'Generate ids query with no ids' => [
                'ids' => null,
                'type' => 'test_type',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Ids are not set.',
            ],
            'Generate ids query with zero ids' => [
                'ids' => [],
                'type' => 'test_type',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Ids are not set.',
            ],
        ];
    }

    /**
     * @dataProvider getQueryParamsExceptionProvider
     *
     * @param mixed $ids
     * @param mixed $type
     * @param mixed $expectedException
     * @param mixed $expectedExceptionMessage
     */
    public function testGetQueryParamsException($ids, $type, $expectedException, $expectedExceptionMessage)
    {
        $this->idsQuery = new arSolrIdsQuery($ids);
        $this->idsQuery->setType($type);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->idsQuery->getQueryParams();
    }
}
