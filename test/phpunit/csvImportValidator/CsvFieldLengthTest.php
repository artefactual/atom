<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvFieldLengthValidator
 */
class CsvFieldLengthTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(DebugPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderWithLanguage = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture,language';
        $this->csvHeaderMissingCulture = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository';

        $this->csvData = [
            // Note: leading and trailing whitespace in first row is intentional
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataCultureLanguage = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","es ","es"',
            '"","","","Chemise","","","","fr","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", "de","de"',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en","english"',
        ];

        $this->csvDataCultureLanguageMultErrors = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","es ","this is spanish"',
            '"","","","Chemise","","","","fr","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", "Germany","de"',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en","english"',
        ];

        $this->csvDataCulturesSomeInvalid = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","es "',
            '"","","","Chemise","","","","fr|en"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", "thisisalongculturevalue"',
            '"E20202", "DJ003", "ID4", "Title Four", "","", "", "en"',
            '"F20202", "DJ004", "DD8989", "pdf documents", "","", "", "ca@valencia"',
        ];

        $this->csvDataMissingCulture = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1",""',
            '"","","","Chemise","","",""',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", ""',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_utf8_bom.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),

            'unix_csv_culture_language_length_error.csv' => $this->csvHeaderWithLanguage."\n".implode("\n", $this->csvDataCultureLanguage),
            'unix_csv_culture_language_length_errors.csv' => $this->csvHeaderWithLanguage."\n".implode("\n", $this->csvDataCultureLanguageMultErrors),
            'unix_csv_missing_culture.csv' => $this->csvHeaderMissingCulture."\n".implode("\n", $this->csvDataMissingCulture),
            'unix_csv_cultures_some_invalid.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataCulturesSomeInvalid),
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
             * Test CsvFieldLengthValidator.class.php
             *
             * Tests:
             * - no checked columns present
             * - one checked col present, not triggering error
             * - multiple checked cols present, one triggers error
             * - multiple checked cols present, multiple trigger error
             */
            [
                'CsvFieldLengthValidator-LengthCheckNonePresent' => [
                    'csvValidatorClasses' => 'CsvFieldLengthValidator',
                    'filename' => '/unix_csv_missing_culture.csv',
                    'testname' => 'CsvFieldLengthValidator',
                    CsvValidatorResult::TEST_TITLE => CsvFieldLengthValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'No columns to check.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvFieldLengthValidator-LengthCheckValidCulturesPresent' => [
                    'csvValidatorClasses' => 'CsvFieldLengthValidator',
                    'filename' => '/unix_csv_cultures_some_invalid.csv',
                    'testname' => 'CsvFieldLengthValidator',
                    CsvValidatorResult::TEST_TITLE => CsvFieldLengthValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: culture',
                        '\'culture\' column may have invalid values.',
                        '\'culture\' values that exceed 11 characters: 1',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'culture column value: thisisalongculturevalue',
                    ],
                ],
            ],

            [
                'CsvFieldLengthValidator-LengthCheckLanguageCultureError' => [
                    'csvValidatorClasses' => 'CsvFieldLengthValidator',
                    'filename' => '/unix_csv_culture_language_length_error.csv',
                    'testname' => 'CsvFieldLengthValidator',
                    CsvValidatorResult::TEST_TITLE => CsvFieldLengthValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: culture,language',
                        '\'culture\' values that exceed 11 characters: 0',
                        '\'language\' column may have invalid values.',
                        '\'language\' values that exceed 6 characters: 1',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'language column value: english',
                    ],
                ],
            ],

            [
                'CsvFieldLengthValidator-LengthCheckLanguageCultureMultErrors' => [
                    'csvValidatorClasses' => 'CsvFieldLengthValidator',
                    'filename' => '/unix_csv_culture_language_length_errors.csv',
                    'testname' => 'CsvFieldLengthValidator',
                    CsvValidatorResult::TEST_TITLE => CsvFieldLengthValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: culture,language',
                        '\'culture\' values that exceed 11 characters: 0',
                        '\'language\' column may have invalid values.',
                        '\'language\' values that exceed 6 characters: 2',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'language column value: this is spanish',
                        'language column value: english',
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
