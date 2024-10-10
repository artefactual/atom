<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvDigitalObjectPathValidator
 */
class CsvDigitalObjectPathTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(DebugPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderWithDigitalObjectCols = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,digitalObjectPath,digitalObjectURI,culture';
        $this->csvHeaderDupedPath = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,digitalObjectPath,digitalObjectPath,digitalObjectURI,culture';

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

        $this->csvDataWithDigitalObjectColsPopulatedDupedPath = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","a.png","","",""',
            '"A10101","","","Chemise","","","","A.PNG","","fr",""',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "","b.png","https://www.artefactual.com/wp-content/uploads/2018/08/artefactual-logo-white.svg", "",""',
            '"", "DJ003", "ID4", "Title Four", "","", "","a.png","", "en",""',
            '"E10101 "," DJ004","ID1 ","Some Photographs","","Extent and medium 1","","b.png","https://www.artefactual.com/wp-content/uploads/2018/08/artefactual-logo-white.svg","",""',
            '"G30303","","","Sweater","","","","d.png","","fr",""',
            '"F20202", "DJ005", "", "Voûte, étagère 0074", "", "", "","","www.google.com", "",""',
            '"", "DJ003", "ID5", "Title Four", "","", "","","ftp://www.artefactual.com/wp-content/uploads/2018/08/artefactual-logo-white.svg", "en", ""',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_utf8_bom.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'unix_csv_with_digital_object_cols.csv' => $this->csvHeaderWithDigitalObjectCols."\n".implode("\n", $this->csvDataWithDigitalObjectCols),
            'unix_csv_with_digital_object_cols_populated.csv' => $this->csvHeaderWithDigitalObjectCols."\n".implode("\n", $this->csvDataWithDigitalObjectColsPopulated),
            'unix_csv_with_digital_object_cols_populated_duped_path.csv' => $this->csvHeaderDupedPath."\n".implode("\n", $this->csvDataWithDigitalObjectColsPopulatedDupedPath),
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
             * Test CsvDigitalObjectPathValidator.class.php
             *
             * Tests:
             * - digitalObjectPath column missing
             * - digitalObjectPath column present but empty
             * - digitalObjectPath column present and populated with:
             * -- valid file path
             * -- duplicated file path
             * -- invalid file path
             * -- empty value
             * -- digitalObjectURI column present and populated
             */
            [
                'CsvDigitalObjectPathValidator-digitalObjectPathMissing' => [
                    'csvValidatorClasses' => 'CsvDigitalObjectPathValidator',
                    'filename' => '/unix_csv_without_utf8_bom.csv',
                    'testname' => 'CsvDigitalObjectPathValidator',
                    CsvValidatorResult::TEST_TITLE => CsvDigitalObjectPathValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        "Column 'digitalObjectPath' not present in CSV. Nothing to verify.",
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvDigitalObjectPathValidator-digitalObjectPathEmpty' => [
                    'csvValidatorClasses' => 'CsvDigitalObjectPathValidator',
                    'filename' => '/unix_csv_with_digital_object_cols.csv',
                    'testname' => 'CsvDigitalObjectPathValidator',
                    CsvValidatorResult::TEST_TITLE => CsvDigitalObjectPathValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Column \'digitalObjectPath\' found.',
                        'Digital object folder location not specified.',
                        'Column \'digitalObjectPath\' is empty - nothing to validate.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvDigitalObjectPathValidator-digitalObjectPathEmptyWithDOFolder' => [
                    'csvValidatorClasses' => 'CsvDigitalObjectPathValidator',
                    'filename' => '/unix_csv_with_digital_object_cols.csv',
                    'testname' => 'CsvDigitalObjectPathValidator',
                    'validatorOptions' => [
                        'source' => 'testsourcefile.csv',
                        'className' => 'QubitInformationObject',
                        'pathToDigitalObjects' => 'vfs://root/digital_objects',
                    ],
                    CsvValidatorResult::TEST_TITLE => CsvDigitalObjectPathValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Column \'digitalObjectPath\' found.',
                        'Column \'digitalObjectPath\' is empty - nothing to validate.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvDigitalObjectPathValidator-digitalObjectPathPopulatedWithDOFolder' => [
                    'csvValidatorClasses' => 'CsvDigitalObjectPathValidator',
                    'filename' => '/unix_csv_with_digital_object_cols_populated.csv',
                    'testname' => 'CsvDigitalObjectPathValidator',
                    'validatorOptions' => [
                        'source' => 'testsourcefile.csv',
                        'className' => 'QubitInformationObject',
                        'pathToDigitalObjects' => 'vfs://root/digital_objects',
                    ],
                    CsvValidatorResult::TEST_TITLE => CsvDigitalObjectPathValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        "Column 'digitalObjectPath' found.",
                        "'digitalObjectPath' will be overridden by 'digitalObjectURI' if both are populated.",
                        "'digitalObjectPath' values that will be overridden by 'digitalObjectURI': 2",
                        'Number of duplicated digital object paths found in CSV: 2',
                        'Digital objects in folder not referenced by CSV: 1',
                        'Digital objects referenced by CSV not found in folder: 2',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        "Number of duplicates for path 'a.png': 2",
                        "Number of duplicates for path 'b.png': 2",
                        'Unreferenced digital object: c.png',
                        'Unable to locate digital object: A.PNG',
                        'Unable to locate digital object: d.png',
                    ],
                ],
            ],

            [
                'CsvDigitalObjectPathValidator-digitalObjectPathDupedPath' => [
                    'csvValidatorClasses' => 'CsvDigitalObjectPathValidator',
                    'filename' => '/unix_csv_with_digital_object_cols_populated_duped_path.csv',
                    'testname' => 'CsvDigitalObjectPathValidator',
                    'validatorOptions' => [
                        'source' => 'testsourcefile.csv',
                        'className' => 'QubitInformationObject',
                        'pathToDigitalObjects' => 'vfs://root/digital_objects',
                    ],
                    CsvValidatorResult::TEST_TITLE => CsvDigitalObjectPathValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        '\'digitalObjectPath\' column appears more than once in file.',
                        'Unable to validate because of duplicated columns in CSV.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
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
