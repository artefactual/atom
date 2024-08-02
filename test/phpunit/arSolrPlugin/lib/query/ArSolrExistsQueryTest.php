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
                'field' => 'testString',
                'result' => 'testString',
            ],
        ];
    }

    /**
     * @dataProvider createSolrExistsQueryProvider
     *
     * @param mixed $field
     * @param mixed $result
     */
    public function testCreateSolrExistsQuery($field, $result)
    {
        $this->existsQuery = new arSolrExistsQuery($field);
        $this->assertTrue($this->existsQuery instanceof arSolrExistsQuery, 'Assert plugin object is arSolrExistsQuery.');
        $this->assertSame($this->existsQuery->getField(), $result, 'Assert arSolrExistsQuery field is correct.');
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Generate exists query with specified type' => [
                'field' => 'test_field',
                'type' => 'test_type',
                'result' => [
                    'query' => [
                        'lucene' => [
                            'query' => 'test_type.test_field:*',
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
     * @param mixed $result
     * @param mixed $field
     * @param mixed $type
     */
    public function testGetQueryParams($field, $type, $result)
    {
        $this->existsQuery = new arSolrExistsQuery($field);
        $this->existsQuery->setType($type);

        $params = $this->existsQuery->getQueryParams();

        $this->assertSame($params, $result);
    }

    public function getQueryParamsExceptionProvider(): array
    {
        return [
            'Generate exists query with missing type' => [
                'field' => 'test_field',
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field \'type\' is not set.',
            ],
            'Generate exists query with missing field and type' => [
                'field' => '',
                'type' => 'test_type',
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

        $params = $this->existsQuery->getQueryParams();
    }
}
