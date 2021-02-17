<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 * @covers \CsvDigitalObjectUriValidator
 */
class CsvDigitalObjectUriTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(DebugPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderWithDigitalObjectCols = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,digitalObjectPath,digitalObjectUri,culture';

        $this->csvData = [
            // Note: leading and trailing whitespace in first row is intentional
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataWithDigitalObjectCols = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","","",""',
            '"","","","Chemise","","","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "","","", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "","","", "en"',
        ];

        $this->csvDataWithDigitalObjectColsPopulated = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","a.png","",""',
            '"A10101","","","Chemise","","","","A.PNG","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "","b.png","https://www.artefactual.com/wp-content/uploads/2018/08/artefactual-logo-white.svg", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "","a.png","", "en"',
            '"E10101 "," DJ004","ID1 ","Some Photographs","","Extent and medium 1","","b.png","https://www.artefactual.com/wp-content/uploads/2018/08/artefactual-logo-white.svg",""',
            '"G30303","","","Sweater","","","","d.png","","fr"',
            '"F20202", "DJ005", "", "Voûte, étagère 0074", "", "", "","","www.google.com", ""',
            '"", "DJ003", "ID5", "Title Four", "","", "","","ftp://www.artefactual.com/wp-content/uploads/2018/08/artefactual-logo-white.svg", "en"',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_utf8_bom.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'unix_csv_with_digital_object_cols.csv' => $this->csvHeaderWithDigitalObjectCols."\n".implode("\n", $this->csvDataWithDigitalObjectCols),
            'unix_csv_with_digital_object_cols_populated.csv' => $this->csvHeaderWithDigitalObjectCols."\n".implode("\n", $this->csvDataWithDigitalObjectColsPopulated),
            'digital_objects' => [
                'a.png' => random_bytes(100),
                'b.png' => random_bytes(100),
                'c.png' => random_bytes(100),
            ],
        ];

        $this->vfs = vfsStream::setup('root', null, $directory);
    }

    /**
     * @dataProvider csvValidatorTestProvider
     *
     * Generic test - options and expected results from csvValidatorTestProvider()
     *
     * @param mixed $options
     */
    public function testCsvValidator($options)
    {
        $filename = $this->vfs->url().$options['filename'];
        $validatorOptions = isset($options['validatorOptions']) ? $options['validatorOptions'] : null;

        $csvValidator = new CsvImportValidator($this->context, null, $validatorOptions);
        $this->runValidator($csvValidator, $filename, $options['csvValidatorClasses']);
        $result = $csvValidator->getResultsByFilenameTestname($filename, $options['testname']);

        $this->assertSame($options[CsvValidatorResult::TEST_TITLE], $result[CsvValidatorResult::TEST_TITLE]);
        $this->assertSame($options[CsvValidatorResult::TEST_STATUS], $result[CsvValidatorResult::TEST_STATUS]);
        $this->assertSame($options[CsvValidatorResult::TEST_RESULTS], $result[CsvValidatorResult::TEST_RESULTS]);
        $this->assertSame($options[CsvValidatorResult::TEST_DETAILS], $result[CsvValidatorResult::TEST_DETAILS]);
    }

    public function csvValidatorTestProvider()
    {
        $vfsUrl = 'vfs://root';

        return [
            /*
             * Test CsvDigitalObjectUriValidator.class.php
             *
             * Tests:
             * - digitalObjectUri column missing
             * - digitalObjectUri column present but empty
             * - digitalObjectUri column present and populated with:
             * -- valid URI
             * -- incorrect scheme URI (e.g. ftp://)
             * -- duplicated URI
             * -- invalid URI
             * -- empty value
             * -- digitalObjectUri column present and populated
             */
            [
                'CsvDigitalObjectUriValidator-digitalObjectUriMissing' => [
                    'csvValidatorClasses' => 'CsvDigitalObjectUriValidator',
                    'filename' => '/unix_csv_without_utf8_bom.csv',
                    'testname' => 'CsvDigitalObjectUriValidator',
                    CsvValidatorResult::TEST_TITLE => CsvDigitalObjectUriValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        "Column 'digitalObjectUri' not present in CSV. Nothing to verify.",
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvDigitalObjectUriValidator-digitalObjectUriEmpty' => [
                    'csvValidatorClasses' => 'CsvDigitalObjectUriValidator',
                    'filename' => '/unix_csv_with_digital_object_cols.csv',
                    'testname' => 'CsvDigitalObjectUriValidator',
                    CsvValidatorResult::TEST_TITLE => CsvDigitalObjectUriValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        "Column 'digitalObjectUri' found.",
                        "Column 'digitalObjectUri' is empty.",
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvDigitalObjectUriValidator-digitalObjectUriPopulatedWithDOFolder' => [
                    'csvValidatorClasses' => 'CsvDigitalObjectUriValidator',
                    'filename' => '/unix_csv_with_digital_object_cols_populated.csv',
                    'testname' => 'CsvDigitalObjectUriValidator',
                    'validatorOptions' => [
                        'source' => 'testsourcefile.csv',
                        'className' => 'QubitInformationObject',
                        'pathToDigitalObjects' => 'vfs://root/digital_objects',
                    ],
                    CsvValidatorResult::TEST_TITLE => CsvDigitalObjectUriValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        "Column 'digitalObjectUri' found.",
                        'Repeating Digital object URIs found in CSV.',
                        'Invalid digitalObjectUri values detected: 2',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        "Number of duplicates for URI 'https://www.artefactual.com/wp-content/uploads/2018/08/artefactual-logo-white.svg': 2",
                        'Invalid URI: www.google.com',
                        'Invalid URI: ftp://www.artefactual.com/wp-content/uploads/2018/08/artefactual-logo-white.svg',
                    ],
                ],
            ],
        ];
    }

    // Generic Validation
    protected function runValidator($csvValidator, $filenames, $tests)
    {
        $csvValidator->setSpecificTests($tests);
        $csvValidator->setFilenames(explode(',', $filenames));

        return $csvValidator->validate();
    }
}
