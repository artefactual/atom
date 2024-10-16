<?php

/**
 * @internal
 *
 * @covers \QubitPhysicalObjectCsvHoldingsReport
 */
class PhysicalObjectCsvHoldingsReportTest extends \PHPUnit\Framework\TestCase
{
    protected $csvData;
    protected $ormClasses;

    // Fixtures

    public function setUp(): void
    {
        $this->ormClasses = [
            'informationObject' => \AccessToMemory\test\mock\QubitInformationObject::class,
            'accession' => \AccessToMemory\test\mock\QubitAccession::class,
            'physicalObject' => \AccessToMemory\test\mock\QubitPhysicalObject::class,
        ];

        $this->typeMap = [
            'description' => \AccessToMemory\test\mock\QubitInformationObject::class,
            'accession' => \AccessToMemory\test\mock\QubitAccession::class,
        ];
    }

    // Data providers

    public function fetchHoldingsRowsProvider()
    {
        $rows = [
            [
                'object_id' => 111111,
                'class_name' => 'QubitInformationObject',
            ],
            [
                'object_id' => 222222,
                'class_name' => 'QubitInformationObject',
            ],
            [
                'object_id' => 516,
                'class_name' => 'QubitAccession',
            ],
            [
                'object_id' => 533,
                'class_name' => 'QubitAccession',
            ],
        ];

        return [
            [$rows],
        ];
    }

    public function mixedHoldingsDataProvider()
    {
        $holdingsData = [
            'total' => 4,
            'types' => [
                \AccessToMemory\test\mock\QubitInformationObject::class => [
                    'total' => 2,
                    'holdings' => [111111, 222222],
                ],
                \AccessToMemory\test\mock\QubitAccession::class => [
                    'total' => 2,
                    'holdings' => [444, 555],
                ],
            ],
        ];

        return [
            [$holdingsData],
        ];
    }

    public function informationObjectHoldingsDataProvider()
    {
        $holdingsData = [
            'total' => 2,
            'types' => [
                \AccessToMemory\test\mock\QubitInformationObject::class => [
                    'total' => 2,
                    'holdings' => [111111, 222222],
                ],
            ],
        ];

        return [
            [$holdingsData],
        ];
    }

    // Tests

    public function testSetOptionsThrowsTypeError()
    {
        $this->expectException(TypeError::class);
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOptions(1);
        $report->setOptions(new stdClass());
    }

    public function testSetValidOptions()
    {
        $options = ['suppressEmpty' => true, 'holdingType' => 'QubitAccession'];
        $report = new QubitPhysicalObjectCsvHoldingsReport($options);
        $this->assertSame(true, $report->getOption('suppressEmpty'));
        $this->assertSame('QubitAccession', $report->getOption('holdingType'));
    }

    public function testSetInvalidOptionsException()
    {
        $this->expectException(UnexpectedValueException::class);
        $options = ['fakeOption'];
        $report = new QubitPhysicalObjectCsvHoldingsReport($options);
    }

    public function testSetValidOption()
    {
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOption('holdingType', 'QubitAccession');
        $this->assertSame('QubitAccession', $report->getOption('holdingType'));
    }

    public function testSetValidSuppressEmptyOptionValues()
    {
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOption('suppressEmpty', false);
        $this->assertSame(false, $report->getOption('suppressEmpty'));
        $report->setOption('suppressEmpty', true);
        $this->assertSame(true, $report->getOption('suppressEmpty'));
    }

    public function testSetInvalidSuppressEmptyOptionValueException()
    {
        $this->expectException(UnexpectedValueException::class);
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOption('suppressEmpty', 'notBoolean');
    }

    public function testSetValidHoldingTypeOptionValues()
    {
        $report = new QubitPhysicalObjectCsvHoldingsReport();

        foreach (QubitPhysicalObjectCsvHoldingsReport::$defaultTypeMap as $description => $className) {
            $report->setOption('holdingType', $className);
            $this->assertSame($className, $report->getOption('holdingType'));
        }
    }

    public function testSetInvalidHoldingTypeOptionValueException()
    {
        $this->expectException(UnexpectedValueException::class);
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOption('holdingType', 'fakeClassName');
    }

    public function testSetInvalidOptionException()
    {
        $this->expectException(UnexpectedValueException::class);
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOption('fakeOption', true);
    }

    public function testGetInvalidOptionException()
    {
        $this->expectException(UnexpectedValueException::class);
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->getOption('fakeOption');
    }

    public function testDefaultOptions()
    {
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $this->assertSame(false, $report->getOption('suppressEmpty'));
        $this->assertSame(null, $report->getOption('holdingType'));
    }

    public function testAddEmptyHoldingColumnsToRow()
    {
        $row = [];
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $row = $report->addEmptyHoldingColumnsToRow($row);

        $this->assertSame(count(QubitPhysicalObjectCsvHoldingsReport::$headerRow), count($row));
    }

    public function testSummarizeHoldingsDataNoRowsTotal()
    {
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $summary = $report->summarizeHoldingsData([]);
        $this->assertSame(0, $summary['total']);
    }

    /**
     * @dataProvider fetchHoldingsRowsProvider
     *
     * @param mixed $rows
     */
    public function testSummarizeHoldingsDataTotal($rows)
    {
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $summary = $report->summarizeHoldingsData($rows);
        $this->assertSame(4, $summary['total']);
    }

    /**
     * @dataProvider fetchHoldingsRowsProvider
     *
     * @param mixed $rows
     */
    public function testSummarizeHoldingsDataTypeTotals($rows)
    {
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $summary = $report->summarizeHoldingsData($rows);
        $this->assertSame(2, count($summary['types']));
        $this->assertSame(2, $summary['types']['QubitInformationObject']['total']);
        $this->assertSame(2, $summary['types']['QubitAccession']['total']);
    }

    /**
     * @dataProvider mixedHoldingsDataProvider
     *
     * @param mixed $holdingsData
     */
    public function testWritePhysicalObjectAndMixedHoldings($holdingsData)
    {
        $writer = \League\Csv\Writer::createFromString('');
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOrmClasses($this->ormClasses);
        $report->setTypeMap($this->typeMap);

        $rowStart = ['Example Box', 'Example Box Location', 'Box'];
        $report->writePhysicalObjectAndHoldings($writer, $rowStart, $holdingsData);

        $expectedOutput = <<<EOM
"Example Box","Example Box Location",Box,description,IDENTIFIER,"Information Object",Term,information-object
"Example Box","Example Box Location",Box,description,IDENTIFIER,"Information Object",Term,information-object
"Example Box","Example Box Location",Box,accession,IDENTIFIER,Accession,,accession
"Example Box","Example Box Location",Box,accession,IDENTIFIER,Accession,,accession\n
EOM;

        $this->assertSame($expectedOutput, $writer->getContent());
    }

    /**
     * @dataProvider mixedHoldingsDataProvider
     *
     * @param mixed $holdingsData
     */
    public function testWritePhysicalObjectAndMixedHoldingsForInformationObjectHoldingType($holdingsData)
    {
        $writer = \League\Csv\Writer::createFromString('');
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOrmClasses($this->ormClasses);
        $report->setTypeMap($this->typeMap);
        $report->setOption('holdingType', \AccessToMemory\test\mock\QubitInformationObject::class);
        $rowStart = ['Example Box', 'Example Box Location', 'Box'];
        $report->writePhysicalObjectAndHoldings($writer, $rowStart, $holdingsData);

        $expectedOutput = <<<EOM
"Example Box","Example Box Location",Box,description,IDENTIFIER,"Information Object",Term,information-object
"Example Box","Example Box Location",Box,description,IDENTIFIER,"Information Object",Term,information-object\n
EOM;

        $this->assertSame($expectedOutput, $writer->getContent());
    }

    /**
     * @dataProvider mixedHoldingsDataProvider
     *
     * @param mixed $holdingsData
     */
    public function testWritePhysicalObjectAndMixedHoldingsForAccessionHoldingType($holdingsData)
    {
        $writer = \League\Csv\Writer::createFromString('');
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOrmClasses($this->ormClasses);
        $report->setTypeMap($this->typeMap);
        $report->setOption('holdingType', \AccessToMemory\test\mock\QubitAccession::class);
        $rowStart = ['Example Box', 'Example Box Location', 'Box'];
        $report->writePhysicalObjectAndHoldings($writer, $rowStart, $holdingsData);

        $expectedOutput = <<<EOM
"Example Box","Example Box Location",Box,accession,IDENTIFIER,Accession,,accession
"Example Box","Example Box Location",Box,accession,IDENTIFIER,Accession,,accession\n
EOM;

        $this->assertSame($expectedOutput, $writer->getContent());
    }

    /**
     * @dataProvider mixedHoldingsDataProvider
     *
     * @param mixed $holdingsData
     */
    public function testWritePhysicalObjectAndMixedHoldingsForNoneHoldingType($holdingsData)
    {
        $writer = \League\Csv\Writer::createFromString('');
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOrmClasses($this->ormClasses);
        $report->setTypeMap($this->typeMap);
        $report->setOption('holdingType', 'none');
        $rowStart = ['Example Box', 'Example Box Location', 'Box'];
        $report->writePhysicalObjectAndHoldings($writer, $rowStart, $holdingsData);

        $this->assertSame('', $writer->getContent());
    }

    /**
     * @dataProvider informationObjectHoldingsDataProvider
     *
     * @param mixed $holdingsData
     */
    public function testWritePhysicalObjectAndInformationObjectHoldings($holdingsData)
    {
        $writer = \League\Csv\Writer::createFromString('');
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOrmClasses($this->ormClasses);
        $report->setTypeMap($this->typeMap);
        $report->setOption('holdingType', \AccessToMemory\test\mock\QubitInformationObject::class);
        $rowStart = ['Example Box', 'Example Box Location', 'Box'];
        $report->writePhysicalObjectAndHoldings($writer, $rowStart, $holdingsData);

        $expectedOutput = <<<EOM
"Example Box","Example Box Location",Box,description,IDENTIFIER,"Information Object",Term,information-object
"Example Box","Example Box Location",Box,description,IDENTIFIER,"Information Object",Term,information-object\n
EOM;

        $this->assertSame($expectedOutput, $writer->getContent());
    }

    /**
     * @dataProvider informationObjectHoldingsDataProvider
     *
     * @param mixed $holdingsData
     */
    public function testWritePhysicalObjectAndInformationObjectHoldingsWithAccessionHoldingType($holdingsData)
    {
        $writer = \League\Csv\Writer::createFromString('');
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOption('holdingType', 'QubitAccession');
        $rowStart = ['Example Box', 'Example Box Location', 'Box'];
        $report->writePhysicalObjectAndHoldings($writer, $rowStart, $holdingsData);

        $this->assertSame('', $writer->getContent());
    }

    /**
     * @dataProvider informationObjectHoldingsDataProvider
     *
     * @param mixed $holdingsData
     */
    public function testWritePhysicalObjectAndInformationObjectHoldingsWithNoneHoldingType($holdingsData)
    {
        $writer = \League\Csv\Writer::createFromString('');
        $report = new QubitPhysicalObjectCsvHoldingsReport();
        $report->setOption('holdingType', 'none');
        $rowStart = ['Example Box', 'Example Box Location', 'Box'];
        $report->writePhysicalObjectAndHoldings($writer, $rowStart, $holdingsData);

        $this->assertSame('', $writer->getContent());
    }
}
