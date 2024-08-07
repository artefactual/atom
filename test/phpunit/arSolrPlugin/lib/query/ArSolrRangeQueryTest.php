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
            'New arSolrRangeQuery with default field and gt/gte range' => [
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
            'New arSolrRangeQuery with null field and lte range' => [
                'field' => null,
                'range' => ['lte' => '1900'],
                'result' => [
                    null,
                    ['lte' => '1900'],
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
     * @param mixed $field
     * @param mixed $range
     * @param mixed $result
     */
    public function testCreateSolrRangeQuery($field, $range, $result)
    {
        $this->rangeQuery = new arSolrRangeQuery($field, $range);

        $this->assertTrue($this->rangeQuery instanceof arSolrRangeQuery, 'Assert plugin object is arSolrRangeQuery.');
        $this->assertSame($this->rangeQuery->getField(), $result[0], 'Assert arSolrRangeQuery field is correct.');
        $this->assertSame($this->rangeQuery->getRange(), $result[1], 'Assert arSolrRangeQuery range is correct.');
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Test Solr Range query with default options' => [
                'field' => 'dates.startDate',
                'range' => ['lte' => '2023-12-31', 'gte' => '2023-01-01'],
                'result' => [
                    'query' => [
                        'lucene' => [
                            'query' => 'dates.startDate:[2023-12-31 TO 2023-01-01]',
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
     * @param array $field
     * @param mixed $range
     * @param mixed $result
     */
    public function testGetQueryParams($field, $range, $result)
    {
        $this->rangeQuery = new arSolrRangeQuery(['dates.startDate'], ['lte' => '2023-12-31', 'gte' => '2023-01-01']);
        $this->rangeQuery->setRange($range);
        $this->rangeQuery->setField($field);

        $params = $this->rangeQuery->getQueryParams();

        $this->assertSame($params, $result);
    }
}
