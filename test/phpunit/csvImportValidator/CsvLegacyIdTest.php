<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvLegacyIdValidator
 */
class CsvLegacyIdTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(DebugPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderMissingLegacyId = 'parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderDupedLegacyId = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture,legacyId';

        $this->csvData = [
            // Note: leading and trailing whitespace in first row is intentional
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataDuplicatedLegacyId = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"B10101", "DJ003", "ID4", "Title Four", "","", "", "fr"',
            '"B10101", "DJ005", "ID5", "Title Five", "","", "", "en"',
        ];

        $this->csvDataDuplicatedLegacyIdCulture = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"B10101", "DJ003", "ID4", "Title Four", "","", "", "en"',
            '"B10101", "DJ005", "ID5", "Title Five", "","", "", "en"',
        ];

        $this->csvDataMissingLegacyId = [
            '" DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","Chemise","","","","fr"',
            '"DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataDuplicatedLegacyIdColumn = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","", ""',
            '"","","","Chemise","","","","fr", ""',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", "", "D20202"',
            '"B10101", "DJ003", "ID4", "Title Four", "","", "", "fr", ""',
            '"B10101", "DJ005", "ID5", "Title Five", "","", "", "en", "B10101"',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_utf8_bom.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'unix_csv_with_duplicated_legacy_id.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataDuplicatedLegacyId),
            'unix_csv_with_duplicated_legacy_id_culture.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataDuplicatedLegacyIdCulture),
            'unix_csv_missing_legacy_id.csv' => $this->csvHeaderMissingLegacyId."\n".implode("\n", $this->csvDataMissingLegacyId),
            'unix_csv_with_duplicated_legacy_id_column.csv' => $this->csvHeaderDupedLegacyId."\n".implode("\n", $this->csvDataDuplicatedLegacyIdColumn),
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
             * Test CsvLegacyIdValidator.class.php
             *
             * Tests:
             * - legacyId col missing
             * - legacyId not populated
             * - legacyId populated
             * - duplicate legacyId
             */
            [
                'CsvLegacyTest-LegacyIdColumnMissing' => [
                    'csvValidatorClasses' => 'CsvLegacyIdValidator',
                    'filename' => '/unix_csv_missing_legacy_id.csv',
                    'testname' => 'CsvLegacyIdValidator',
                    CsvValidatorResult::TEST_TITLE => CsvLegacyIdValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        '\'legacyId\' column not present. Future CSV updates may not match these records.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvLegacyTest-LegacyIdColumnPresent' => [
                    'csvValidatorClasses' => 'CsvLegacyIdValidator',
                    'filename' => '/unix_csv_without_utf8_bom.csv',
                    'testname' => 'CsvLegacyIdValidator',
                    CsvValidatorResult::TEST_TITLE => CsvLegacyIdValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        '\'legacyId\' values are all unique.',
                        'Rows with empty \'legacyId\' column: 2',
                        'Future CSV updates may not match these records.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'CSV row numbers missing \'legacyId\': 3, 5',
                    ],
                ],
            ],

            [
                'CsvLegacyTest-DuplicatedLegacyId' => [
                    'csvValidatorClasses' => 'CsvLegacyIdValidator',
                    'filename' => '/unix_csv_with_duplicated_legacy_id.csv',
                    'testname' => 'CsvLegacyIdValidator',
                    CsvValidatorResult::TEST_TITLE => CsvLegacyIdValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Rows with non-unique \'legacyId\' values: 1',
                        'Rows with empty \'legacyId\' column: 1',
                        'Future CSV updates may not match these records.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'Non-unique \'legacyId\' values: B10101',
                        'CSV row numbers missing \'legacyId\': 3',
                    ],
                ],
            ],

            [
                'CsvLegacyTest-DuplicatedLegacyIdCulture' => [
                    'csvValidatorClasses' => 'CsvLegacyIdValidator',
                    'filename' => '/unix_csv_with_duplicated_legacy_id_culture.csv',
                    'testname' => 'CsvLegacyIdValidator',
                    CsvValidatorResult::TEST_TITLE => CsvLegacyIdValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Rows with non-unique \'legacyId\' values: 1',
                        'Consecutive CSV rows with matching legacyId and culture will trigger errors during CSV import.',
                        'Rows with empty \'legacyId\' column: 1',
                        'Future CSV updates may not match these records.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'Non-unique \'legacyId\' values: B10101',
                        'Duplicate translation values for: legacyId: B10101; culture: en',
                        'CSV row numbers missing \'legacyId\': 3',
                    ],
                ],
            ],

            [
                'CsvLegacyTest-DuplicatedLegacyId' => [
                    'csvValidatorClasses' => 'CsvLegacyIdValidator',
                    'filename' => '/unix_csv_with_duplicated_legacy_id_column.csv',
                    'testname' => 'CsvLegacyIdValidator',
                    CsvValidatorResult::TEST_TITLE => CsvLegacyIdValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        '\'legacyId\' column appears more than once in file.',
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
