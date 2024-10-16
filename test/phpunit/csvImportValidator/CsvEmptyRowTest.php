<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvEmptyRowValidator
 */
class CsvEmptyRowTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(DebugPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderBlank = '';
        $this->csvHeaderBlankWithCommas = ',,,';

        $this->csvData = [
            // Note: leading and trailing whitespace in first row is intentional
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataEmptyRows = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
            '  , ',
            ' ',
            '',
        ];

        $this->csvDataEmptyRowsWithCommas = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            ',,,',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '   , , ',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_utf8_bom.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'unix_csv_with_empty_rows.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataEmptyRows),
            'unix_csv_with_empty_rows_with_commas.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataEmptyRowsWithCommas),
            'unix_csv_with_empty_rows_header.csv' => $this->csvHeaderBlank."\n".implode("\n", $this->csvDataEmptyRows),
            'unix_csv_with_empty_rows_header_with_commas.csv' => $this->csvHeaderBlankWithCommas."\n".implode("\n", $this->csvDataEmptyRowsWithCommas),
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
            [
                'CsvEmptyRowValidator-testNoEmptyRows' => [
                    'csvValidatorClasses' => 'CsvEmptyRowValidator',
                    'filename' => '/unix_csv_without_utf8_bom.csv',
                    'testname' => 'CsvEmptyRowValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEmptyRowValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'CSV does not have any blank rows.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [],
                ],
            ],

            [
                'CsvEmptyRowValidator-testEmptyRows' => [
                    'csvValidatorClasses' => 'CsvEmptyRowValidator',
                    'filename' => '/unix_csv_with_empty_rows.csv',
                    'testname' => 'CsvEmptyRowValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEmptyRowValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'CSV blank row count: 2',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'Blank row numbers: 3, 6',
                    ],
                ],
            ],

            [
                'CsvEmptyRowValidator-testEmptyRowsWithCommas' => [
                    'csvValidatorClasses' => 'CsvEmptyRowValidator',
                    'filename' => '/unix_csv_with_empty_rows_with_commas.csv',
                    'testname' => 'CsvEmptyRowValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEmptyRowValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'CSV blank row count: 2',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'Blank row numbers: 3, 5',
                    ],
                ],
            ],

            [
                'CsvEmptyRowValidator-testEmptyHeader' => [
                    'csvValidatorClasses' => 'CsvEmptyRowValidator',
                    'filename' => '/unix_csv_with_empty_rows_header.csv',
                    'testname' => 'CsvEmptyRowValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEmptyRowValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'CSV Header is blank.',
                        'CSV blank row count: 2',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'Blank row numbers: 3, 6',
                    ],
                ],
            ],

            [
                'CsvEmptyRowValidator-EmptyRowsAndHeader' => [
                    'csvValidatorClasses' => 'CsvEmptyRowValidator',
                    'filename' => '/unix_csv_with_empty_rows_header_with_commas.csv',
                    'testname' => 'CsvEmptyRowValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEmptyRowValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'CSV Header is blank.',
                        'CSV blank row count: 2',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'Blank row numbers: 3, 5',
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
