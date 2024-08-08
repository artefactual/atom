<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/lib/query/arSolrRangeQuery.class.php';

/**
 * @internal
 *
 * @covers \arSolrRangeQuery
 */
class ArSolrRangeQueryTest extends TestCase
{
    public function createSolrRangeQueryProvider()
    {
        return [
            'New arSolrRangeQuery with default field and lte range' => [
                'field' => ['dates.startDate'],
                'range' => ['lte' => '1900'],
                'result' => [
                    ['dates.startDate'],
                    ['lte' => '1900'],
                ],
            ],
            'New arSolrRangeQuery with default field and lt range' => [
                'field' => ['dates.startDate'],
                'range' => ['lt' => '1900'],
                'result' => [
                    ['dates.startDate'],
                    ['lt' => '1900'],
                ],
            ],
            'New arSolrRangeQuery with default field and gte range' => [
                'field' => ['dates.startDate'],
                'range' => ['gte' => '1900'],
                'result' => [
                    ['dates.startDate'],
                    ['gte' => '1900'],
                ],
            ],
            'New arSolrRangeQuery with default field and gt range' => [
                'field' => ['dates.startDate'],
                'range' => ['gt' => '1900'],
                'result' => [
                    ['dates.startDate'],
                    ['gt' => '1900'],
                ],
            ],
            'New arSolrRangeQuery with empty field and range array' => [
                'field' => [],
                'range' => [],
                'result' => [
                    [],
                    [],
                ],
            ],
            'New arSolrRangeQuery with NULL field and range' => [
                'field' => null,
                'range' => [null => null],
                'result' => [
                    null,
                    [null => null],
                ],
            ],
        ];
    }

    /**
     * @dataProvider createSolrRangeQueryProvider
     *
     * @param string $field
     * @param array  $range
     * @param string $type
     * @param mixed  $result
     */
    public function testCreateSolrRangeQuery($field, $range, $result)
    {
        $this->rangeQuery = new arSolrRangeQuery($field, $range);

        $this->assertTrue($this->rangeQuery instanceof arSolrRangeQuery, 'Assert plugin object is arSolrRangeQuery.');
        $this->assertSame($this->rangeQuery->getField(), $result[0], 'Assert arSolrRangeQuery field is correct.');
        $this->assertSame($this->rangeQuery->getRange(), $result[1], 'Assert arSolrRangeQuery range is correct.');
    }

    public function testSetComputedRange()
    {
        $this->rangeQuery = new arSolrRangeQuery('test_field', ['lte' => 'test_date', 'gte' => 'test_date']);
        $this->rangeQuery->setRange(['lte' => 'test_date', 'gte' => 'test_date']);
        $this->rangeQuery->setType('test_type');

        $this->rangeQuery->getQueryParams();

        $this->assertSame('[test_date TO test_date]', $this->rangeQuery->getComputedRange());
    }

    public function testSetTypeException()
    {
        $this->rangeQuery = new arSolrRangeQuery('test_field', ['lte' => 'test_date', 'gte' => 'test_date']);

        $this->assertNull($this->rangeQuery->setType(''));
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Test Solr Range query with default options' => [
                'field' => 'dates.startDate',
                'range' => ['lte' => '2023-12-31', 'gte' => '2023-01-01'],
                'type' => 'testType',
                'result' => [
                    'query' => [
                        'lucene' => [
                            'query' => 'testType.dates.startDate:[2023-12-31 TO 2023-01-01]',
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
     * @param string $field
     * @param array  $range
     * @param string $type
     * @param array  $result
     */
    public function testGetQueryParams($field, $range, $type, $result)
    {
        $this->rangeQuery = new arSolrRangeQuery($field, $range);
        $this->rangeQuery->setRange($range);
        $this->rangeQuery->setField($field);
        $this->rangeQuery->setType($type);

        $params = $this->rangeQuery->getQueryParams();

        $this->assertSame($params, $result);
    }

    public function getQueryParamsExceptionProvider(): array
    {
        return [
            'Test range query with NULL field' => [
                'type' => 'test_type',
                'field' => null,
                'range' => ['lte' => 'test_date', 'gte' => 'test_date'],
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field is not set.',
            ],
            'Test range query with empty type field' => [
                'type' => '',
                'field' => 'test_field',
                'range' => ['lte' => 'test_date', 'gte' => 'test_date'],
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field \'type\' is not set.',
            ],
        ];
    }

    /**
     * @dataProvider getQueryParamsExceptionProvider
     *
     * @param string $type
     * @param array  $field
     * @param mixed  $range
     * @param mixed  $expectedException
     * @param mixed  $expectedExceptionMessage
     */
    public function testGetQueryParamsException($type, $field, $range, $expectedException, $expectedExceptionMessage)
    {
        $this->rangeQuery = new arSolrRangeQuery($field, $range);
        $this->rangeQuery->setType($type);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->rangeQuery->getQueryParams();
    }
}
