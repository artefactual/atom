<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 *
 * @covers \CsvImportAuditer
 */
class CsvImportAuditerTest extends \PHPUnit\Framework\TestCase
{
    protected $csvHeader;
    protected $csvData;
    protected $ormClasses;
    protected $vfs; // virtual filesystem

    // Fixtures

    public function setUp(): void
    {
        $this->ormClasses = [
            'keymap' => \AccessToMemory\test\mock\QubitKeymap::class,
        ];

        $this->csvHeader = 'legacyId';

        $this->csvData = [
            // Note: leading and trailing whitespace in first row is intentional
            '"123 "',
            '"124"',
            '"125"',
        ];

        // Define virtual file system
        $directory = [
            'test.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'noheader.csv' => implode("\n", $this->csvData)."\n",
            'unreadable.csv' => $this->csvData[0],
            'error.log' => '',
        ];

        // Set up and cache the virtual file system
        $this->vfs = vfsStream::setup('root', null, $directory);

        // Make 'unreadable.csv' owned and readable only by root user
        $file = $this->vfs->getChild('root/unreadable.csv');
        $file->chmod('0400');
        $file->chown(vfsStream::OWNER_USER_1);
    }

    public function getCsvRowAsAssocArray($row = 0): array
    {
        return array_combine(
            explode(',', $this->csvHeader),
            str_getcsv($this->csvData[$row])
        );
    }

    // Data providers

    public function setOptionsProvider(): array
    {
        $defaultOptions = [
            'quiet' => false,
            'errorLog' => null,
            'progressFrequency' => 1,
            'idColumnName' => 'legacyId',
        ];

        $inputs = [
            null,
            [],
            [
                'progressFrequency' => 2,
            ],
        ];

        $outputs = [
            $defaultOptions,
            $defaultOptions,
            [
                'quiet' => false,
                'errorLog' => null,
                'progressFrequency' => 2,
                'idColumnName' => 'legacyId',
            ],
        ];

        return [
            [$inputs[0], $outputs[0]],
            [$inputs[1], $outputs[1]],
            [$inputs[2], $outputs[2]],
        ];
    }

    public function processRowProvider(): array
    {
        $inputs = [
            [
                'source' => 'test_import',
                'row' => [
                    'legacyId' => '123',
                    'title' => 'Row with no issues',
                ],
            ],
            [
                'source' => 'test_import',
                'row' => [
                    'legacyId' => '124',
                    'title' => 'Row with new source ID',
                ],
            ],
            [
                'source' => 'bad_source',
                'row' => [
                    'legacyId' => '123',
                    'title' => 'Row with bad source name',
                ],
            ],
        ];

        $expectedResults = [
            [
                'missing' => [],
            ],
            [
                'missing' => [124 => 1],
            ],
            [
                'missing' => [123 => 1],
            ],
        ];

        return [
            [$inputs[0], $expectedResults[0]],
            [$inputs[1], $expectedResults[1]],
            [$inputs[2], $expectedResults[2]],
        ];
    }

    // Tests

    public function testSetAndGetSourceName(): void
    {
        $auditer = new CsvImportAuditer();

        $auditer->setSourceName('some_import');
        $this->assertSame('some_import', $auditer->getSourceName());
    }

    public function testSetAndGetTargetName(): void
    {
        $auditer = new CsvImportAuditer();

        $auditer->setTargetName('some_target_name');
        $this->assertSame('some_target_name', $auditer->getTargetName());
    }

    public function testSetAndGetFilename()
    {
        $auditer = new CsvImportAuditer();

        $auditer->setFileName($this->vfs->url().'/test.csv');
        $this->assertSame($this->vfs->url().'/test.csv', $auditer->getFileName());
    }

    public function testSetFilenameFileNotFoundException(): void
    {
        $this->expectException(sfException::class);
        $auditer = new CsvImportAuditer();
        $auditer->setFilename('bad_name.csv');
    }

    public function testSetFilenameFileUnreadableException()
    {
        $this->expectException(sfException::class);
        $auditer = new CsvImportAuditer();
        $auditer->setFilename($this->vfs->url().'/unreadable.csv');
    }

    public function testSetFilenameSuccess()
    {
        $importer = new CsvImportAuditer();
        $importer->setFilename($this->vfs->url().'/test.csv');
        $this->assertSame(
            $this->vfs->url().'/test.csv',
            $importer->getFilename()
        );
    }

    /**
     * @dataProvider setOptionsProvider
     *
     * @param mixed $options
     * @param mixed $expected
     */
    public function testSetOptions($options, $expected): void
    {
        $importer = new CsvImportAuditer();
        $importer->setOptions($options);
        $this->assertSame($expected, $importer->getOptions());
    }

    public function testSetOptionsThrowsTypeError(): void
    {
        $this->expectException(TypeError::class);

        $auditer = new CsvImportAuditer();
        $auditer->setOptions(new stdClass());
    }

    public function testSetAndGetIdColumnName(): void
    {
        $auditer = new CsvImportAuditer();

        $auditer->setOption('idColumnName', 'some_column');
        $this->assertSame('some_column', $auditer->getOption('idColumnName'));
    }

    public function testSetOptionFromOptions(): void
    {
        $auditer = new CsvImportAuditer();
        $auditer->setOptions([
            'progressFrequency' => 5,
            'idColumnName' => 'some_column',
        ]);

        $this->assertSame(5, $auditer->getOption('progressFrequency'));
        $this->assertSame('some_column', $auditer->getOption('idColumnName'));
    }

    public function testSourceNameDefaultsToFilename()
    {
        $filename = $this->vfs->url().'/test.csv';
        $importer = new CsvImportAuditer();
        $importer->setFilename($filename);

        $this->assertSame(basename($filename), $importer->getSourceName());
    }

    public function testDoAuditNoFilenameException(): void
    {
        $this->expectException(sfException::class);

        $auditer = new CsvImportAuditer();
        $auditer->doAudit();
    }

    public function testImportRowsWithDefaultTargetName(): void
    {
        $auditer = new CsvImportAuditer(['quiet' => true]);
        $auditer->setFilename($this->vfs->url().'/test.csv');

        $auditer->setOrmClasses($this->ormClasses);
        $auditer->setSourceName('test_import');

        $auditer->doAudit();

        $this->assertSame($auditer->getMissingIds(), [124 => 2]);
    }

    public function testImportRowsWithExplicitTargetName(): void
    {
        $auditer = new CsvImportAuditer(['quiet' => true]);
        $auditer->setFilename($this->vfs->url().'/test.csv');

        $auditer->setOrmClasses($this->ormClasses);
        $auditer->setSourceName('test_import');
        $auditer->setTargetName('information_object');

        $auditer->doAudit();

        $this->assertSame($auditer->getMissingIds(), [124 => 2]);
    }

    public function testImportRowsWithBadTargetName(): void
    {
        $auditer = new CsvImportAuditer(['quiet' => true]);
        $auditer->setFilename($this->vfs->url().'/test.csv');

        $auditer->setOrmClasses($this->ormClasses);
        $auditer->setSourceName('test_import');
        $auditer->setTargetName('bad_target_name');

        $auditer->doAudit();

        // All IDs should be missing
        $this->assertSame($auditer->getMissingIds(), [123 => 1, 124 => 2, 125 => 3]);
    }

    /**
     * @dataProvider processRowProvider
     *
     * @param mixed $data
     * @param mixed $expectedResult
     */
    public function testProcessRow($data, $expectedResult): void
    {
        $auditer = new CsvImportAuditer();
        $auditer->setOrmClasses($this->ormClasses);
        $auditer->setSourceName($data['source']);

        $result = $auditer->processRow($data['row']);

        $this->assertSame($auditer->getMissingIds(), $expectedResult['missing']);
    }

    public function testProcessRowThrowsExceptionIfBadLegacyIdColumn(): void
    {
        $this->expectException(UnexpectedValueException::class);

        // Row with mis-named ID column
        $row = [
            'id' => '123',
        ];

        $auditer = new CsvImportAuditer();
        $auditer->setOrmClasses($this->ormClasses);
        $auditer->setSourceName('test_import');

        $result = $auditer->processRow($row);
    }

    public function testProcessRowCustomIdColumn(): void
    {
        $row = [
            'id' => '123',
        ];

        $auditer = new CsvImportAuditer(['idColumnName' => 'id']);
        $auditer->setOrmClasses($this->ormClasses);
        $auditer->setSourceName('test_import');

        $result = $auditer->processRow($row);

        $this->assertSame($auditer->getMissingIds(), []);
    }

    public function testProcessRowThrowsExceptionIfNoIdColumn(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $auditer = new CsvImportAuditer();

        $auditer->processRow([]);
    }

    public function testProgressUpdateFreqOne(): void
    {
        $auditer = new CsvImportAuditer();
        $auditer->setOption('progressFrequency', 1);

        $this->assertSame(
            'Row [0/0] audited',
            $auditer->progressUpdate(0)
        );
    }

    public function testProgressUpdateFreqTwo(): void
    {
        $auditer = new CsvImportAuditer();
        $auditer->setOption('progressFrequency', 2);

        $this->assertSame(
            'Audited 2 of 0 rows...',
            $auditer->progressUpdate(2)
        );
    }
}
