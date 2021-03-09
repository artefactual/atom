<?php

use org\bovigo\vfs\vfsStream;

class CsvImportValidatorTest extends \PHPUnit\Framework\TestCase
{
  protected $vdbcon;
  protected $context;

  public function setUp() : void
  {
    $this->context = sfContext::getInstance();
    $this->vdbcon = $this->createMock(DebugPDO::class);

    $this->csvHeader = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture';

    $this->csvHeaderShort = 'legacyId,parentId,identifier,title,levelOfDescription,repository,culture';
    $this->csvHeaderLong = 'legacyId,parentId,identifier,title,levelOfDescription,extentAndMedium,repository,culture,extraHeading';
    $this->csvHeaderBlank = '';
    $this->csvHeaderBlankWithCommas = ',,,';

    $this->csvHeaderWithUtf8Bom = CsvImportValidator::UTF8_BOM . $this->csvHeader;
    $this->csvHeaderWithUtf16LEBom = CsvImportValidator::UTF16_LITTLE_ENDIAN_BOM . $this->csvHeader;
    $this->csvHeaderWithUtf16BEBom = CsvImportValidator::UTF16_BIG_ENDIAN_BOM . $this->csvHeader;
    $this->csvHeaderWithUtf32LEBom = CsvImportValidator::UTF32_LITTLE_ENDIAN_BOM . $this->csvHeader;
    $this->csvHeaderWithUtf32BEBom = CsvImportValidator::UTF32_BIG_ENDIAN_BOM . $this->csvHeader;

    $this->csvData = array(
      // Note: leading and trailing whitespace in first row is intentional
      '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
      '"","","","Chemise","","","","fr"',
      '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
      '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
    );

    $this->csvDataShortRow = array(
      '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
      '"","","","Chemise ","","","fr"',  // Short row: 7 cols
      '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
      '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
    );

    $this->csvDataShortRows = array(
      '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
      '"","","","Chemise ","","","fr"',  // Short row: 7 cols
      '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
      '"", "DJ003", "ID4", "Title Four", "", "en"',  // Short row: 6 cols
      '', // Short row: zero cols
    );

    $this->csvDataLongRow = array(
      '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
      '"","","","Chemise ","","", "","fr", ""',  // Long row: 9 cols
      '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
      '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
    );

    $this->csvDataLongRows = array(
      '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","","","","",', // Long row: 12 cols
      '"","","","Chemise ","","", "","fr", ""',  // Long row: 9 cols
      '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
      '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
    );

    $this->csvDataEmptyRows = array(
      // Note: leading and trailing whitespace in first row is intentional
      '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
      '',
      '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
      '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
      '  , ',
      ' ',
      '',
    );

    $this->csvDataEmptyRowsWithCommas = array(
      // Note: leading and trailing whitespace in first row is intentional
      '"B10101 "," DJ001","ID1 ","Some Photographs","","Extent and medium 1","",""',
      ',,,',
      '"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""',
      '   , , ',
      '"", "DJ003", "ID4", "Title Four", "","", "", "en"',
    );

    // define virtual file system
    $directory = [
      'unix_csv_with_utf8_bom.csv' => $this->csvHeaderWithUtf8Bom . "\n" . implode("\n", $this->csvData),
      'unix_csv_without_utf8_bom.csv' => $this->csvHeader . "\n" . implode("\n", $this->csvData),
      'windows_csv_with_utf8_bom.csv' => $this->csvHeaderWithUtf8Bom . "\r\n" . implode("\r\n", $this->csvData),
      'windows_csv_without_utf8_bom.csv' => $this->csvHeader . "\r\n" . implode("\r\n", $this->csvData),
      'unix_csv-windows_1252.csv' => mb_convert_encoding($this->csvHeader . "\n" . implode("\n", $this->csvData), 'Windows-1252', 'UTF-8'),
      'windows_csv-windows_1252.csv' => mb_convert_encoding($this->csvHeader . "\r\n" . implode("\r\n", $this->csvData), 'Windows-1252', 'UTF-8'),
      'unix_csv_with_utf16LE_bom.csv' => $this->csvHeaderWithUtf16LEBom . "\n" . implode("\n", $this->csvData),
      'unix_csv_with_utf16BE_bom.csv' => $this->csvHeaderWithUtf16BEBom . "\n" . implode("\n", $this->csvData),
      'unix_csv_with_utf32LE_bom.csv' => $this->csvHeaderWithUtf32LEBom . "\n" . implode("\n", $this->csvData),
      'unix_csv_with_utf32BE_bom.csv' => $this->csvHeaderWithUtf32BEBom . "\n" . implode("\n", $this->csvData),
      'unix_csv_with_short_header.csv' => $this->csvHeaderShort . "\n" . implode("\n", $this->csvData),
      'unix_csv_with_long_header.csv' => $this->csvHeaderLong . "\n" . implode("\n", $this->csvData),
      'unix_csv_with_short_row.csv' => $this->csvHeader . "\n" . implode("\n", $this->csvDataShortRow),
      'unix_csv_with_long_row.csv' => $this->csvHeader . "\n" . implode("\n", $this->csvDataLongRow),
      'unix_csv_with_short_rows.csv' => $this->csvHeader . "\n" . implode("\n", $this->csvDataShortRows),
      'unix_csv_with_long_rows.csv' => $this->csvHeader . "\n" . implode("\n", $this->csvDataLongRows),
      'unix_csv_with_empty_rows.csv' => $this->csvHeader . "\n" . implode("\n", $this->csvDataEmptyRows),
      'unix_csv_with_empty_rows_with_commas.csv' => $this->csvHeader . "\n" . implode("\n", $this->csvDataEmptyRowsWithCommas),
      'unix_csv_with_empty_rows_header.csv' => $this->csvHeaderBlank . "\n" . implode("\n", $this->csvDataEmptyRows),
      'unix_csv_with_empty_rows_header_with_commas.csv' => $this->csvHeaderBlankWithCommas . "\n" . implode("\n", $this->csvDataEmptyRowsWithCommas),
      'root.csv' => $this->csvHeader . "\n" . implode("\n", $this->csvData),
    ];

    $this->vfs = vfsStream::setup('root', null, $directory);

    $file = $this->vfs->getChild('root/root.csv');
    $file->chmod('0400');
    $file->chown(vfsStream::OWNER_ROOT);
  }

  /**************************************************************************
   * Data providers
   **************************************************************************/

  public function setOptionsProvider()
  {
  }

  /**************************************************************************
   * Basic tests
   **************************************************************************/

  public function testConstructorWithNoContextPassed()
  {
    $csvValidator = new CsvImportValidator(null, $this->vdbcon, null);

    $this->assertSame(sfContext::class, get_class($csvValidator->getContext()));
  }

  public function testConstructorWithNoDbconPassed()
  {
    $csvValidator = new CsvImportValidator($this->context, null, null);

    $this->assertSame(DebugPDO::class, get_class($csvValidator->getDbCon()));
  }

  public function testSetInvalidOptionsException()
  {
    $this->expectException(UnexpectedValueException::class);
    $options = ['fakeOption'];
    $csvValidator = new CsvImportValidator($this->context, null, $options);
  }

  public function testSetValidImportTypeOption()
  {
    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setOption('importType', 'QubitInformationObject');
    $this->assertSame('QubitInformationObject', $csvValidator->getOption('importType'));
  }

  public function testSetInvalidImportTypeOption()
  {
    $this->expectException(UnexpectedValueException::class);
    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setOption('importType', 'QubitAccession');
  }

  public function testSetValidVerboseTypeOption()
  {
    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setOption('verbose', true);
    $this->assertSame(true, $csvValidator->getOption('verbose'));
  }

  public function testDefaultOptions()
  {
    $csvValidator = new CsvImportValidator($this->context, null, null);
    $this->assertSame(false, $csvValidator->getOption('verbose'));
    $this->assertSame('QubitInformationObject', $csvValidator->getOption('importType'));
  }

  /**************************************************************************
   * Generic Validation
   **************************************************************************/
  protected function runValidator($csvValidator, $filenames, $tests, $verbose = true)
  {
    $csvValidator->setCsvTests($tests);
    $csvValidator->setFilenames(explode(",", $filenames));
    $csvValidator->setVerbose($verbose);

    return $csvValidator->validate();
  }

  /**
   * @dataProvider csvValidatorTestProvider
   * 
   * Generic test - options and expected results from csvValidatorTestProvider()
   */
  public function testCsvValidator($options) //, $expectedResult)
  {
    $filename = $this->vfs->url() . $options['filename'];

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $this->runValidator($csvValidator, $filename, $options['csvValidatorClasses']);
    $result = $csvValidator->getResultsByFilenameTestname($filename, $options['testname']);
    
    $this->assertSame($options[CsvBaseTest::TEST_TITLE], $result[CsvBaseTest::TEST_TITLE]);
    $this->assertSame($options[CsvBaseTest::TEST_STATUS], $result[CsvBaseTest::TEST_STATUS]);
    $this->assertSame($options[CsvBaseTest::TEST_RESULTS], $result[CsvBaseTest::TEST_RESULTS]);
    $this->assertSame($options[CsvBaseTest::TEST_DETAIL], $result[CsvBaseTest::TEST_DETAIL]);
  }

  public function csvValidatorTestProvider()
  {
    $vfsUrl = 'vfs://root';

    $testlist = [
      /**************************************************************************
       * Test csvFileEncodingTest.class.php results.
       **************************************************************************/
      [
        "CsvFileEncodingTest-Utf8ValidatorUnixWithBOM" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/unix_csv_with_utf8_bom.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a UTF-8 BOM.'
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ],
      ],

      [
        "CsvFileEncodingTest-testUtf8ValidatorUnix" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/unix_csv_without_utf8_bom.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvFileEncodingTest-testUtf8ValidatorWindowsWithBOM" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/windows_csv_with_utf8_bom.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a UTF-8 BOM.'
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvFileEncodingTest-testUtf8ValidatorWindows" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/windows_csv_without_utf8_bom.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvFileEncodingTest-testUtf8IncompatibleUnix" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/unix_csv-windows_1252.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding does not appear to be UTF-8 compatible.',
          ],
          CsvBaseTest::TEST_DETAIL => [ implode(',', str_getcsv(mb_convert_encoding('"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""', "Windows-1252", "UTF-8"))) ],
        ],
      ],

      [
        "CsvFileEncodingTest-testUtf8IncompatibleWindows" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/windows_csv-windows_1252.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding does not appear to be UTF-8 compatible.',
          ],
          CsvBaseTest::TEST_DETAIL => [ implode(',', str_getcsv(mb_convert_encoding('"D20202", "DJ002", "", "Voûte, étagère 0074", "", "", "", ""', "Windows-1252", "UTF-8"))) ],
        ],
      ],

      [
        "CsvFileEncodingTest-testDetectUtf16LEBomUnix" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/unix_csv_with_utf16LE_bom.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a unicode BOM, but it is not UTF-8.',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvFileEncodingTest-testDetectUtf16BEBomUnix" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/unix_csv_with_utf16BE_bom.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a unicode BOM, but it is not UTF-8.',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvFileEncodingTest-testDetectUtf32LEBomUnix" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/unix_csv_with_utf32LE_bom.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a unicode BOM, but it is not UTF-8.',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvFileEncodingTest-testDetectUtf32BEBomUnix" => [
          "csvValidatorClasses" => [ 'CsvFileEncodingTest' => CsvFileEncodingTest::class ],
          "filename" => '/unix_csv_with_utf32BE_bom.csv',
          "testname" => 'CsvFileEncodingTest',
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a unicode BOM, but it is not UTF-8.',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      /**************************************************************************
       * Test csvSampleColumnsTest.class.php
       * 
       * CSV Sample Values test. Outputs column names and a sample value from first
       * populated row found. Only populated columns are included.
       **************************************************************************/

      [
        "CsvSampleColumnsTest-testSampleValues" => [
          "csvValidatorClasses" => [ 'CsvSampleColumnsTest' => CsvSampleColumnsTest::class ],
          "filename" => '/unix_csv_without_utf8_bom.csv',
          "testname" => 'CsvSampleColumnsTest',
          CsvBaseTest::TEST_TITLE => CsvSampleColumnsTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvSampleColumnsTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'legacyId:  B10101 ',
            'parentId:   DJ001',
            'identifier:  ID1 ',
            'title:  Some Photographs',
            'extentAndMedium:  Extent and medium 1',
            'culture:  fr',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      /**************************************************************************
       * Test csvColumnCountTest.class.php
       * 
       * Test that all rows including header have the same number of
       * columns/elements.
       * 
       **************************************************************************/

      [
        "CsvColumnCountTest-testColumnsEqualLength" => [
          "csvValidatorClasses" => [ 'CsvColumnCountTest' => CsvColumnCountTest::class ],
          "filename" => '/unix_csv_without_utf8_bom.csv',
          "testname" => 'CsvColumnCountTest',
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'Number of columns in CSV: 8',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvColumnCountTest-testHeaderTooShort" => [
          "csvValidatorClasses" => [ 'CsvColumnCountTest' => CsvColumnCountTest::class ],
          "filename" => '/unix_csv_with_short_header.csv',
          "testname" => 'CsvColumnCountTest',
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 7 columns: 1',
            'Number of rows with 8 columns: 4',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvColumnCountTest-testHeaderTooLong" => [
          "csvValidatorClasses" => [ 'CsvColumnCountTest' => CsvColumnCountTest::class ],
          "filename" => '/unix_csv_with_long_header.csv',
          "testname" => 'CsvColumnCountTest',
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 9 columns: 1',
            'Number of rows with 8 columns: 4',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvColumnCountTest-testRowTooShort" => [
          "csvValidatorClasses" => [ 'CsvColumnCountTest' => CsvColumnCountTest::class ],
          "filename" => '/unix_csv_with_short_row.csv',
          "testname" => 'CsvColumnCountTest',
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 8 columns: 4',
            'Number of rows with 7 columns: 1',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvColumnCountTest-testRowTooLong" => [
          "csvValidatorClasses" => [ 'CsvColumnCountTest' => CsvColumnCountTest::class ],
          "filename" => '/unix_csv_with_long_row.csv',
          "testname" => 'CsvColumnCountTest',
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 8 columns: 4',
            'Number of rows with 9 columns: 1',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvColumnCountTest-testRowsTooShort" => [
          "csvValidatorClasses" => [ 'CsvColumnCountTest' => CsvColumnCountTest::class ],
          "filename" => '/unix_csv_with_short_rows.csv',
          "testname" => 'CsvColumnCountTest',
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 8 columns: 3',
            'Number of rows with 7 columns: 1',
            'Number of rows with 6 columns: 1',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvColumnCountTest-testRowsTooLong" => [
          "csvValidatorClasses" => [ 'CsvColumnCountTest' => CsvColumnCountTest::class ],
          "filename" => '/unix_csv_with_long_rows.csv',
          "testname" => 'CsvColumnCountTest',
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 8 columns: 3',
            'Number of rows with 11 columns: 1',
            'Number of rows with 9 columns: 1',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      /**************************************************************************
       * Test csvEmptyRowTest.class.php
       *
       * Test if the header or any rows are empty.
       *
       **************************************************************************/

      [
        "CsvEmptyRowTest-testNoEmptyRows" => [
          "csvValidatorClasses" => [ 'CsvEmptyRowTest' => CsvEmptyRowTest::class ],
          "filename" => '/unix_csv_with_long_rows.csv',
          "testname" => 'CsvEmptyRowTest',
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'CSV does not have any blank rows.',
          ],
          CsvBaseTest::TEST_DETAIL => [],
        ],
      ],

      [
        "CsvEmptyRowTest-testEmptyRows" => [
          "csvValidatorClasses" => [ 'CsvEmptyRowTest' => CsvEmptyRowTest::class ],
          "filename" => '/unix_csv_with_empty_rows.csv',
          "testname" => 'CsvEmptyRowTest',
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'CSV blank row count: 2',
          ],
          CsvBaseTest::TEST_DETAIL => [
            'Blank row numbers: 3, 6',
          ],
        ],
      ],

      [
        "CsvEmptyRowTest-testEmptyRowsWithCommas" => [
          "csvValidatorClasses" => [ 'CsvEmptyRowTest' => CsvEmptyRowTest::class ],
          "filename" => '/unix_csv_with_empty_rows_with_commas.csv',
          "testname" => 'CsvEmptyRowTest',
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'CSV blank row count: 2',
          ],
          CsvBaseTest::TEST_DETAIL => [
            'Blank row numbers: 3, 5',
          ],
        ],
      ],

      [
        "CsvEmptyRowTest-testEmptyHeader" => [
          "csvValidatorClasses" => [ 'CsvEmptyRowTest' => CsvEmptyRowTest::class ],
          "filename" => '/unix_csv_with_empty_rows_header.csv',
          "testname" => 'CsvEmptyRowTest',
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'CSV Header is blank.',
            'CSV blank row count: 2',
          ],
          CsvBaseTest::TEST_DETAIL => [
            'Blank row numbers: 3, 6',
          ],
        ],
      ],

      [
        "CsvEmptyRowTest-EmptyRowsAndHeader" => [
          "csvValidatorClasses" => [ 'CsvEmptyRowTest' => CsvEmptyRowTest::class ],
          "filename" => '/unix_csv_with_empty_rows_header_with_commas.csv',
          "testname" => 'CsvEmptyRowTest',
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'CSV Header is blank.',
            'CSV blank row count: 2',
          ],
          CsvBaseTest::TEST_DETAIL => [
            'Blank row numbers: 3, 5'
          ],
        ],
      ],

      // testMultiFile

    ];

    return $testlist;
  }
}
