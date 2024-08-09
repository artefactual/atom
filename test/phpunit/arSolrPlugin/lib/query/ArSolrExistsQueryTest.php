<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/lib/query/arSolrAbstractQuery.class.php';

require_once 'plugins/arSolrPlugin/lib/query/arSolrExistsQuery.class.php';

/**
 * @internal
 *
 * @covers \arSolrExistsQuery
 */
class ArSolrExistsQueryTest extends TestCase
{
    public function createSolrExistsQueryProvider(): array
    {
        return [
            'New arSolrExistsQuery with blank field' => [
                'field' => '',
                'result' => null,
            ],
            'New arSolrExistsQuery with null field' => [
                'field' => null,
                'result' => null,
            ],
            'New arSolrExistsQuery with string field' => [
                'field' => 'testField',
                'result' => 'testField',
            ],
        ];
    }

    /**
     * @dataProvider createSolrExistsQueryProvider
     *
     * @param mixed $field
     * @param mixed $expected
     */
    public function testCreateSolrExistsQuery($field, $expected)
    {
        $this->existsQuery = new arSolrExistsQuery($field);
        $this->assertTrue($this->existsQuery instanceof arSolrExistsQuery, 'Assert plugin object is arSolrExistsQuery.');
        $this->assertSame($expected, $this->existsQuery->getField(), 'Assert arSolrExistsQuery field is correct.');
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Generate exists query with specified type' => [
                'field' => 'testField',
                'type' => 'testType',
                'result' => [
                    'query' => [
                        'lucene' => [
                            'query' => 'testType.testField:*',
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
     * @param mixed $expected
     * @param mixed $field
     * @param mixed $type
     */
    public function testGetQueryParams($field, $type, $expected)
    {
        $this->existsQuery = new arSolrExistsQuery($field);
        $this->existsQuery->setType($type);

        $actual = $this->existsQuery->getQueryParams();

        $this->assertSame($expected, $actual, 'Params passed does not match expected.');
    }

    public function getQueryParamsExceptionProvider(): array
    {
        return [
            'Generate exists query with missing type' => [
                'field' => 'testField',
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field \'type\' is not set.',
            ],
            'Generate exists query with missing field' => [
                'field' => '',
                'type' => 'testType',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field is not set.',
            ],
            'Generate exists query with missing field and type' => [
                'field' => '',
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field is not set.',
            ],
        ];
    }

    /**
     * @dataProvider getQueryParamsExceptionProvider
     *
     * @param mixed $field
     * @param mixed $type
     * @param mixed $expectedException
     * @param mixed $expectedExceptionMessage
     */
    public function testGetQueryParamsException($field, $type, $expectedException, $expectedExceptionMessage)
    {
        $this->existsQuery = new arSolrExistsQuery($field);
        $this->existsQuery->setType($type);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->existsQuery->getQueryParams();
    }
}
