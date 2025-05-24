<?php

/**
 * @internal
 *
 * @covers \CsvValidatorCollection
 */
class CsvValidatorCollectionTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
    }

    public function testGetValidatorCollection()
    {
        $options = [
            'className' => 'QubitInformationObject',
            'source' => '',
            'separator' => ',',
            'enclosure' => '"',
            'specificTests' => '',
            'pathToDigitalObjects' => '',
        ];

        $validatorCollection = CsvValidatorCollection::getValidatorCollection('QubitInformationObject', $options);

        $this->assertInstanceOf(CsvValidatorCollection::class, $validatorCollection);
    }

    public function testGetResultCollection()
    {
        $options = [
            'className' => 'QubitInformationObject',
            'source' => '',
            'separator' => ',',
            'enclosure' => '"',
            'specificTests' => '',
            'pathToDigitalObjects' => '',
        ];

        $validatorCollection = CsvValidatorCollection::getValidatorCollection('QubitInformationObject', $options);

        $resultCollection = $validatorCollection->getResultCollection();

        $this->assertInstanceOf(CsvValidatorResultCollection::class, $resultCollection);
    }
}
