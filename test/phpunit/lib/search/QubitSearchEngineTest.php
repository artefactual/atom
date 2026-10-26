<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 *
 * The search engine contract (QubitSearchEngine), checked against the
 * configured engine and the data of the test installation. Every engine
 * must pass it; nothing in the index is changed.
 *
 * @package AccesstoMemory
 * @subpackage search
 * @author Johan Pieterse <johan@theahg.co.za>
 */

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class QubitSearchEngineTest extends TestCase
{
    protected $engine;

    protected function setUp(): void
    {
        $this->engine = QubitSearch::getInstance();
    }

    public function testTheEngineIsAQubitSearchEngine()
    {
        $this->assertInstanceOf(QubitSearchEngine::class, $this->engine);
    }

    public function testGetVersionAnswersAVersionNumber()
    {
        $this->assertMatchesRegularExpression('/^\d+\.\d+/', $this->engine->getVersion());
    }

    public function testCountCountsAnIndexAndAQueryNarrowsIt()
    {
        $this->assertGreaterThan(0, $this->engine->count('QubitTerm'));
        $this->assertSame(0, $this->engine->count('QubitTerm', $this->noMatch()));
    }

    public function testSearchFindsADocumentThatGetDocumentReturns()
    {
        $hit = $this->engine->search('QubitTerm', new \Elastica\Query(['size' => 1]))->getResults()[0];
        $doc = $this->engine->getDocument('QubitTerm', $hit->getId());

        $this->assertIsArray($doc);
        $this->assertSame($hit->getSource()['slug'], $doc['slug']);
    }

    public function testGetDocumentOfAnIdThatIsNotIndexedIsNull()
    {
        $this->assertNull($this->engine->getDocument('QubitTerm', PHP_INT_MAX));
    }

    public function testMultiSearchAnswersEachSearchInOrder()
    {
        $resultSets = $this->engine->multiSearch([
            ['QubitTerm', new \Elastica\Query(['size' => 0])],
            ['QubitTerm', new \Elastica\Query(['query' => $this->noMatch()->toArray(), 'size' => 0])],
        ]);

        $this->assertCount(2, $resultSets);
        $this->assertSame($this->engine->count('QubitTerm'), $resultSets[0]->getTotalHits());
        $this->assertSame(0, $resultSets[1]->getTotalHits());
    }

    public function testScrollIdsReadsEveryMatchPastOnePage()
    {
        // A page of 10 makes the scroll take several requests.
        $ids = $this->engine->scrollIds('QubitTerm', new \Elastica\Query(['size' => 10]));

        $this->assertCount($this->engine->count('QubitTerm'), array_unique($ids));
    }

    public function testUpdateByQueryReportsTimeAndFailures()
    {
        $response = $this->engine->updateByQuery(
            'QubitTerm',
            $this->noMatch(),
            'ctx._source.slug = ctx._source.slug',
            ['conflicts' => 'proceed']
        );

        $this->assertArrayHasKey('took', $response);
        $this->assertEmpty($response['failures']);
    }

    public function testBulkPartialUpdateSetsFields()
    {
        // Write back a field's current value, so the index is left as it was.
        $hit = $this->engine->search('QubitTerm', new \Elastica\Query(['size' => 1]))->getResults()[0];
        $slug = $hit->getSource()['slug'];

        $this->engine->bulkPartialUpdate('QubitTerm', [$hit->getId() => ['slug' => $slug]]);

        $this->assertSame($slug, $this->engine->getDocument('QubitTerm', $hit->getId())['slug']);
    }

    private function noMatch()
    {
        return new \Elastica\Query\Term(['slug' => 'no-such-slug-'.uniqid()]);
    }
}
