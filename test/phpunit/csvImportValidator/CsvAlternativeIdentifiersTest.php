<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvAlternativeIdentifiersValidator
 */
class CsvAlternativeIdentifiersTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;
    protected $vfs;
    protected $csvHeader;
    protected $csvData;
    protected $csvDataWithoutAltIdColumns;
    protected $csvDataWithMatchingCounts;
    protected $csvDataWithMismatchedCounts;
    protected $csvDataWithOneColumnPopulated;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(PropelPDO::class);

        $this->csvHeader = 'identifier,title,alternativeIdentifiers,alternativeIdentifierLabels';

        $this->csvDataWithoutAltIdColumns = [
            '"001","Title 001"',
            '"002","Title 002"',
        ];

        $this->csvDataWithMatchingCounts = [
            '"003","Title 003","ID 1|ID 2|ID 3","Label 1|Label 2|Label 3"',
            '"004","Title 004","Id","Label"',
        ];

        $this->csvDataWithMismatchedCounts = [
            '"005","Title 005","ID 1|ID 2|ID 3","Label 1"',
            '"006","Title 006","Id","Label A|Label B"',
        ];

        $this->csvDataWithOneColumnPopulated = [
            '"007","Title 005","ID 1|ID 2",""',
            '"008","Title 006","","Label A|Label B"',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_alt_id_columns.csv' => 'identifier,title'."\n".implode("\n", $this->csvDataWithoutAltIdColumns),
            'unix_csv_with_matching_counts.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataWithMatchingCounts),
            'unix_csv_with_mismatched_counts.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataWithMismatchedCounts),
            'unix_csv_with_one_column_populated.csv' => $this->csvHeader."\n".implode("\n", $this->csvDataWithOneColumnPopulated),
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
             * Test CsvAlternativeIdentifiersValidator.class.php
             *
             * Tests:
             * - alternativeIdentifiers columns missing
             * - both columns present with matching number of values
             * - both columns present with mismatched number of values
             * - values in only one of the two columns
             */
            [
                'CsvAlternativeIdentifiersValidator-AltIdColumnsMissing' => [
                    'csvValidatorClasses' => 'CsvAlternativeIdentifiersValidator',
                    'filename' => '/unix_csv_without_alt_id_columns.csv',
                    'testname' => 'CsvAlternativeIdentifiersValidator',
                    CsvValidatorResult::TEST_TITLE => CsvAlternativeIdentifiersValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'No alternativeIdentifier columns to check.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvAlternativeIdentifiersValidator-MatchingCounts' => [
                    'csvValidatorClasses' => 'CsvAlternativeIdentifiersValidator',
                    'filename' => '/unix_csv_with_matching_counts.csv',
                    'testname' => 'CsvAlternativeIdentifiersValidator',
                    CsvValidatorResult::TEST_TITLE => CsvAlternativeIdentifiersValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: alternativeIdentifiers,alternativeIdentifierLabels',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvAlternativeIdentifiersValidator-MismatchedCounts' => [
                    'csvValidatorClasses' => 'CsvAlternativeIdentifiersValidator',
                    'filename' => '/unix_csv_with_mismatched_counts.csv',
                    'testname' => 'CsvAlternativeIdentifiersValidator',
                    CsvValidatorResult::TEST_TITLE => CsvAlternativeIdentifiersValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: alternativeIdentifiers,alternativeIdentifierLabels',
                        "Rows where 'alternativeIdentifiers' and 'alternativeIdentifierLabels' value counts do not match: 2",
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'CSV row numbers where value counts do not match: 2, 3',
                        "CSV import will fail with error 'Number of alternative ids does not match number of alt id labels' on these rows.",
                    ],
                ],
            ],

            [
                'CsvAlternativeIdentifiersValidator-OneColumnPopulated' => [
                    'csvValidatorClasses' => 'CsvAlternativeIdentifiersValidator',
                    'filename' => '/unix_csv_with_one_column_populated.csv',
                    'testname' => 'CsvAlternativeIdentifiersValidator',
                    CsvValidatorResult::TEST_TITLE => CsvAlternativeIdentifiersValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: alternativeIdentifiers,alternativeIdentifierLabels',
                        "Rows where only one of 'alternativeIdentifiers' and 'alternativeIdentifierLabels' has values: 2",
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'CSV row numbers where values in only one column were found: 2, 3',
                        'Alternative identifiers in these rows will not be imported.',
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
