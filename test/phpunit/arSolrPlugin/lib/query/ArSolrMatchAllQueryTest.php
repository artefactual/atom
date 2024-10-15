<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/lib/client/arSolrMatchAllQuery.class.php';

/**
 * @internal
 *
 * @covers \arSolrMatchAllQuery
 */
class ArSolrMatchAllQueryTest extends TestCase
{
    public function createSolrMatchAllQueryProvider()
    {
        return [
            'New arSolrMatchAllQuery with default options' => [
                'expected' => [
                    'query' => [
                        'lucene' => [
                            'q.op' => 'AND',
                            'stopwords' => 'true',
                            'query' => '*:*',
                        ],
                    ],
                    'offset' => 0,
                    'limit' => 10,
                ],
            ],
        ];
    }

    /**
     * @dataProvider createSolrMatchAllQueryProvider
     *
     * @param array $expected
     */
    public function testCreateSolrMatchAllQuery($expected)
    {
        $this->matchAllQuery = new arSolrMatchAllQuery();
        $actual = $this->matchAllQuery->getQueryParams();

        $this->assertTrue($this->matchAllQuery instanceof arSolrMatchAllQuery, 'Assert plugin object is arSolrMatchAllQuery.');
        $this->assertSame($expected, $actual, 'Params passed do not match expected.');
    }

    public function testSetDefaultOperatorException()
    {
        $this->matchAllQuery = new arSolrMatchAllQuery();

        $this->expectException('\Exception');
        $this->expectExceptionMessage('Invalid operator. AND and OR are the only acceptable operator types.');

        $this->matchAllQuery->setDefaultOperator('testOperator');
    }

    public function setDefaultOperatorProvider()
    {
        return [
            'Test setting the default operator to \'OR\'' => [
                'operator' => 'OR',
                'expected' => 'OR',
            ],
            'Test setting the default operator to \'AND\'' => [
                'operator' => 'AND',
                'expected' => 'AND',
            ],
        ];
    }

    /**
     * @dataProvider setDefaultOperatorProvider
     *
     * @param mixed  $expected
     * @param string $operator
     */
    public function testSetDefaultOperator($expected, $operator)
    {
        $this->matchAllQuery = new arSolrMatchAllQuery();
        $this->matchAllQuery->setDefaultOperator($operator);

        $actual = $this->matchAllQuery->getDefaultOperator();

        $this->assertSame($expected, $actual, 'Params passed do not match expected.');
    }

    public function testSetSearchQuery()
    {
        $this->matchAllQuery = new arSolrMatchAllQuery();

        // Test setting the search query to blank query
        $this->matchAllQuery->setSearchQuery('');
        $this->assertEquals('', $this->matchAllQuery->getSearchQuery());

        // Test setting the search query to default
        $this->matchAllQuery->setSearchQuery('*:*');
        $this->assertEquals('*:*', $this->matchAllQuery->getSearchQuery());
    }
}
