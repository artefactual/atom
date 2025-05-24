<?php

/**
 * @internal
 *
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
        $result = new CsvValidatorResult('Title', 'Filename', 'DisplayFilename', 'Classname');
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertSame(
            [
                'DisplayFilename' => [
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
        $result = new CsvValidatorResult('Title', 'Filename', 'DisplayFilename', 'Classname');
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertSame(
            '{"DisplayFilename":{"Classname":{"title":"Title","status":0,"results":[],"details":[]}}}',
            $resultCollection->toJson()
        );
    }

    public function testGetErrorCount()
    {
        $result = new CsvValidatorResult('Title', 'Filename', 'DisplayFilename', 'Classname');
        $result->setStatusError();
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertSame(1, $resultCollection->getErrorCount('Filename'));
        $this->assertSame(0, $resultCollection->getWarnCount('Filename'));
    }

    public function testGetWarnCount()
    {
        $result = new CsvValidatorResult('Title', 'Filename', 'DisplayFilename', 'Classname');
        $result->setStatusWarn();
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertSame(1, $resultCollection->getWarnCount('Filename'));
        $this->assertSame(0, $resultCollection->getErrorCount('Filename'));
    }

    public function testRenderResultsAsText()
    {
        $result = new CsvValidatorResult('Title', 'Filename', 'DisplayFilename', 'Classname');
        $result->setStatusWarn();
        $resultCollection = new CsvValidatorResultCollection();
        $resultCollection->appendResult($result);

        $this->assertIsString($resultCollection->renderResultsAsText(true));
    }
}
