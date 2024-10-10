<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvScriptValidator
 */
class CsvScriptTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(DebugPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderWithScript = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture,scriptOfDescription';
        $this->csvHeaderDupedScript = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture,scriptOfDescription,scriptOfDescription';

        $this->csvData = [
            // Note: leading and trailing whitespace in first row is intentional
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataValidScripts = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","es ", "Latn"',
            '"","","","Chemise","","","","fr","Copt"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", "de","Grek "',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"," Hang"',
        ];

        $this->csvDataScriptsSomeInvalid = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","es ", "Latin"',
            '"","","","Chemise","","","","fr","Copt|Latin"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", "de","Gggg|HGGG"',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"," LATN"',
        ];

        $this->csvDataDupedScripts = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","es ", "Latin",""',
            '"","","","Chemise","","","","fr","Copt|Latin", ""',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", "de","Gggg|HGGG", "DGGG"',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"," LATN",""',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_utf8_bom.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'unix_csv_valid_scripts.csv' => $this->csvHeaderWithScript."\n".implode("\n", $this->csvDataValidScripts),
            'unix_csv_scripts_some_invalid.csv' => $this->csvHeaderWithScript."\n".implode("\n", $this->csvDataScriptsSomeInvalid),
            'unix_csv_duped_scripts.csv' => $this->csvHeaderDupedScript."\n".implode("\n", $this->csvDataDupedScripts),
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
             * Test CsvScriptValidator.class.php
             *
             * Tests:
             * - scriptOfDescription column missing
             * - scriptOfDescription column present with valid data
             * - scriptOfDescription column present with mix of valid and invalid data
             */
            [
                'CsvScriptValidator-ScriptColMissing' => [
                    'csvValidatorClasses' => 'CsvScriptValidator',
                    'filename' => '/unix_csv_without_utf8_bom.csv',
                    'testname' => 'CsvScriptValidator',
                    CsvValidatorResult::TEST_TITLE => CsvScriptValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        '\'scriptOfDescription\' column not present in file.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvScriptValidator-ScriptValid' => [
                    'csvValidatorClasses' => 'CsvScriptValidator',
                    'filename' => '/unix_csv_valid_scripts.csv',
                    'testname' => 'CsvScriptValidator',
                    CsvValidatorResult::TEST_TITLE => CsvScriptValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        '\'scriptOfDescription\' column values are all valid.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvScriptValidator-ScriptSomeInvalid' => [
                    'csvValidatorClasses' => 'CsvScriptValidator',
                    'filename' => '/unix_csv_scripts_some_invalid.csv',
                    'testname' => 'CsvScriptValidator',
                    CsvValidatorResult::TEST_TITLE => CsvScriptValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Rows with invalid scriptOfDescription values: 4',
                        'Invalid scriptOfDescription values: Latin, Gggg, HGGG, LATN',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'CSV row numbers where issues were found: 2, 3, 4, 5',
                    ],
                ],
            ],

            [
                'CsvScriptValidator-DupedScript' => [
                    'csvValidatorClasses' => 'CsvScriptValidator',
                    'filename' => '/unix_csv_duped_scripts.csv',
                    'testname' => 'CsvScriptValidator',
                    CsvValidatorResult::TEST_TITLE => CsvScriptValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        '\'scriptOfDescription\' column appears more than once in file.',
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
