<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvColumnCountValidator
 */
class CsvColumnCountTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(DebugPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderShort = 'legacyId,parentId,identifier,title,levelOfDescription,repository,culture';
        $this->csvHeaderLong = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture,extraHeading';

        $this->csvData = [
            // Note: leading and trailing whitespace in first row is intentional
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataShortRow = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise ","","","fr"',  // Short row: 7 cols
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataShortRows = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise ","","","fr"',  // Short row: 7 cols
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "", "en"',  // Short row: 6 cols
            '', // Short row: zero cols
        ];

        $this->csvDataLongRow = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise ","","", "","fr", ""',  // Long row: 9 cols
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataLongRows = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","","","",', // Long row: 12 cols
            '"","","","Chemise ","","", "","fr", ""',  // Long row: 9 cols
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_utf8_bom.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'unix_csv_with_short_header.csv' => $this->csvHeaderShort."\n".implode("\n", $this->csvData),
            'unix_csv_with_long_header.csv' => $this->csvHeaderLong."\n".implode("\n", $this->csvData),
            'unix_csv_with_short_row.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataShortRow),
            'unix_csv_with_long_row.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataLongRow),
            'unix_csv_with_short_rows.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataShortRows),
            'unix_csv_with_long_rows.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataLongRows),
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
             * Test csvColumnCountTest.class.php
             *
             * Test that all rows including header have the same number of
             * columns/elements.
             *
             * - test columns all equal length
             * - test incorrect separator set
             * - test header too short
             * - test header too long
             * - test single row too short
             * - test single row too long
             * - test rows too short
             * - test rows too long
             */
            [
                'CsvColumnCountValidator-testColumnsEqualLength' => [
                    'csvValidatorClasses' => 'CsvColumnCountValidator',
                    'filename' => '/unix_csv_without_utf8_bom.csv',
                    'testname' => 'CsvColumnCountValidator',
                    CsvValidatorResult::TEST_TITLE => CsvColumnCountValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Number of columns in CSV: 8',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [],
                ],
            ],

            [
                'CsvColumnCountValidator-incorrectSeparator ' => [
                    'csvValidatorClasses' => 'CsvColumnCountValidator',
                    'filename' => '/unix_csv_without_utf8_bom.csv',
                    'testname' => 'CsvColumnCountValidator',
                    'validatorOptions' => [
                        'separator' => 'j',
                    ],
                    CsvValidatorResult::TEST_TITLE => CsvColumnCountValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Number of columns in CSV: 1',
                        'CSV appears to have only one column - ensure CSV field separator is comma (\',\').',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [],
                ],
            ],

            [
                'CsvColumnCountValidator-testHeaderTooShort' => [
                    'csvValidatorClasses' => 'CsvColumnCountValidator',
                    'filename' => '/unix_csv_with_short_header.csv',
                    'testname' => 'CsvColumnCountValidator',
                    CsvValidatorResult::TEST_TITLE => CsvColumnCountValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Number of rows with 7 columns: 1',
                        'Number of rows with 8 columns: 4',
                        'CSV rows with different lengths detected - ensure CSV enclosure character is double quote (\'"\').',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [],
                ],
            ],

            [
                'CsvColumnCountValidator-testHeaderTooLong' => [
                    'csvValidatorClasses' => 'CsvColumnCountValidator',
                    'filename' => '/unix_csv_with_long_header.csv',
                    'testname' => 'CsvColumnCountValidator',
                    CsvValidatorResult::TEST_TITLE => CsvColumnCountValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Number of rows with 9 columns: 1',
                        'Number of rows with 8 columns: 4',
                        'CSV rows with different lengths detected - ensure CSV enclosure character is double quote (\'"\').',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [],
                ],
            ],

            [
                'CsvColumnCountValidator-testRowTooShort' => [
                    'csvValidatorClasses' => 'CsvColumnCountValidator',
                    'filename' => '/unix_csv_with_short_row.csv',
                    'testname' => 'CsvColumnCountValidator',
                    CsvValidatorResult::TEST_TITLE => CsvColumnCountValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Number of rows with 8 columns: 4',
                        'Number of rows with 7 columns: 1',
                        'CSV rows with different lengths detected - ensure CSV enclosure character is double quote (\'"\').',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [],
                ],
            ],

            [
                'CsvColumnCountValidator-testRowTooLong' => [
                    'csvValidatorClasses' => 'CsvColumnCountValidator',
                    'filename' => '/unix_csv_with_long_row.csv',
                    'testname' => 'CsvColumnCountValidator',
                    CsvValidatorResult::TEST_TITLE => CsvColumnCountValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Number of rows with 8 columns: 4',
                        'Number of rows with 9 columns: 1',
                        'CSV rows with different lengths detected - ensure CSV enclosure character is double quote (\'"\').',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [],
                ],
            ],

            [
                'CsvColumnCountValidator-testRowsTooShort' => [
                    'csvValidatorClasses' => 'CsvColumnCountValidator',
                    'filename' => '/unix_csv_with_short_rows.csv',
                    'testname' => 'CsvColumnCountValidator',
                    CsvValidatorResult::TEST_TITLE => CsvColumnCountValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Number of rows with 8 columns: 3',
                        'Number of rows with 7 columns: 1',
                        'Number of rows with 6 columns: 1',
                        'CSV rows with different lengths detected - ensure CSV enclosure character is double quote (\'"\').',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [],
                ],
            ],

            [
                'CsvColumnCountValidator-testRowsTooLong' => [
                    'csvValidatorClasses' => 'CsvColumnCountValidator',
                    'filename' => '/unix_csv_with_long_rows.csv',
                    'testname' => 'CsvColumnCountValidator',
                    CsvValidatorResult::TEST_TITLE => CsvColumnCountValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Number of rows with 8 columns: 3',
                        'Number of rows with 11 columns: 1',
                        'Number of rows with 9 columns: 1',
                        'CSV rows with different lengths detected - ensure CSV enclosure character is double quote (\'"\').',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [],
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
