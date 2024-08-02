<?php

use PHPUnit\Framework\TestCase;

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
                'operator' => 'AND',
                'searchQuery' => '*:*',
                'result' => [
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
     */
    public function testCreateSolrMatchAllQuery()
    {
        $this->matchAllQuery = new arSolrMatchAllQuery();

        $this->assertTrue($this->matchAllQuery instanceof arSolrMatchAllQuery, 'Assert plugin object is arSolrMatchAllQuery.');
    }

    public function testSetDefaultOperator()
    {
        $this->matchAllQuery = new arSolrMatchAllQuery();

        // Test setting the default operator to 'OR'
        $this->matchAllQuery->setDefaultOperator('OR');
        $this->assertEquals('OR', $this->matchAllQuery->getDefaultOperator());

        // Test setting the default operator to NULL
        $this->matchAllQuery->setDefaultOperator(NULL);
        $this->assertEquals(NULL, $this->matchAllQuery->getDefaultOperator());

        // Test setting the default operator to 'AND'
        $this->matchAllQuery->setDefaultOperator('AND');
        $this->assertEquals('AND', $this->matchAllQuery->getDefaultOperator());
    }

    public function testSetSearchQuery()
    {
        $this->matchAllQuery = new arSolrMatchAllQuery();

        // Test setting the search query to blank query
        $this->matchAllQuery->setSearchQuery('');
        $this->assertEquals('', $this->matchAllQuery->getSearchQuery());

        // Test setting the search query to NULL query
        $this->matchAllQuery->setSearchQuery(NULL);
        $this->assertEquals(NULL, $this->matchAllQuery->getSearchQuery());

        // Test setting the search query to default
        $this->matchAllQuery->setSearchQuery('*:*');
        $this->assertEquals('*:*', $this->matchAllQuery->getSearchQuery());
    }

    public function getQueryParamsProvider(): array
    {
        return [
            'Test Solr MatchAll query with default options' => [
                'result' => [
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
     * @dataProvider getQueryParamsProvider
     *
     * @param mixed $result
     */
    public function testGetQueryParams($result)
    {
        $this->matchAllQuery = new arSolrMatchAllQuery();

        $params = $this->matchAllQuery->getQueryParams();

        $this->assertSame($params, $result);
    }
}
