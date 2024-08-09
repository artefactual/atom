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
                'range' => ['lte' => '1900-01-01'],
                'expected' => [
                    ['dates.startDate'],
                    ['lte' => '1900-01-01'],
                ],
            ],
            'New arSolrRangeQuery with default field and lt range' => [
                'field' => ['dates.startDate'],
                'range' => ['lt' => '1900'],
                'expected' => [
                    ['dates.startDate'],
                    ['lt' => '1900'],
                ],
            ],
            'New arSolrRangeQuery with empty field and default gt range' => [
                'field' => [],
                'range' => ['gt' => '1900-01-01'],
                'expected' => [
                    [],
                    ['gt' => '1900-01-01'],
                ],
            ],
            'New arSolrRangeQuery with NULL field and default range' => [
                'field' => null,
                'range' => ['gte' => '1900'],
                'expected' => [
                    null,
                    ['gte' => '1900'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider createSolrRangeQueryProvider
     *
     * @param string $field
     * @param array  $range
     * @param array  $expected
     */
    public function testCreateSolrRangeQuery($field, $range, $expected)
    {
        $this->rangeQuery = new arSolrRangeQuery($field, $range);

        $this->assertTrue($this->rangeQuery instanceof arSolrRangeQuery, 'Assert plugin object is arSolrRangeQuery.');
        $this->assertSame($this->rangeQuery->getField(), $expected[0], 'Assert arSolrRangeQuery field is correct.');
        $this->assertSame($this->rangeQuery->getRange(), $expected[1], 'Assert arSolrRangeQuery range is correct.');
    }

    public function createSolrRangeQueryExceptionProvider()
    {
        return [
            'New arSolrRangeQuery with empty range' => [
                'range' => [],
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Invalid range date format. Range date must be formatted as YYYY-MM-DD or YYYY.',
            ],
            'New arSolrRangeQuery with string range' => [
                'range' => 'test_range',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Invalid range date format. Range date must be formatted as YYYY-MM-DD or YYYY.',
            ],
            'New arSolrRangeQuery with NULL range' => [
                'range' => null,
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Invalid range date format. Range date must be formatted as YYYY-MM-DD or YYYY.',
            ],
        ];
    }

    /**
     * @dataProvider createSolrRangeQueryExceptionProvider
     *
     * @param array $range
     * @param mixed $expectedException
     * @param mixed $expectedExceptionMessage
     */
    public function testCreateSolrRangeQueryException($range, $expectedException, $expectedExceptionMessage)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->rangeQuery = new arSolrRangeQuery('testField', $range);
    }

    public function testSetComputedRange()
    {
        $this->rangeQuery = new arSolrRangeQuery('test_field', ['lte' => '2000', 'gte' => '2000']);
        $this->rangeQuery->setRange(['lte' => '2000', 'gte' => '2000']);
        $this->rangeQuery->setType('test_type');

        $this->rangeQuery->getQueryParams();

        $this->assertSame('[2000 TO 2000]', $this->rangeQuery->getComputedRange(), 'Params passed does not match expected.');
    }

    public function testSetTypeException()
    {
        $this->rangeQuery = new arSolrRangeQuery('test_field', ['lte' => '2000', 'gte' => '2000']);

        $this->assertNull($this->rangeQuery->setType(''));
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Test range query with default options' => [
                'field' => 'testField',
                'range' => ['lte' => '1990', 'gte' => '2000'],
                'type' => 'testType',
                'expected' => [
                    'query' => [
                        'lucene' => [
                            'query' => 'testType.testField:[1990 TO 2000]',
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
     * @param array  $expected
     */
    public function testGetQueryParams($field, $range, $type, $expected)
    {
        $this->rangeQuery = new arSolrRangeQuery($field, $range);
        $this->rangeQuery->setRange($range);
        $this->rangeQuery->setField($field);
        $this->rangeQuery->setType($type);

        $actual = $this->rangeQuery->getQueryParams();

        $this->assertSame($expected, $actual, 'Params passed do not match expected.');
    }

    public function getQueryParamsExceptionProvider(): array
    {
        return [
            'Test range query with NULL field' => [
                'type' => 'test_type',
                'field' => null,
                'range' => ['lte' => '2000', 'gte' => '2000'],
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Field is not set.',
            ],
            'Test range query with empty type field' => [
                'type' => '',
                'field' => 'test_field',
                'range' => ['lte' => '2000', 'gte' => '2000'],
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
