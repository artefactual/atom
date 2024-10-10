<?php

class CsvMockValidator extends CsvBaseValidator {}

/**
 * @internal
 *
 * @covers \CsvBaseValidator
 */
class CsvBaseValidatorTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
    }

    public function testNewValidatorObject()
    {
        $csvValidator = new CsvMockValidator([]);

        $this->assertIsObject($csvValidator);
    }

    public function testSetTitle()
    {
        $csvValidator = new CsvMockValidator([]);
        $csvValidator->setTitle('Title');
        $this->assertSame('Title', $csvValidator->getTitle());
    }

    public function testSetFilename()
    {
        $csvValidator = new CsvMockValidator([]);
        $csvValidator->setFilename('Filename');
        $this->assertSame('Filename', $csvValidator->getFilename());
    }

    public function testGetResult()
    {
        $csvValidator = new CsvMockValidator([]);
        $csvValidator->setTitle('Title');
        $this->assertInstanceOf(CsvValidatorResult::class, $csvValidator->getTestResult());
    }

    public function testGetColumnCount()
    {
        $csvValidator = new CsvMockValidator([]);
        $csvValidator->setColumnCount(12);
        $this->assertSame(12, $csvValidator->getColumnCount());
    }

    public function testGetClassname()
    {
        $csvValidator = new CsvMockValidator([]);
        $this->assertSame('CsvMockValidator', $csvValidator->getClassname());
    }

    public function testColumnPresent()
    {
        $csvValidator = new CsvMockValidator([]);
        $csvValidator->setHeader(['test1', 'test2', 'test3']);
        $this->assertSame(true, $csvValidator->columnPresent('test1'));
        $this->assertSame(false, $csvValidator->columnPresent('test0'));
    }

    public function testColumnDuplicated()
    {
        $csvValidator = new CsvMockValidator([]);
        $csvValidator->setHeader(['test1', 'test2', 'test3']);
        $this->assertSame(false, $csvValidator->columnDuplicated('test1', ['test1', 'test2', 'test3']));
        $this->assertSame(false, $csvValidator->columnDuplicated('test5', ['test1', 'test2', 'test3']));
        $csvValidator->setHeader(['test0', 'test2', 'test0']);
        $this->assertSame(true, $csvValidator->columnDuplicated('test0', ['test0', 'test2', 'test0']));
    }
}
