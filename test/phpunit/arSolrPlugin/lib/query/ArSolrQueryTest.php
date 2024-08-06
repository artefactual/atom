<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/lib/query/arSolrQuery.class.php';

/**
 * @internal
 *
 * @covers \arSolrQuery
 */
class ArSolrQueryTest extends TestCase
{
    public function createSolrQueryProvider()
    {
        return [
            'New arSolrQuery with default options' => [
                'searchQuery' => '*:*',
                'result' => '*:*',
            ],
        ];
    }

    /**
     * @dataProvider createSolrQueryProvider
     */
    public function testCreateSolrQuery($searchQuery, $result)
    {
        $this->query = new arSolrQuery($searchQuery);

        $this->assertTrue($this->query instanceof arSolrQuery, 'Assert plugin object is arSolrQuery.');
        $this->assertSame($this->query->getSearchQuery(), $result, 'Assert arSolrQuery search query is correct.');
    }

    public function testSetFields()
    {
        $this->query = new arSolrQuery('*:*');

        // Test setting the fields to empty array
        $this->query->setFields('');
        $this->assertEquals('', $this->query->getFields());

        // Test setting the fields to null
        $this->query->setFields(NULL);
        $this->assertEquals(NULL, $this->query->getFields());

        $fields = ['QubitInformationObject.i18n.en.title', 'QubitInformationObject.i18n.fr.title'];
        // Test setting the fields to array
        $this->query->setFields($fields);
        $this->assertEquals($fields, $this->query->getFields());
    }

    public function testSetDefaultOperator()
    {
        $this->query = new arSolrQuery('*:*');

        // Test setting the default operator to 'OR'
        $this->query->setDefaultOperator('OR');
        $this->assertEquals('OR', $this->query->getDefaultOperator());

        // Test setting the default operator to 'AND'
        $this->query->setDefaultOperator('AND');
        $this->assertEquals('AND', $this->query->getDefaultOperator());
    }

    public function testSetAggregations()
    {
        $this->query = new arSolrQuery('*:*');

        // Test setting the aggrergations to empty array
        $this->query->setAggregations([]);
        $this->assertEquals([], $this->query->getAggregations());

        // Test setting the aggregations to NULL
        $this->query->setAggregations(NULL);
        $this->assertEquals(NULL, $this->query->getAggregations());

        $aggregations = ['field' => 'QubitInformationObject.i18n.en.title', 'size' => '10'];
        // Test setting the aggregations to array
        $this->query->setAggregations($aggregations);
        $this->assertEquals($aggregations, $this->query->getAggregations());
    }

    public function getQueryParamsProvider(): array
    {
        $fields = ['QubitInformationObject.i18n.en.title', 'QubitInformationObject.i18n.fr.title'];
        return [
            'Test Solr MatchAll query with default options' => [
                'fields' => $fields,
                'operator' => 'AND',
                'searchQuery' => '*:*',
                'result' => [
                    'query' => [
                        'edismax' => [
                            'q.op' => 'AND',
                            'stopwords' => 'true',
                            'query' => '*:*~',
                            'qf' => implode(' ', $fields),
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
     * @param array $fields
     * @param string $operator
     * @param string $searchQuery
     * @param mixed $result
     */
    public function testGetQueryParams($fields, $operator, $searchQuery, $result)
    {
        $this->query = new arSolrQuery($searchQuery);
        $this->query->setFields($fields);
        $this->query->setDefaultOperator($operator);

        $params = $this->query->getQueryParams();

        $this->assertSame($params, $result);
    }

    public function getQueryParamsAggsProvider(): array
    {
        $fields = ['QubitInformationObject.i18n.en.title', 'QubitInformationObject.i18n.fr.title'];
        $aggregations = ['field' => 'QubitInformationObject.i18n.en.title', 'size' => '10'];
        return [
            'Test Solr MatchAll query with default options' => [
                'fields' => $fields,
                'operator' => 'AND',
                'searchQuery' => '*:*',
                'aggregations' => $aggregations,
                'result' => [
                    'query' => [
                        'edismax' => [
                            'q.op' => 'AND',
                            'stopwords' => 'true',
                            'query' => '*:*~',
                            'qf' => implode(' ', $fields),
                        ],
                    ],
                    'facet' => [
                        'categories' => [
                            'type' => 'terms',
                            'field' => $aggregations['field'],
                            'limit' => $aggregations['size'],
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
     * @param array $fields
     * @param string $operator
     * @param string $searchQuery
     * @param array $aggregations
     * @param mixed $result
     */
    public function testGetQueryParamsAggs($fields, $operator, $searchQuery, $aggregations, $result)
    {
        $this->query = new arSolrQuery($searchQuery);
        $this->query->setFields($fields);
        $this->query->setDefaultOperator($operator);
        $this->query->setAggregations($aggregations);

        $params = $this->query->getQueryParams();

        $this->assertSame($params, $result);
    }
}
