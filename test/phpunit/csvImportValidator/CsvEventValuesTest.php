<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvEventValuesValidator
 */
class CsvEventValuesTest extends \PHPUnit\Framework\TestCase
{
    protected $vdbcon;
    protected $context;

    public function setUp(): void
    {
        $this->context = sfContext::getInstance();
        $this->vdbcon = $this->createMock(DebugPDO::class);

        $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';
        $this->csvHeaderWithEventType = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,eventTypes,eventDates,eventStartDates,eventEndDates,repository,culture';
        $this->csvHeaderWithAllEventCols = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,eventTypes,eventDates,eventStartDates,eventEndDates,eventActors,eventActorHistories,eventPlaces,repository,culture';

        $this->csvData = [
            // Note: leading and trailing whitespace in first row is intentional
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
            '"","","","Chemise","","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
        ];

        $this->csvDataWithEventType = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","creation","1922-1925","1922","1925","",""',
            '"","","","Chemise","","","creation","2010","01-01-2010","12-12-2010","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "creation","2020-2021","Jan 1, 2020","Dec 31 2021", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","","creation", "1900-1999",1900,1999, "", "en"',
        ];

        $this->csvDataWithEventTypeMismatches = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","creation","1922-1925",,"1925","",""',
            '"","","","Chemise","","","creation|donation","2010","01-01-2010","","","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", ,"2020-2021","Jan 1, 2020","Dec 31 2021", "", ""',
            '"", "DJ003", "ID4", "Title Four", "","","creation", "1900-1999",1900,1999, "", "en"',
        ];

        $this->csvDataWithAllEventCols = [
            '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","creation","1922-1925","1922","1925",S. Smith,Smith history., Chilliwack, BC,"",""',
            '"","","","Chemise","","","creation","2010","01-01-2010","12-12-2010",,,,"","fr"',
            '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "creation","2020-2021","Jan 1, 2020","Dec 31 2021",,,, "", ""',
            '"", "DJ003", "ID4", "Title Four", "","","creation|donation", "1900|1999",1900|1901,1999|2000,,,, "", "en"',
        ];

        // define virtual file system
        $directory = [
            'unix_csv_without_utf8_bom.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'unix_csv_with_event_type.csv' => $this->csvHeaderWithEventType."\n".implode("\n", $this->csvDataWithEventType),
            'unix_csv_with_event_type_mismatches.csv' => $this->csvHeaderWithEventType."\n".implode("\n", $this->csvDataWithEventTypeMismatches),
            'unix_csv_with_event_type_all_cols.csv' => $this->csvHeaderWithAllEventCols."\n".implode("\n", $this->csvDataWithAllEventCols),
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
             * Test CsvEventValuesValidator.class.php
             *
             * Tests:
             * - event columns missing
             * - subset of event columns present w. each field populated with same # of values
             * - subset of event columns present w. each field populated with different # of values
             * - all event columns present w. each field populated with same # (> 1) of values
             */
            [
                'CsvEventValuesValidator-EventColsMissing' => [
                    'csvValidatorClasses' => 'CsvEventValuesValidator',
                    'filename' => '/unix_csv_without_utf8_bom.csv',
                    'testname' => 'CsvEventValuesValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEventValuesValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'No event columns to check.',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvEventValuesValidator-WithEventTypeAndDates' => [
                    'csvValidatorClasses' => 'CsvEventValuesValidator',
                    'filename' => '/unix_csv_with_event_type.csv',
                    'testname' => 'CsvEventValuesValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEventValuesValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: eventTypes,eventDates,eventStartDates,eventEndDates',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                    ],
                ],
            ],

            [
                'CsvEventValuesValidator-WithEventTypeAndDateMismatches' => [
                    'csvValidatorClasses' => 'CsvEventValuesValidator',
                    'filename' => '/unix_csv_with_event_type_mismatches.csv',
                    'testname' => 'CsvEventValuesValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEventValuesValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_WARN,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: eventTypes,eventDates,eventStartDates,eventEndDates',
                        'Event value mismatches found: 1',
                    ],
                    CsvValidatorResult::TEST_DETAILS => [
                        'CSV row numbers where issues were found: 3',
                    ],
                ],
            ],

            [
                'CsvEventValuesValidator-WithEventTypeAllColsMatching' => [
                    'csvValidatorClasses' => 'CsvEventValuesValidator',
                    'filename' => '/unix_csv_with_event_type_all_cols.csv',
                    'testname' => 'CsvEventValuesValidator',
                    CsvValidatorResult::TEST_TITLE => CsvEventValuesValidator::TITLE,
                    CsvValidatorResult::TEST_STATUS => CsvValidatorResult::RESULT_INFO,
                    CsvValidatorResult::TEST_RESULTS => [
                        'Checking columns: eventTypes,eventDates,eventStartDates,eventEndDates,eventActors,eventActorHistories,eventPlaces',
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
