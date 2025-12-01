<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvFieldLengthValidator
 */
class CsvLanguageScriptTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;
    protected $vfs;
    protected $csvData;
    protected $csvInvalidData;
    protected $csvHeader;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(PropelPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,language,script,languageOfDescription,scriptOfDescription,repository,culture';

        $this->csvData = [
            '"","","","title","","","en","Latn","en","Latn","","en"',
            '"","","","another title","","","","","","","","en"',
        ];

        $this->csvInvalidData = [
            '"","","","title","","","en|fr","Latn | Cyrl","en|fr","Latn|Cyrl","","en"',
            '"","","","another title","","","en | fr","Latn|Cyrl","en|fr","Latn|Cyrl","","en"',
            '"","","","third title","","","jp|kr","Japn|Kore","en |en","Latn|Cyrl","","en"',
            '"","","","fourth title","","","en|ar","Latn|Arab","en|ar","Latn | Arab","","en"',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_valid.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'unix_csv_invalid.csv' => $this->csvHeader."\n".implode("\n", $this->csvInvalidData),
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
             * - Valid title
             * - Title field with more than 1024 characters
             */
            [
                'CsvLanguageScriptValidator-LengthCheckValid' => [
                    'csvValidatorClasses' => 'CsvLanguageScriptValidator',
                    'filename' => '/unix_csv_valid.csv',
                    'testname' => 'CsvLanguageScriptValidator',
                    CsvValidatorResult::TEST_TITLE => CsvLanguageScriptValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'All language and script columns contain valid characters.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],
            [
                'CsvLanguageScriptValidator-LengthCheckValid' => [
                    'csvValidatorClasses' => 'CsvLanguageScriptValidator',
                    'filename' => '/unix_csv_invalid.csv',
                    'testname' => 'CsvLanguageScriptValidator',
                    CsvValidatorResult::TEST_TITLE => CsvLanguageScriptValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Rows with invalid language/script values: 4',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'CSV row numbers where issues were found: 2, 3, 4, 5',
                        'Listing invalid language/script values: Latn | Cyrl, en | fr, en |en, Latn | Arab',
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
