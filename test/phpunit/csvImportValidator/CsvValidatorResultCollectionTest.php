<?php

/**
 * @internal
 * @covers \CsvValidatorResultCollection
 */
class CsvValidatorResultCollectionTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
    }

    public function testToArray()
    {
        $result = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertSame(
            [
                'Filename' => [
                    'Classname' => [
                        'title' => 'Title',
                        'status' => 0,
                        'results' => [],
                        'details' => [],
                    ],
                ],
            ],
            $resultCollection->toArray()
        );
    }

    public function testToJson()
    {
        $result = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertSame(
            '{"Filename":{"Classname":{"title":"Title","status":0,"results":[],"details":[]}}}',
            $resultCollection->toJson()
        );
    }

    public function testGetErrorCount()
    {
        $result = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        $result->setStatusError();
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertSame(1, $resultCollection->getErrorCount());
        $this->assertSame(0, $resultCollection->getWarnCount());
    }

    public function testGetWarnCount()
    {
        $result = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        $result->setStatusWarn();
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertSame(1, $resultCollection->getWarnCount());
        $this->assertSame(0, $resultCollection->getErrorCount());
    }

    public function testRenderResultsAsText()
    {
        $result = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        $result->setStatusWarn();
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertIsString($resultCollection->renderResultsAsText($resultCollection));
    }
}
