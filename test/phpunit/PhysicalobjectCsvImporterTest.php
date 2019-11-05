<?php

use org\bovigo\vfs\vfsStream;

class PhysicalObjectCsvImporterTest extends \PHPUnit\Framework\TestCase
{
  protected $csvHeader;
  protected $csvData;
  protected $typeIdLookupTable;
  protected $ormClasses;
  protected $vfs;               // virtual filesystem
  protected $vdbcon;            // virtual database connection


  /**************************************************************************
   * Fixtures
   **************************************************************************/

  public function setUp() : void
  {
    $this->context = sfContext::getInstance();
    $this->vdbcon = $this->createMock(DebugPDO::class);
    $this->ormClasses = [
      'informationObject' => \AccessToMemory\test\mock\QubitInformationObject::class,
      'keymap'            => \AccessToMemory\test\mock\QubitKeymap::class,
      'physicalObject'    => \AccessToMemory\test\mock\QubitPhysicalObject::class,
      'relation'          => \AccessToMemory\test\mock\QubitRelation::class,
    ];

    $this->csvHeader = 'legacyId,name,type,location,culture,descriptionSlugs';

    $this->csvData = array(
      // Note: leading and trailing whitespace first rowis intentional
      '"B10101 "," DJ001","Folder "," Aisle 25,Shelf D"," en","test-fonds-1 | test-collection"',
      '"","","Chemise","","fr",""',
      '"", "DJ002", "Boîte Hollinger", "Voûte, étagère 0074", "fr", "Mixed-Case-Fonds|no-match|"',
      '"", "DJ003", "Hollinger box", "Aisle 11, Shelf J", "en", ""',
    );

    $this->typeIdLookupTableFixture = [
      'en' => [
        'hollinger box' => 1,
        'folder' => 2,
      ],
      'fr' => [
        'boîte hollinger' => 1,
        'chemise' => 2,
      ]
    ];

    // define virtual file system
    $directory = [
      'unix.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
      'windows.csv' => $this->csvHeader."\r\n".implode("\r\n", $this->csvData)
        ."\r\n",
      'noheader.csv' => implode("\n", $this->csvData)."\n",
      'duplicate.csv' => $this->csvHeader."\n".implode("\n",
        $this->csvData + $this->csvData),
      'root.csv' => $this->csvData[0],
      'error.log' => '',
    ];

    // setup and cache the virtual file system
    $this->vfs = vfsStream::setup('root', null, $directory);

    // Make 'root.csv' owned and readable only by root user
    $file = $this->vfs->getChild('root/root.csv');
    $file->chmod('0400');
    $file->chown(vfsStream::OWNER_ROOT);
  }

  public function getCsvRowAsAssocArray($row = 0)
  {
    return array_combine(
      explode(',', $this->csvHeader), str_getcsv($this->csvData[$row]));
  }


  /**************************************************************************
   * Data providers
   **************************************************************************/

  public function setOptionsProvider()
  {
    return [
      [
        ['option1' => 'value1'],
        ['option1' => 'value1'],
      ],
      [
        ['option1' => 'value1', 'option2' => 'value2'],
        ['option1' => 'value1', 'option2' => 'value2'],
      ],
      [null, array()],
      [array(), array()],
    ];
  }

  public function processRowProvider()
  {
    $inputs = [
      // Leading and trailing whitespace is intentional
      [
        'legacyId'         => 'B10101 ',
        'name'             => ' DJ001',
        'type'             => 'Boîte Hollinger ',
        'location'         => ' Voûte, étagère 0074',
        'culture'          => 'fr ',
        'descriptionSlugs' => ' test-fonds-1 | test-collection ',
      ],
      [
        'legacyId'         => ' ',
        'name'             => 'DJ002 ',
        'type'             => 'Folder',
        'location'         => 'Aisle 25, Shelf D',
        // Test case insensitivity (should match 'en')
        'culture'          => 'EN',
        // Slugs are case sensitive
        'descriptionSlugs' => '|Mixed-Case-Fonds|no-match|',
      ],
      [
        'name'             => 'DJ003',
        'location'         => 'Aisle 11, Shelf J',
      ],
      [
        'legacyId'         => '',
        'name'             => 'DJ004',
        'type'             => '',
        'location'         => '',
        'culture'          => '',
        'descriptionSlugs' => '',
      ],
    ];

    $expectedResults = [
      [
        'legacyId'             => 'B10101',
        'name'                 => 'DJ001',
        'typeId'               => 1,
        'location'             => 'Voûte, étagère 0074',
        'culture'              => 'fr',
        'informationObjectIds' => [111111, 222222],
      ],
      [
        'legacyId'             => null,
        'name'                 => 'DJ002',
        'typeId'               => 2,
        'location'             => 'Aisle 25, Shelf D',
        'culture'              => 'en',
        'informationObjectIds' => [333333],
      ],
      [
        'legacyId'             => null,
        'name'                 => 'DJ003',
        'typeId'               => null,
        'location'             => 'Aisle 11, Shelf J',
        'culture'              => 'en',
        'informationObjectIds' => [],
      ],
      [
        'legacyId'             => null,
        'name'                 => 'DJ004',
        'typeId'               => null,
        'location'             => null,
        'culture'              => 'en',
        'informationObjectIds' => [],
      ],
    ];

    return [
      [$inputs[0], $expectedResults[0]],
      [$inputs[1], $expectedResults[1]],
      [$inputs[2], $expectedResults[2]],
      [$inputs[3], $expectedResults[3]],
    ];
  }


  /**************************************************************************
   * Tests
   **************************************************************************/

  public function testConstructorWithNoContextPassed()
  {
    $importer = new PhysicalObjectCsvImporter(null, $this->vdbcon);

    $this->assertSame(sfContext::class, get_class($importer->context));
  }

  public function testConstructorWithNoDbconPassed()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, null);

    $this->assertSame(DebugPDO::class, get_class($importer->dbcon));
  }

  public function testMagicGetInvalidPropertyException()
  {
    $this->expectException(sfException::class);
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $foo = $importer->blah;
  }

  public function testMagicSetInvalidPropertyException()
  {
    $this->expectException(sfException::class);
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->foo = 'blah';
  }

  public function testSetAndGetMultiValueDelimiter()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->multiValueDelimiter = '/';
    $this->assertSame('/', $importer->multiValueDelimiter);
  }

  public function testSetFilenameFileNotFoundException()
  {
    $this->expectException(sfException::class);
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setFilename('bad_name.csv');
  }

  public function testSetFilenameFileUnreadableException()
  {
    $this->expectException(sfException::class);
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setFilename($this->vfs->url().'/root.csv');
  }

  public function testSetFilenameSuccess()
  {
    // Explicit method call
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setFilename($this->vfs->url().'/unix.csv');
    $this->assertSame($this->vfs->url().'/unix.csv', $importer->getFilename());

    // Magic __set
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setFilename($this->vfs->url().'/windows.csv');
    $this->assertSame($this->vfs->url().'/windows.csv', $importer->getFilename());
  }

  /**
   * @dataProvider setOptionsProvider
   */
  public function testSetOptions($options, $expected)
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setOptions($options);
    $this->assertSame($expected, $importer->getOptions());
  }

  public function testSetOptionsThrowsTypeError()
  {
    $this->expectException(TypeError::class);

    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setOptions(1);
    $importer->setOptions(new stdClass);
  }

  public function testSetAndGetUpdateOnMatch()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);

    $importer->setUpdateOnMatch(true);
    $this->assertSame(true, $importer->getUpdateOnMatch());

    // Test boolean casting
    $importer->setUpdateOnMatch(0);
    $this->assertSame(false, $importer->getUpdateOnMatch());
  }

  public function testSetUpdateSearchIndex()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);

    $importer->setUpdateSearchIndex(true);
    $this->assertSame(true, $importer->getUpdateSearchIndex());

    // Test boolean casting
    $importer->setUpdateSearchIndex(1);
    $this->assertSame(true, $importer->getUpdateSearchIndex());

    $importer->setUpdateSearchIndex(null);
    $this->assertSame(false, $importer->getUpdateSearchIndex());
  }

  public function testSetUpdateSearchIndexFromOptions()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setOptions(['updateSearchIndex' => true]);

    $this->assertSame(true, $importer->getUpdateSearchIndex());
  }

  public function testSetAndGetOption()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setOption('sourceName', 'test-001');

    $this->assertSame('test-001', $importer->getOption('sourceName'));
  }

  public function testSetOptionFromOptions()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setOptions([
      'header'            => 'name,location,type,culture',
      'offset'            => 1,
      'sourceName'        => 'test-002',
      'updateSearchIndex' => true,
    ]);

    $this->assertSame(1, $importer->getOffset());
    $this->assertSame('test-002', $importer->getOption('sourceName'));
    $this->assertSame(true, $importer->getUpdateSearchIndex());
    $this->assertSame(
      ['name', 'location', 'type', 'culture'],
      $importer->getHeader()
    );
  }

  public function testSetAndGetOffset()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $this->assertSame(0, $importer->getOffset());

    $importer->setOffset(1);
    $this->assertSame(1, $importer->getOffset());
  }

  public function testSetAndGetHeader()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $this->assertSame(null, $importer->getHeader());

    $importer->setHeader('name ,location, type, culture');
    $this->assertSame(
      ['name', 'location', 'type', 'culture'],
      $importer->getHeader()
    );
  }

  public function testSetHeaderThrowsExceptionOnEmptyHeader()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);

    $this->expectException(sfException::class);
    $importer->setHeader(',');
  }

  public function testSetHeaderThrowsExceptionOnInvalidColumnName()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);

    $this->expectException(sfException::class);
    $importer->setHeader('foo');
  }

  public function testSetAndGetProgressFrequency()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $this->assertSame(1, $importer->getProgressFrequency());

    $importer->setProgressFrequency(10);
    $this->assertSame(10, $importer->getProgressFrequency());
  }

  public function testSourceNameDefaultsToFilename()
  {
    $filename = $this->vfs->url().'/unix.csv';
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setFilename($filename);

    $this->assertSame(basename($filename), $importer->getOption('sourceName'));
  }

  public function testGetHeaderReturnsNullBeforeImport()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);

    $this->assertSame(null, $importer->getHeader());
  }

  public function testDoImportNoFilenameException()
  {
    $this->expectException(sfException::class);

    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->doImport();
  }

  public function testDoImportWithUnixNewlines()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;
    $importer->setOrmClasses($this->ormClasses);

    $importer->doImport($this->vfs->url().'/unix.csv');

    $this->assertSame(explode(',', $this->csvHeader), $importer->getHeader());
    $this->assertSame($this->getCsvRowAsAssocArray(), $importer->getRow(0));
    $this->assertSame(3, $importer->countRowsImported());
    $this->assertSame(4, $importer->countRowsTotal());
  }

  public function testDoImportWithWindowsNewlinesAndErrorLog()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;
    $importer->setOrmClasses($this->ormClasses);
    $importer->setOption('errorLog', $this->vfs->url().'/error.log');
    $importer->setProgressFrequency(2);

    $importer->doImport($this->vfs->url().'/windows.csv');

    $this->assertSame(explode(',', $this->csvHeader), $importer->getHeader());
    $this->assertSame($this->getCsvRowAsAssocArray(), $importer->getRow(0));
    $this->assertSame(3, $importer->countRowsImported());
    $this->assertSame(4, $importer->countRowsTotal());
  }

  public function testDoImportWithOffset()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;
    $importer->setOrmClasses($this->ormClasses);
    $importer->setOffset(1);
    $importer->setProgressFrequency(0);

    $importer->doImport($this->vfs->url().'/unix.csv');

    $this->assertSame(explode(',', $this->csvHeader), $importer->getHeader());
    $this->assertSame($this->getCsvRowAsAssocArray(1), $importer->getRow(1));
    $this->assertSame(2, $importer->countRowsImported());
    $this->assertSame(4, $importer->countRowsTotal());
  }

  public function testDoImportWithSetHeader()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;
    $importer->setOrmClasses($this->ormClasses);
    $importer->setHeader($this->csvHeader);

    $importer->doImport($this->vfs->url().'/noheader.csv');

    $this->assertSame(explode(',', $this->csvHeader), $importer->getHeader());
    $this->assertSame($this->getCsvRowAsAssocArray(0), $importer->getRow(0));
    $this->assertSame(3, $importer->countRowsImported());
    $this->assertSame(4, $importer->countRowsTotal());
  }

  public function testDoImportWithUpdateOnMatch()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon,
      ['updateOnMatch' => true]);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;
    $importer->setOrmClasses($this->ormClasses);

    $importer->doImport($this->vfs->url().'/unix.csv');

    $this->assertSame(true, $importer->getUpdateOnMatch());
    $this->assertSame(explode(',', $this->csvHeader), $importer->getHeader());
    $this->assertSame($this->getCsvRowAsAssocArray(), $importer->getRow(0));
    $this->assertSame(3, $importer->countRowsImported());
    $this->assertSame(4, $importer->countRowsTotal());
  }

  public function testDoImportWithUpdateOnMatchAndNoInsert()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon,
      ['updateOnMatch' => true, 'noInsert' => true]);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;
    $importer->setOrmClasses($this->ormClasses);

    $importer->doImport($this->vfs->url().'/unix.csv');

    $this->assertSame(true, $importer->getUpdateOnMatch());
    $this->assertSame(explode(',', $this->csvHeader), $importer->getHeader());
    $this->assertSame($this->getCsvRowAsAssocArray(), $importer->getRow(0));
    $this->assertSame(2, $importer->countRowsImported());
    $this->assertSame(4, $importer->countRowsTotal());
  }

  /**
   * @dataProvider processRowProvider
   */
  public function testProcessRow($data, $expectedResult)
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon,
      ['defaultCulture' => 'en']);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;
    $importer->setOrmClasses($this->ormClasses);

    $result = $importer->processRow($data);

    // assertSame returns an error if array order is no the same
    ksort($expectedResult);
    ksort($result);

    $this->assertSame($expectedResult, $result);
  }

  public function testProcessRowThrowsExceptionIfNoNameOrLocation()
  {
    $this->expectException(UnexpectedValueException::class);

    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;

    $importer->processRow([
      'name'     => '',
      'type'     => 'Boîte Hollinger',
      'location' => '',
      'culture'  => 'fr'
    ]);
  }

  public function testProcessRowThrowsExceptionIfUnknownType()
  {
    $this->expectException(UnexpectedValueException::class);

    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;

    $importer->processRow([
      'name'     => 'MPATHG',
      'type'     => 'Spam',
      'location' => 'Camelot',
      'culture'  => 'en'
    ]);
  }

  public function testGetRecordCulture()
  {
    $importer = new PhysicalObjectCsvImporter(
      $this->context, $this->vdbcon, array('defaultCulture' => 'de'));

    // Passed direct value
    $this->assertSame('fr', $importer->getRecordCulture('fr'));

    // Get culture from $this->defaultCulture
    $this->assertSame('de', $importer->getRecordCulture());

    // Get culture from sfConfig
    sfConfig::set('default_culture', 'en');
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $this->assertSame('en', $importer->getRecordCulture());
  }

  public function testGetRecordCultureThrowsExceptionWhenCantDetermineCulture()
  {
    $this->expectException(UnexpectedValueException::class);

    sfConfig::set('default_culture', '');

    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->getRecordCulture();
  }

  public function testTypeIdLookupTableSetAndGet()
  {
    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->typeIdLookupTable = $this->typeIdLookupTableFixture;

    $this->assertSame($this->typeIdLookupTableFixture,
      $importer->typeIdLookupTable);
  }

  public function testGetTypeIdLookupTable()
  {
    $stub = $this->createStub(QubitTaxonomy::class);
    $stub->method('getTermNameToIdLookupTable')
         ->willReturn($this->typeIdLookupTableFixture);

    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setPhysicalObjectTypeTaxonomy($stub);

    $this->assertEquals($this->typeIdLookupTableFixture,
      $importer->typeIdLookupTable);
  }

  public function testGetTypeIdLookupTableExceptionGettingTerms()
  {
    $stub = $this->createStub(QubitTaxonomy::class);
    $stub->method('getTermNameToIdLookupTable')
         ->willReturn(null);

    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setPhysicalObjectTypeTaxonomy($stub);

    $this->expectException(sfException::class);
    $importer->typeIdLookupTable;
  }

  public function testGetPhysicalObjectTypeTaxonomy()
  {
    $stub = $this->createStub(QubitTaxonomy::class);

    $importer = new PhysicalObjectCsvImporter($this->context, $this->vdbcon);
    $importer->setPhysicalObjectTypeTaxonomy($stub);

    $this->assertSame($stub, $importer->getPhysicalObjectTypeTaxonomy());
  }
}
