<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/lib/client/arSolrAbstractQuery.class.php';

require_once 'plugins/arSolrPlugin/lib/client/arSolrTermsQuery.class.php';

/**
 * @internal
 *
 * @covers \arSolrTermsQuery
 */
class ArSolrTermsQueryTest extends TestCase
{
    public function createSolrTermsQueryProvider(): array
    {
        return [
            'New arSolrTermsQuery with no term' => [
                'term' => null,
                'expectedField' => null,
                'expectedValue' => null,
            ],
            'New arSolrTermsQuery with empty term' => [
                'term' => ['' => []],
                'expectedField' => '',
                'expectedValue' => [],
            ],
            'New arSolrTermsQuery with one string term' => [
                'term' => ['tField' => ['tValue']],
                'expectedField' => 'tField',
                'expectedValue' => ['tValue'],
            ],
            'New arSolrTermsQuery with multiple string terms' => [
                'term' => ['tField' => ['tValue', 'tValue2']],
                'expectedField' => 'tField',
                'expectedValue' => ['tValue', 'tValue2'],
            ],
        ];
    }

    public function testCreateEmptySolrTermsQuery()
    {
        $this->termsQuery = new arSolrTermsQuery();
        $this->assertTrue($this->termsQuery instanceof arSolrTermsQuery, 'Assert plugin object is arSolrTermsQuery.');
    }

    /**
     * @dataProvider createSolrTermsQueryProvider
     *
     * @param mixed $term
     * @param mixed $expectedField
     * @param mixed $expectedValue
     */
    public function testCreateSolrTermsQuery($term, $expectedField, $expectedValue)
    {
        $this->termsQuery = new arSolrTermsQuery($term);
        $actualField = $this->termsQuery->getTermField();
        $actualValues = $this->termsQuery->getTermValues();

        $this->assertTrue($this->termsQuery instanceof arSolrTermsQuery, 'Assert plugin object is arSolrTermsQuery.');
        $this->assertSame($expectedField, $actualField, 'Passed field does not match expected field.');
        $this->assertSame($expectedValue, $actualValues, 'Passed value does not match expected value.');
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Generate terms query with specified type' => [
                'term' => ['test_field' => ['testVal']],
                'type' => 'test_type',
                'expected' => [
                    'query' => [
                        'edismax' => [
                            'query' => 'test_type.test_field:(testVal)',
                        ],
                    ],
                    'offset' => 0,
                    'limit' => 10,
                ],
            ],
            'Generate terms query with multiple values' => [
                'term' => ['test_field' => ['testVal', 'testVal2']],
                'type' => 'test_type',
                'expected' => [
                    'query' => [
                        'edismax' => [
                            'query' => 'test_type.test_field:(testVal OR testVal2)',
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
        $this->termsQuery = new arSolrTermsQuery($term);
        $this->termsQuery->setType($type);

        $actual = $this->termsQuery->getQueryParams();

        $this->assertSame($expected, $actual, 'Params passed do not match expected.');
    }

    public function getQueryParamsExceptionProvider(): array
    {
        return [
            'Generate terms query with missing type' => [
                'term' => ['test_field' => ['testVal']],
                'type' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field \'type\' is not set.',
            ],
            'Generate terms query with missing term' => [
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
        $this->termsQuery = new arSolrTermsQuery($term);
        $this->termsQuery->setType($type);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->termsQuery->getQueryParams();
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
                'expectedExceptionMessage' => 'Term values are not set.',
            ],
            'Generate term query with missing missing type' => [
                'termField' => 'tField',
                'termValue' => ['tValue'],
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
        $this->termsQuery = new arSolrTermsQuery();
        $this->termsQuery->setTerms($termField, $termValue);
        $this->termsQuery->setType($type);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->termsQuery->getQueryParams();
    }
}
