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
      ' ',
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
   * Test csvFileEncodingTest.class.php results.
   **************************************************************************/
  public function testUtf8ValidatorUnixWithBOM()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_utf8_bom.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a UTF-8 BOM.'
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testUtf8ValidatorUnix()
  {
    $filename = $this->vfs->url() . '/unix_csv_without_utf8_bom.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testUtf8ValidatorWindowsWithBOM()
  {
    $filename = $this->vfs->url() . '/windows_csv_with_utf8_bom.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a UTF-8 BOM.'
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testUtf8ValidatorWindows()
  {
    $filename = $this->vfs->url() . '/windows_csv_without_utf8_bom.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testUtf8IncompatibleUnix()
  {
    $filename = $this->vfs->url() . '/unix_csv-windows_1252.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedDetail = [
      implode(',', str_getcsv(mb_convert_encoding($this->csvData[2], "Windows-1252", "UTF-8"))),
    ];
    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding does not appear to be UTF-8 compatible.',
          ],
          CsvBaseTest::TEST_DETAIL => $expectedDetail,
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testUtf8IncompatibleWindows()
  {
    $filename = $this->vfs->url() . '/windows_csv-windows_1252.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedDetail = [
      implode(',', str_getcsv(mb_convert_encoding($this->csvData[2], "Windows-1252", "UTF-8"))),
    ];
    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding does not appear to be UTF-8 compatible.',
          ],
          CsvBaseTest::TEST_DETAIL => $expectedDetail,
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }


  public function testDetectUtf16LEBomUnix()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_utf16LE_bom.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a unicode BOM, but it is not UTF-8.',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testDetectUtf16BEBomUnix()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_utf16BE_bom.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a unicode BOM, but it is not UTF-8.',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testDetectUtf32LEBomUnix()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_utf32LE_bom.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a unicode BOM, but it is not UTF-8.',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testDetectUtf32BEBomUnix()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_utf32BE_bom.csv';
    $testName = 'CsvFileEncodingTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvFileEncodingTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvFileEncodingTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvFileEncodingTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'File encoding is UTF-8 compatible.',
            'This file includes a unicode BOM, but it is not UTF-8.',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  /**************************************************************************
   * Test csvSampleColumnsTest.class.php
   * 
   * CSV Sample Values test. Outputs column names and a sample value from first
   * populated row found. Only populated columns are included.
   **************************************************************************/
  public function testSampleValues()
  {
    $filename = $this->vfs->url() . '/unix_csv_without_utf8_bom.csv';
    $testName = 'CsvSampleColumnsTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvSampleColumnsTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
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
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  /**************************************************************************
   * Test csvColumnCountTest.class.php
   * 
   * Test that all rows including header have the same number of
   * columns/elements.
   * 
   **************************************************************************/
  public function testColumnsEqualLength()
  {
    $filename = $this->vfs->url() . '/unix_csv_without_utf8_bom.csv';
    $testName = 'CsvColumnCountTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName     => CsvColumnCountTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'Number of columns in CSV: 8',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testHeaderTooShort()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_short_header.csv';
    $testName = 'CsvColumnCountTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName     => CsvColumnCountTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 7 columns: 1',
            'Number of rows with 8 columns: 4',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testHeaderTooLong()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_long_header.csv';
    $testName = 'CsvColumnCountTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName     => CsvColumnCountTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 9 columns: 1',
            'Number of rows with 8 columns: 4',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testRowTooShort()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_short_row.csv';
    $testName = 'CsvColumnCountTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName     => CsvColumnCountTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 8 columns: 4',
            'Number of rows with 7 columns: 1',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testRowTooLong()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_long_row.csv';
    $testName = 'CsvColumnCountTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName     => CsvColumnCountTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 8 columns: 4',
            'Number of rows with 9 columns: 1',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testRowsTooShort()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_short_rows.csv';
    $testName = 'CsvColumnCountTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName     => CsvColumnCountTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 8 columns: 3',
            'Number of rows with 7 columns: 1',
            'Number of rows with 6 columns: 1',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testRowsTooLong()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_long_rows.csv';
    $testName = 'CsvColumnCountTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName     => CsvColumnCountTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvColumnCountTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvColumnCountTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'Number of rows with 8 columns: 3',
            'Number of rows with 11 columns: 1',
            'Number of rows with 9 columns: 1',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  /**************************************************************************
   * Test csvEmptyRowTest.class.php
   *
   * Test if the header or any rows are empty.
   *
   **************************************************************************/
  public function testNoEmptyRows()
  {
    $filename = $this->vfs->url() . '/unix_csv_without_utf8_bom.csv';
    $testName = 'CsvEmptyRowTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvEmptyRowTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_INFO,
          CsvBaseTest::TEST_RESULTS => [
            'CSV does not have any blank rows.',
          ],
          CsvBaseTest::TEST_DETAIL => array(),
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testEmptyRows()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_empty_rows.csv';
    $testName = 'CsvEmptyRowTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvEmptyRowTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'CSV blank row count: 2',
          ],
          CsvBaseTest::TEST_DETAIL => [
            'Blank row numbers: 3, 6'
          ],
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testEmptyRowsWithCommas()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_empty_rows_with_commas.csv';
    $testName = 'CsvEmptyRowTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvEmptyRowTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'CSV blank row count: 2',
          ],
          CsvBaseTest::TEST_DETAIL => [
            'Blank row numbers: 3, 5'
          ],
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testEmptyHeader()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_empty_rows_header.csv';
    $testName = 'CsvEmptyRowTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvEmptyRowTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'CSV Header is blank.',
            'CSV blank row count: 2',
          ],
          CsvBaseTest::TEST_DETAIL => [
            'Blank row numbers: 3, 6'
          ],
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }

  public function testEmptyHeaderWithCommas()
  {
    $filename = $this->vfs->url() . '/unix_csv_with_empty_rows_header_with_commas.csv';
    $testName = 'CsvEmptyRowTest';

    $csvValidator = new CsvImportValidator($this->context, null, null);
    $csvValidator->setCsvTests(
      [
        $testName        => CsvEmptyRowTest::class,
      ]
    );
    $csvValidator->setFilenames(explode(",", $filename));
    $csvValidator->setVerbose(true);
    $csvValidator->validate();

    $results = $csvValidator->getResults();

    $expectedOutput = [
      $filename =>
      [ $testName =>
        [
          CsvBaseTest::TEST_TITLE => CsvEmptyRowTest::TITLE,
          CsvBaseTest::TEST_STATUS => CsvEmptyRowTest::RESULT_ERROR,
          CsvBaseTest::TEST_RESULTS => [
            'CSV Header is blank.',
            'CSV blank row count: 2',
          ],
          CsvBaseTest::TEST_DETAIL => [
            'Blank row numbers: 3, 5'
          ],
        ]
      ]
    ];

    $this->assertSame($expectedOutput, $results);
  }


/*
  public function testMultiFile()
  {

  }
*/
}

