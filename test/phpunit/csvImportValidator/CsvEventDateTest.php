<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvFieldLengthValidator
 */
class CsvEventDateTest extends \PHPUnit\Framework\TestCase
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

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,eventDates,eventTypes,eventStartDates,eventEndDates,repository,culture';

        $this->csvData = [
            '"","","","title","","","1990","Creation","1990","1991","","en"',
            '"","","","title","","","1992","Accumulation","1992-01","1992-03-04","","en"',
            '"","","","another title","","","1995","Creation","19950101","","","en"',
            '"","","","yet another title","","","1997|1998","Creation|Accumulation","1997-01-01|1998-01-01","","","en"',
        ];

        $this->csvInvalidData = [
            '"","","","title","","","1990?","Creation","1990-?","1991","","en"',
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
                'CsvEventDateValidator-LengthCheckValid' => [
                    'csvValidatorClasses' => 'CsvEventDateValidator',
                    'filename' => '/unix_csv_valid.csv',
                    'testname' => 'CsvEventDateValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEventDateValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        "All ''eventStartDates' and 'eventEndDates' columns contain dates in a valid format.",
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],
            [
                'CsvEventDateValidator-LengthCheckValid' => [
                    'csvValidatorClasses' => 'CsvEventDateValidator',
                    'filename' => '/unix_csv_invalid.csv',
                    'testname' => 'CsvEventDateValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEventDateValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        "Rows with invalid event date values: 1",
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                      "CSV row numbers where issues were found: 2",
                      "Listing invalid date values: 1990-?"
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
