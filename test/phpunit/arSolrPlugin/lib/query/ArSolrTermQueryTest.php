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
                'expectedField' => null,
                'expectedValue' => null,
            ],
            'New arSolrTermQuery with empty term' => [
                'term' => ['' => ''],
                'expectedField' => '',
                'expectedValue' => '',
            ],
            'New arSolrTermQuery with string array term' => [
                'term' => ['tField' => 'tValue'],
                'expectedField' => 'tField',
                'expectedValue' => 'tValue',
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
     * @param mixed $expectedField
     * @param mixed $expectedValue
     */
    public function testCreateSolrTermQuery($term, $expectedField, $expectedValue)
    {
        $this->termQuery = new arSolrTermQuery($term);
        $actualField = $this->termQuery->getTermField();
        $actualValue = $this->termQuery->getTermValue();

        $this->assertTrue($this->termQuery instanceof arSolrTermQuery, 'Assert plugin object is arSolrTermQuery.');
        $this->assertSame($expectedField, $actualField, 'Passed field does not match expected field.');
        $this->assertSame($expectedValue, $actualValue, 'Passed value does not match expected value.');
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Generate term query with specified type' => [
                'term' => ['test_field' => 'testVal'],
                'type' => 'test_type',
                'expected' => [
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
     * @param mixed $expected
     */
    public function testGetQueryParams($term, $type, $expected)
    {
        $this->termQuery = new arSolrTermQuery($term);
        $this->termQuery->setType($type);

        $actual = $this->termQuery->getQueryParams();

        $this->assertSame($expected, $actual, 'Params passed do not match expected.');
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

        $this->termQuery->getQueryParams();
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

        $this->termQuery->getQueryParams();
    }
}
