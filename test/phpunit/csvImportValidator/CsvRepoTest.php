<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvRepoValidator
 */
class CsvRepoTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(DebugPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,culture';
        $this->csvHeaderWithRepo = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderDupedRepo = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture,repository';

        $this->csvData = [
            // Note: leading and trailing whitespace in first row is intentional
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1",""',
            '"","","","Chemise","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "en"',
        ];

        $this->csvDataValidRepos = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","es "',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", "de"',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataReposSomeInvalid = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","new repo 1","es "',
            '"","","","Chemise","","","Existing Repository","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "new repo 2", "de"',
            '"", "DJ003", "ID4", "Title Four", "","", "new repo 1", "en"',
        ];

        $this->csvDataDupedRepo = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","new repo 1","es ",""',
            '"","","","Chemise","","","Existing Repository","fr", ""',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "new repo 2", "de", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "new repo 1", "en",""',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_utf8_bom.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'unix_csv_valid_repos.csv' => $this->csvHeaderWithRepo."\n".implode("\n", $this->csvDataValidRepos),
            'unix_csv_repos_some_invalid.csv' => $this->csvHeaderWithRepo."\n".implode("\n", $this->csvDataReposSomeInvalid),
            'unix_csv_duped_repo.csv' => $this->csvHeaderDupedRepo."\n".implode("\n", $this->csvDataDupedRepo),
        ];

        $this->vfs = vfsStream::setup('root', null, $directory);

        $this->ormClasses = [
            'QubitFlatfileImport' => \AccessToMemory\test\mock\QubitFlatfileImport::class,
            // 'QubitObject' => \AccessToMemory\test\mock\QubitObject::class,
        ];
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
             * Test CsvRepoValidator.class.php
             *
             * Tests:
             * - repository column missing
             * - repository column present with valid data
             * - repository column present with mix of valid and invalid data
             */
            [
                'CsvRepoValidator-RepoColMissing' => [
                    'csvValidatorClasses' => 'CsvRepoValidator',
                    'filename' => '/unix_csv_without_utf8_bom.csv',
                    'testname' => 'CsvRepoValidator',
                    CsvValidatorResult::TEST_TITLE => CsvRepoValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        "'repository' column not present in file.",
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvRepoValidator-ReposValid' => [
                    'csvValidatorClasses' => 'CsvRepoValidator',
                    'filename' => '/unix_csv_valid_repos.csv',
                    'testname' => 'CsvRepoValidator',
                    CsvValidatorResult::TEST_TITLE => CsvRepoValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'No issues detected with repository values.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvRepoValidator-ReposSomeInvalid' => [
                    'csvValidatorClasses' => 'CsvRepoValidator',
                    'filename' => '/unix_csv_repos_some_invalid.csv',
                    'testname' => 'CsvRepoValidator',
                    CsvValidatorResult::TEST_TITLE => CsvRepoValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Number of NEW repository records that will be created by this CSV: 2',
                        'New repository records will be created for: new repo 1,new repo 2',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'CSV row numbers where issues were found: 2, 4, 5',
                    ],
                ],
            ],

            [
                'CsvRepoValidator-DupedRepo' => [
                    'csvValidatorClasses' => 'CsvRepoValidator',
                    'filename' => '/unix_csv_duped_repo.csv',
                    'testname' => 'CsvRepoValidator',
                    CsvValidatorResult::TEST_TITLE => CsvRepoValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_ERROR,
                    CsvValidatorResult::TEST_RESULTS => [
                        '\'repository\' column appears more than once in file.',
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
        $csvValidator->setOrmClasses($this->ormClasses);

        return $csvValidator->validate();
    }
}
