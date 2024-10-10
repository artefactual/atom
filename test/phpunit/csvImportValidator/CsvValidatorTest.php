<?php

/**
 * @internal
 *
 * @covers \CsvImportValidator
 */
class CsvValidatorTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
    }

    // Basic tests
    public function testConstructorWithNoContextPassed()
    {
        $csvValidator = new CsvImportValidator(null, $this->vdbcon, null);

        $this->assertSame(sfContext::class, get_class($csvValidator->getContext()));
    }

    public function testConstructorWithNoDbconPassed()
    {
        $csvValidator = new CsvImportValidator($this->context, null, null);

        $this->assertSame(DebugPDO::class, get_class($csvValidator->getDbCon()));
    }

    public function testSetInvalidOptionsException()
    {
        $this->expectException(UnexpectedValueException::class);
        $options = ['fakeOption'];
        $csvValidator = new CsvImportValidator($this->context, null, $options);
    }

    public function testSetValidClassNameOption()
    {
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $csvValidator->setOption('className', 'QubitInformationObject');
        $this->assertSame('QubitInformationObject', $csvValidator->getOption('className'));
    }

    public function testSetInvalidClassNameOption()
    {
        $this->expectException(UnexpectedValueException::class);
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $csvValidator->setOption('className', 'QubitProperty');
    }

    public function testSetSourceOption()
    {
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $csvValidator->setOption('source', 'testfilename.csv');
        $this->assertSame('testfilename.csv', $csvValidator->getOption('source'));
    }

    public function testSetSeparatorOption()
    {
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $csvValidator->setOption('separator', ';');
        $this->assertSame(';', $csvValidator->getOption('separator'));
    }

    public function testSetInvalidSeparatorOption()
    {
        $this->expectException(UnexpectedValueException::class);
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $csvValidator->setOption('separator', ';;');
    }

    public function testSetEnclosureOption()
    {
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $csvValidator->setOption('enclosure', "'");
        $this->assertSame("'", $csvValidator->getOption('enclosure'));
    }

    public function testSetInvalidEnclosureOption()
    {
        $this->expectException(UnexpectedValueException::class);
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $csvValidator->setOption('enclosure', '""');
    }

    public function testSetSpecificTestsOption()
    {
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $csvValidator->setOption('specificTests', 'CsvSampleValuesValidator,CsvLegacyIdValidator');
        $this->assertSame('CsvSampleValuesValidator,CsvLegacyIdValidator', $csvValidator->getOption('specificTests'));
    }

    public function testSetPathToDigitalObjectsOption()
    {
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $csvValidator->setOption('pathToDigitalObjects', '/usr/test/example');
        $this->assertSame('/usr/test/example', $csvValidator->getOption('pathToDigitalObjects'));
    }

    public function testDefaultOptions()
    {
        $csvValidator = new CsvImportValidator($this->context, null, null);
        $this->assertSame('QubitInformationObject', $csvValidator->getOption('className'));
        $this->assertSame('', $csvValidator->getOption('source'));
        $this->assertSame(',', $csvValidator->getOption('separator'));
        $this->assertSame('"', $csvValidator->getOption('enclosure'));
        $this->assertSame('', $csvValidator->getOption('specificTests'));
        $this->assertSame('', $csvValidator->getOption('pathToDigitalObjects'));
    }
}
