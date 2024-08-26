<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/lib/query/arSolrStringQuery.class.php';

/**
 * @internal
 *
 * @covers \arSolrStringQuery
 */
class ArSolrQueryTest extends TestCase
{
    public function createSolrQueryProvider()
    {
        return [
            'New arSolrStringQuery with default options' => [
                'searchQuery' => '*:*',
                'expected' => '*:*',
            ],
        ];
    }

    /**
     * @dataProvider createSolrQueryProvider
     *
     * @param mixed $searchQuery
     * @param mixed $expected
     */
    public function testCreateSolrQuery($searchQuery, $expected)
    {
        $this->query = new arSolrStringQuery($searchQuery);
        $actual = $this->query->getSearchQuery();

        $this->assertTrue($this->query instanceof arSolrStringQuery, 'Assert plugin object is arSolrStringQuery.');
        $this->assertSame($expected, $actual, 'Assert arSolrStringQuery search query is correct.');
    }

    public function testSetFields()
    {
        $this->query = new arSolrStringQuery('*:*');

        // Test setting the fields to empty array
        $this->query->setFields('');
        $this->assertEquals('', $this->query->getFields());

        // Test setting the fields to null
        $this->query->setFields(null);
        $this->assertEquals(null, $this->query->getFields());

        $fields = ['QubitInformationObject.i18n.en.title', 'QubitInformationObject.i18n.fr.title'];
        // Test setting the fields to array
        $this->query->setFields($fields);
        $this->assertEquals($fields, $this->query->getFields());
    }

    public function setDefaultOperatorProvider()
    {
        return [
            'Test set default operator with \'OR\'' => [
                'operator' => 'OR',
                'expected' => 'OR',
            ],
            'Test set default operator with \'AND\'' => [
                'operator' => 'AND',
                'expected' => 'AND',
            ],
        ];
    }

    /**
     * @dataProvider setDefaultOperatorProvider
     *
     * @param string $operator
     * @param string $expected
     */
    public function testSetDefaultOperator($operator, $expected)
    {
        $this->query = new arSolrStringQuery('*:*');
        $this->query->setDefaultOperator($operator);
        $actual = $this->query->getDefaultOperator();

        $this->assertSame($expected, $actual, 'Params passed does not match expected.');
    }

    public function testSetDefaultOperatorException()
    {
        $this->query = new arSolrStringQuery('*:*');

        $this->expectException('\Exception');
        $this->expectExceptionMessage('Invalid operator. AND and OR are the only acceptable operator types.');

        $this->query->setDefaultOperator('testOperator');
    }

    public function testSetAggregations()
    {
        $this->query = new arSolrStringQuery('*:*');

        // Test setting the aggrergations to empty array
        $this->query->setAggregations([]);
        $this->assertEquals([], $this->query->getAggregations());

        $aggregations = ['field' => 'QubitInformationObject.i18n.en.title', 'size' => '10'];
        // Test setting the aggregations to array
        $this->query->setAggregations($aggregations);
        $this->assertEquals($aggregations, $this->query->getAggregations());
    }

    public function getQueryParamsProvider(): array
    {
        $fields = ['testField', 'testField2'];

        return [
            'Test Solr MatchAll query with default options' => [
                'fields' => $fields,
                'type' => 'testType',
                'operator' => 'AND',
                'searchQuery' => 'searchString',
                'expected' => [
                    'query' => [
                        'edismax' => [
                            'q.op' => 'AND',
                            'stopwords' => 'true',
                            'query' => 'searchString~',
                            'qf' => 'testType.testField testType.testField2',
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
     * @param array  $fields
     * @param string $type
     * @param string $operator
     * @param string $searchQuery
     * @param mixed  $expected
     */
    public function testGetQueryParams($fields, $type, $operator, $searchQuery, $expected)
    {
        $this->query = new arSolrStringQuery($searchQuery);
        $this->query->setFields($fields);
        $this->query->setDefaultOperator($operator);
        $this->query->setType($type);

        $actual = $this->query->getQueryParams();

        $this->assertSame($expected, $actual, 'Params passed does not match expected.');
    }

    public function getQueryParamsAggsProvider(): array
    {
        $fields = ['testField', 'testField2'];
        $aggregations = ['field' => 'testField2', 'size' => '10'];

        return [
            'Test Solr MatchAll query with default options' => [
                'fields' => $fields,
                'operator' => 'AND',
                'type' => 'testType',
                'searchQuery' => '*:*',
                'aggregations' => $aggregations,
                'expected' => [
                    'query' => [
                        'edismax' => [
                            'q.op' => 'AND',
                            'stopwords' => 'true',
                            'query' => '*:*~',
                            'qf' => 'testType.testField testType.testField2',
                        ],
                    ],
                    'facet' => [
                        'categories' => [
                            'type' => 'terms',
                            'field' => 'testType.testField2',
                            'limit' => '10',
                        ],
                    ],
                    'offset' => 0,
                    'limit' => 10,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getQueryParamsAggsProvider
     *
     * @param array  $fields
     * @param string $operator
     * @param string $searchQuery
     * @param array  $aggregations
     * @param mixed  $expected
     * @param mixed  $type
     */
    public function testGetQueryParamsAggs($fields, $operator, $type, $searchQuery, $aggregations, $expected)
    {
        $this->query = new arSolrStringQuery($searchQuery);
        $this->query->setFields($fields);
        $this->query->setType($type);
        $this->query->setDefaultOperator($operator);
        $this->query->setAggregations($aggregations);

        $actual = $this->query->getQueryParams();

        $this->assertSame($expected, $actual, 'Params passed do not match expected.');
    }

    public function getQueryParamsAggsExceptionProvider(): array
    {
        return [
            'Test Solr MatchAll query with null fields' => [
                'fields' => null,
                'type' => 'testType',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Fields not set.',
            ],
            'Test Solr MatchAll query with null type' => [
                'fields' => 'testField',
                'type' => null,
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field \'type\' is not set.',
            ],
        ];
    }

    /**
     * @dataProvider getQueryParamsAggsExceptionProvider
     *
     * @param array $fields
     * @param mixed $type
     * @param mixed $expectedException
     * @param mixed $expectedExceptionMessage
     */
    public function testGetQueryParamsAggsException($fields, $type, $expectedException, $expectedExceptionMessage)
    {
        $this->query = new arSolrStringQuery('*:*');
        $this->query->setFields($fields);
        $this->query->setType($type);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->query->getQueryParams();
    }
}
