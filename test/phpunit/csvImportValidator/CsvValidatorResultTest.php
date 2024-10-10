<?php

/**
 * @internal
 *
 * @covers \CsvValidatorResult
 */
class CsvValidatorResultTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
    }

    public function testSetTitleOption()
    {
        $csvValidator = new CsvValidatorResult();
        $this->assertSame('', $csvValidator->getTitle());

        $csvValidator->setTitle('Title');
        $this->assertSame('Title', $csvValidator->getTitle());

        $csvValidator = new CsvValidatorResult('Title');
        $this->assertSame('Title', $csvValidator->getTitle());
    }

    public function testSetFilenameOption()
    {
        $csvValidator = new CsvValidatorResult();
        $this->assertSame('', $csvValidator->getFilename());

        $csvValidator->setFilename('Filename');
        $this->assertSame('Filename', $csvValidator->getFilename());

        $csvValidator = new CsvValidatorResult('Title', 'Filename');
        $this->assertSame('Filename', $csvValidator->getFilename());
    }

    public function testSetClassnameOption()
    {
        $csvValidator = new CsvValidatorResult();
        $this->assertSame('', $csvValidator->getClassname());

        $csvValidator->setClassname('Classname');
        $this->assertSame('Classname', $csvValidator->getClassname());

        $csvValidator = new CsvValidatorResult('Title', 'Filename', 'DisplayFilename', 'Classname');
        $this->assertSame('Classname', $csvValidator->getClassname());
    }

    public function testSetStatus()
    {
        $csvValidator = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        // INFO status by default.
        $this->assertSame(CsvValidatorResult::RESULT_INFO, $csvValidator->getStatus());

        $csvValidator->setStatusWarn();
        $this->assertSame(CsvValidatorResult::RESULT_WARN, $csvValidator->getStatus());

        $csvValidator->setStatusError();
        $this->assertSame(CsvValidatorResult::RESULT_ERROR, $csvValidator->getStatus());

        $csvValidator->setStatusWarn();
        // Should still be in error state.
        $this->assertSame(CsvValidatorResult::RESULT_ERROR, $csvValidator->getStatus());

        $csvValidator = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        $csvValidator->setStatus(CsvValidatorResult::RESULT_ERROR);
        $this->assertSame(CsvValidatorResult::RESULT_ERROR, $csvValidator->getStatus());
    }

    public function testAddResult()
    {
        $csvValidator = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        $csvValidator->addResult('Result 1');
        $csvValidator->addResult('Result 2');
        $this->assertSame(['Result 1', 'Result 2'], $csvValidator->getResults());
    }

    public function testAddDetail()
    {
        $csvValidator = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        $csvValidator->addDetail('Detail 1');
        $csvValidator->addDetail('Detail 2');
        $this->assertSame(['Detail 1', 'Detail 2'], $csvValidator->getDetails());
    }

    public function testFormatStatus()
    {
        $this->assertSame('info', CsvValidatorResult::formatStatus(CsvValidatorResult::RESULT_INFO));
        $this->assertSame('warning', CsvValidatorResult::formatStatus(CsvValidatorResult::RESULT_WARN));
        $this->assertSame('error', CsvValidatorResult::formatStatus(CsvValidatorResult::RESULT_ERROR));
    }

    public function testToArray()
    {
        $csvValidator = new CsvValidatorResult('Title', 'Filename', 'Classname', true);
        $result = $csvValidator->toArray();
        $this->assertSame(
            [
                'title' => 'Title',
                'status' => CsvValidatorResult::RESULT_INFO,
                'results' => [],
                'details' => [],
            ],
            $result
        );
    }
}
