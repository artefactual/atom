<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/lib/query/arSolrAbstractQuery.class.php';

require_once 'plugins/arSolrPlugin/lib/query/arSolrTermQuery.class.php';

require_once 'plugins/arSolrPlugin/lib/query/arSolrMatchQuery.class.php';

/**
 * @internal
 *
 * @covers \arSolrMatchQuery
 */
class ArSolrMatchQueryTest extends TestCase
{
    public function testCreateEmptySolrMatchQuery()
    {
        $this->matchQuery = new arSolrMatchQuery();
        $this->assertTrue($this->matchQuery instanceof arSolrMatchQuery, 'Assert plugin object is arSolrMatchQuery.');
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Generate match query with specified type' => [
                'field' => 'test_field',
                'value' => 'testVal',
                'type' => 'test_type',
                'result' => [
                    'query' => [
                        'edismax' => [
                            'query' => 'test_type.test_field:testVal~',
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
     * @param mixed $field
     * @param mixed $value
     * @param mixed $type
     * @param mixed $result
     */
    public function testGetQueryParams($field, $value, $type, $result)
    {
        $this->matchQuery = new arSolrMatchQuery();
        $this->matchQuery->setFieldQuery($field, $value);
        $this->matchQuery->setType($type);

        $params = $this->matchQuery->getQueryParams();

        $this->assertSame($params, $result);
    }


    public function getQueryParamsUsingSetExceptionProvider(): array
    {
        return [
            'Generate match query with missing field \'field\'' => [
                'field' => null,
                'value' => null,
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Match field is not set.',
            ],
            'Generate match query with missing field value' => [
                'field' => 'tField',
                'value' => null,
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Match value is not set.',
            ],
            'Generate match query with missing type' => [
                'field' => 'tField',
                'value' => 'tValue',
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Match \'type\' is not set.',
            ],
        ];
    }
    /**
     * @dataProvider getQueryParamsUsingSetExceptionProvider
     *
     * @param mixed $field
     * @param mixed $value
     * @param mixed $type
     * @param mixed $expectedException
     * @param mixed $expectedExceptionMessage
     */
    public function testGetQueryParamsUsingSetException($field, $value, $type, $expectedException, $expectedExceptionMessage)
    {
        $this->matchQuery = new arSolrMatchQuery();
        $this->matchQuery->setFieldQuery($field, $value);
        $this->matchQuery->setType($type);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $params = $this->matchQuery->getQueryParams();
    }
}
