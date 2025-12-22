<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvAccessionTitleLengthValidator
 */
class CsvAccessionTitleLengthTest extends \PHPUnit\Framework\TestCase
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

        $this->csvHeader = 'accessionNumber,acquisitionDate,sourceOfAcquisition,title,culture';

        $this->csvData = [
            '"","","","Valid Title","en"',
            '"","","","Another Valid Title","en"',
        ];

        $this->csvInvalidData = [
            '"","","","Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis s","en"',
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

        $validatorOptions['className'] = 'QubitAccession';

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
             * Test CsvAccessionTitleLengthValidator.class.php
             *
             * Tests:
             * - Valid title
             * - Title field with more than 255 characters
             */
            [
                'CsvAccessionTitleLengthValidator-LengthCheckValid' => [
                    'csvValidatorClasses' => 'CsvAccessionTitleLengthValidator',
                    'filename' => '/unix_csv_valid.csv',
                    'testname' => 'CsvAccessionTitleLengthValidator',
                    CsvValidatorResult::TEST_TITLE => CsvAccessionTitleLengthValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: title',
                        '\'title\' values that exceed 255 characters: 0',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],
            [
                'CsvAccessionTitleLengthValidator-LengthCheckInvalid' => [
                    'csvValidatorClasses' => 'CsvAccessionTitleLengthValidator',
                    'filename' => '/unix_csv_invalid.csv',
                    'testname' => 'CsvAccessionTitleLengthValidator',
                    CsvValidatorResult::TEST_TITLE => CsvAccessionTitleLengthValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: title',
                        '\'title\' column may have invalid values.',
                        '\'title\' values that exceed 255 characters: 1',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'title column value: Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis s',
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
