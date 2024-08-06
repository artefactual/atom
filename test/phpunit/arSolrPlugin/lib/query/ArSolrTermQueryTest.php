<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/lib/query/arSolrAbstractQuery.class.php';

require_once 'plugins/arSolrPlugin/lib/query/arSolrTermQuery.class.php';

/**
 * @internal
 *
 * @covers \arSolrTermQuery
 */
class ArSolrTermQueryTest extends TestCase
{
    public function createSolrTermQueryProvider(): array
    {
        return [
            'New arSolrTermQuery with no term' => [
                'term' => null,
                'resultField' => null,
                'resultValue' => null,
            ],
            'New arSolrTermQuery with empty term' => [
                'term' => ['' => ''],
                'resultField' => '',
                'resultValue' => '',
            ],
            'New arSolrTermQuery with string array term' => [
                'term' => ['tField' => 'tValue'],
                'resultField' => 'tField',
                'resultValue' => 'tValue',
            ],
        ];
    }

    public function testCreateEmptySolrTermQuery()
    {
        $this->termQuery = new arSolrTermQuery();
        $this->assertTrue($this->termQuery instanceof arSolrTermQuery, 'Assert plugin object is arSolrTermQuery.');
    }

    /**
     * @dataProvider createSolrTermQueryProvider
     *
     * @param mixed $term
     * @param mixed $resultField
     * @param mixed $resultValue
     */
    public function testCreateSolrTermQuery($term, $resultField, $resultValue)
    {
        $this->termQuery = new arSolrTermQuery($term);
        $this->assertTrue($this->termQuery instanceof arSolrTermQuery, 'Assert plugin object is arSolrTermQuery.');
        $this->assertSame($this->termQuery->getTermField(), $resultField, 'Assert arSolrTermQuery field is correct.');
        $this->assertSame($this->termQuery->getTermValue(), $resultValue, 'Assert arSolrTermQuery value is correct.');
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Generate term query with specified type' => [
                'term' => ['test_field' => 'testVal'],
                'type' => 'test_type',
                'result' => [
                    'query' => [
                        'edismax' => [
                            'query' => 'test_type.test_field:testVal',
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
     * @param mixed $term
     * @param mixed $type
     * @param mixed $result
     */
    public function testGetQueryParams($term, $type, $result)
    {
        $this->termQuery = new arSolrTermQuery($term);
        $this->termQuery->setType($type);

        $params = $this->termQuery->getQueryParams();

        $this->assertSame($params, $result);
    }

    public function getQueryParamsExceptionProvider(): array
    {
        return [
            'Generate term query with missing type' => [
                'term' => ['test_field' => 'testVal'],
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field \'type\' is not set.',
            ],
            'Generate term query with missing term' => [
                'term' => [],
                'type' => 'test_type',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Term field is not set.',
            ],
        ];
    }

    /**
     * @dataProvider getQueryParamsExceptionProvider
     *
     * @param mixed $term
     * @param mixed $type
     * @param mixed $expectedException
     * @param mixed $expectedExceptionMessage
     */
    public function testGetQueryParamsException($term, $type, $expectedException, $expectedExceptionMessage)
    {
        $this->termQuery = new arSolrTermQuery($term);
        $this->termQuery->setType($type);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $params = $this->termQuery->getQueryParams();
    }

    public function getQueryParamsUsingSetExceptionProvider(): array
    {
        return [
            'Generate term query with missing term field' => [
                'termField' => null,
                'termValue' => null,
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Term field is not set.',
            ],
            'Generate term query with missing term value' => [
                'termField' => 'tField',
                'termValue' => null,
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Term value is not set.',
            ],
            'Generate term query with missing missing type' => [
                'termField' => 'tField',
                'termValue' => 'tValue',
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field \'type\' is not set.',
            ],
        ];
    }

    /**
     * @dataProvider getQueryParamsUsingSetExceptionProvider
     *
     * @param mixed $termField
     * @param mixed $termValue
     * @param mixed $type
     * @param mixed $expectedException
     * @param mixed $expectedExceptionMessage
     */
    public function testGetQueryParamsUsingSetException($termField, $termValue, $type, $expectedException, $expectedExceptionMessage)
    {
        $this->termQuery = new arSolrTermQuery();
        $this->termQuery->setTerm($termField, $termValue);
        $this->termQuery->setType($type);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $params = $this->termQuery->getQueryParams();
    }
}
