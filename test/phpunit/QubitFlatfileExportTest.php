<?php

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitFlatFileExport
 */
class QubitFlatfileExportTest extends TestCase
{
    protected $vfs; // virtual filesystem

    public function setUp(): void
    {
        $directory = [];

        // Set up and cache the virtual file system
        $this->vfs = vfsStream::setup('root', null, $directory);
    }

    /**
     * Creates a mock exporter with the specified column names and hidden columns.
     *
     * @param mixed      $outputPath    The path to the output file
     * @param mixed      $columnNames   The column names for the output CSV file
     * @param mixed      $hiddenColumns The columns to hide from the output CSV file
     * @param null|mixed $params
     *
     * @return PHPUnit\Framework\MockObject\MockObject|QubitFlatfileExport
     */
    public function createMockExporter($outputPath, $columnNames, $hiddenColumns)
    {
        $exporter = $this->getMockBuilder(QubitFlatfileExport::class)
            ->setConstructorArgs([$outputPath])
            ->onlyMethods([
                'loadResourceSpecificConfiguration',
                'getHiddenVisibleElementCsvHeaders',
            ])
            ->getMock();

        $exporter->method('loadResourceSpecificConfiguration')
            ->willReturnCallback(function () use ($exporter, $columnNames) {
                // Simulate what loadResourceSpecificConfiguration does

                $mockUser = $this->getMockBuilder(stdClass::class)
                    ->addMethods(['isAuthenticated'])
                    ->getMock();

                $mockUser->method('isAuthenticated')
                    ->willReturn(true);

                $exporter->columnNames = $columnNames;
                $exporter->standardColumns = $columnNames;
                $exporter->totalColumnsIncludingHidden = count($columnNames);
                $exporter->columnMap = [];
                $exporter->propertyMap = [];
                $exporter->user = $mockUser;

                // Need to use reflection to change protected members
                $reflection = new ReflectionClass($exporter);

                $configurationLoadedProperty = $reflection->getProperty('configurationLoaded');
                $configurationLoadedProperty->setValue($exporter, true);

                $rowProperty = $reflection->getProperty('row');
                $rowProperty->setAccessible(true);
                $rowProperty->setValue($exporter, array_fill(0, count($exporter->columnNames), null));
            });

        $exporter->method('getHiddenVisibleElementCsvHeaders')
            ->willReturnCallback(function () use ($exporter, $columnNames, $hiddenColumns) {
                $reflection = new ReflectionClass($exporter);

                $nonVisibleElementsIncludedProperty = $reflection->getProperty('nonVisibleElementsIncluded');
                $nonVisibleElementsIncludedProperty->setValue($exporter, $hiddenColumns);

                $indices = [];
                foreach ($hiddenColumns as $col) {
                    $index = array_search($col, $columnNames);
                    array_push($indices, $index);
                }

                $nonVisibleElementsIndexesProperty = $reflection->getProperty('nonVisibleElementsIndexes');
                $nonVisibleElementsIndexesProperty->setValue($exporter, $indices);
            });

        $exporter->setParams([
            'nonVisibleElementsIncluded' => [],
        ]);

        return $exporter;
    }

    /**
     * Create a mock resource to export.
     *
     * Applies array key=>value pairs to properties on a mock object.
     *
     * @param mixed $properties the properties to add to the mock object
     *
     * @return PHPUnit\Framework\MockObject\MockObject|stdClass
     */
    public function createMockResource($properties)
    {
        $mockResource = $this->getMockBuilder(stdClass::class)
            ->addMethods(['getDigitalObject'])
            ->getMock();

        foreach ($properties as $prop => $value) {
            $mockResource->{$prop} = $value;
        }

        $mockResource->method('getDigitalObject')
            ->willReturn(null);

        return $mockResource;
    }

    public function testExportSingleResource(): void
    {
        $csvFile = $this->vfs->url().'/output.csv';

        $mockExporter = $this->createMockExporter(
            outputPath: $csvFile,
            columnNames: ['colA', 'colB', 'colC'],
            hiddenColumns: [],
        );

        $mockResource = $this->createMockResource(properties: [
            'colA' => 'A',
            'colB' => 'B',
            'colC' => 'C',
        ]);

        $mockExporter->exportResource($mockResource);

        $this->assertTrue(file_exists($csvFile));

        $csvContent = file_get_contents($csvFile);
        $rows = str_getcsv($csvContent, "\n");

        // Should have two rows (one header, one data row)
        $this->assertCount(2, $rows);

        $headerData = str_getcsv($rows[0]);
        $rowData = str_getcsv($rows[1]);

        $this->assertEquals(['colA', 'colB', 'colC'], $headerData);
        $this->assertEquals(['A', 'B', 'C'], $rowData);
    }

    public function testExportSingleResourceWithArrayContent(): void
    {
        $csvFile = $this->vfs->url().'/output.csv';

        $mockExporter = $this->createMockExporter(
            outputPath: $csvFile,
            columnNames: ['colA', 'colB', 'colC'],
            hiddenColumns: [],
        );

        $mockResource = $this->createMockResource(properties: [
            'colA' => ['1', '2', '3'],
            'colB' => ['u', 'v', 'w'],
            'colC' => 'C',
        ]);

        $mockExporter->exportResource($mockResource);

        $this->assertTrue(file_exists($csvFile));

        $csvContent = file_get_contents($csvFile);
        $rows = str_getcsv($csvContent, "\n");

        // Should have two rows (one header, one data row)
        $this->assertCount(2, $rows);

        $headerData = str_getcsv($rows[0]);
        $rowData = str_getcsv($rows[1]);

        $this->assertEquals(['colA', 'colB', 'colC'], $headerData);
        $this->assertEquals(['1|2|3', 'u|v|w', 'C'], $rowData);
    }

    public function testExportSingleResourceOneHiddenCol(): void
    {
        $csvFile = $this->vfs->url().'/output.csv';

        // Hide colA
        $mockExporter = $this->createMockExporter(
            outputPath: $csvFile,
            columnNames: ['colA', 'colB', 'colC', 'colD'],
            hiddenColumns: ['colA'],
        );

        $mockResource = $this->createMockResource(properties: [
            'colA' => 'A',
            'colB' => 'B',
            'colC' => 'C',
            'colD' => 'D',
        ]);

        $mockExporter->exportResource($mockResource);

        $this->assertTrue(file_exists($csvFile));

        $csvContent = file_get_contents($csvFile);
        $rows = str_getcsv($csvContent, "\n");

        // Should have two rows (one header, one data row)
        $this->assertCount(2, $rows);

        $headerData = str_getcsv($rows[0]);
        $rowData = str_getcsv($rows[1]);

        $this->assertEquals(['colB', 'colC', 'colD'], $headerData);
        $this->assertEquals(['B', 'C', 'D'], $rowData);
    }

    public function testExportSingleResourceMultipleHiddenCols(): void
    {
        $csvFile = $this->vfs->url().'/output.csv';

        // Hide colB and colD
        $mockExporter = $this->createMockExporter(
            outputPath: $csvFile,
            columnNames: ['colA', 'colB', 'colC', 'colD'],
            hiddenColumns: ['colB', 'colD'],
        );

        $mockResource = $this->createMockResource(properties: [
            'colA' => 'A',
            'colB' => 'B',
            'colC' => 'C',
            'colD' => 'D',
        ]);

        $mockExporter->exportResource($mockResource);

        $this->assertTrue(file_exists($csvFile));

        $csvContent = file_get_contents($csvFile);
        $rows = str_getcsv($csvContent, "\n");

        // Should have two rows (one header, one data row)
        $this->assertCount(2, $rows);

        $headerData = str_getcsv($rows[0]);
        $rowData = str_getcsv($rows[1]);

        $this->assertEquals(['colA', 'colC'], $headerData);
        $this->assertEquals(['A', 'C'], $rowData);
    }

    public function testExportMultipleResources(): void
    {
        $csvFile = $this->vfs->url().'/output.csv';

        $mockExporter = $this->createMockExporter(
            outputPath: $csvFile,
            columnNames: ['colA', 'colB', 'colC'],
            hiddenColumns: [],
        );

        // Create and export 3 resources
        foreach (range(1, 3) as $i) {
            $mockResource = $this->createMockResource([
                'colA' => "A{$i}",
                'colB' => "B{$i}",
                'colC' => "C{$i}",
            ]);

            $mockExporter->exportResource($mockResource);
        }

        $this->assertTrue(file_exists($csvFile));

        $csvContent = file_get_contents($csvFile);
        $rows = str_getcsv($csvContent, "\n");

        // Should have four rows (one header, three data rows)
        $this->assertCount(4, $rows);

        $headerData = str_getcsv($rows[0]);
        $rowData1 = str_getcsv($rows[1]);
        $rowData2 = str_getcsv($rows[2]);
        $rowData3 = str_getcsv($rows[3]);

        $this->assertEquals(['colA', 'colB', 'colC'], $headerData);
        $this->assertEquals(['A1', 'B1', 'C1'], $rowData1);
        $this->assertEquals(['A2', 'B2', 'C2'], $rowData2);
        $this->assertEquals(['A3', 'B3', 'C3'], $rowData3);
    }

    public function testExportMultipleResourcesOneHiddenCol(): void
    {
        $csvFile = $this->vfs->url().'/output.csv';

        // Hide colA
        $mockExporter = $this->createMockExporter(
            outputPath: $csvFile,
            columnNames: ['colA', 'colB', 'colC'],
            hiddenColumns: ['colA'],
        );

        // Create and export 3 resources
        foreach (range(1, 3) as $i) {
            $mockResource = $this->createMockResource([
                'colA' => "A{$i}",
                'colB' => "B{$i}",
                'colC' => "C{$i}",
            ]);

            $mockExporter->exportResource($mockResource);
        }

        $this->assertTrue(file_exists($csvFile));

        $csvContent = file_get_contents($csvFile);
        $rows = str_getcsv($csvContent, "\n");

        // Should have four rows (one header, three data rows)
        $this->assertCount(4, $rows);

        $headerData = str_getcsv($rows[0]);
        $rowData1 = str_getcsv($rows[1]);
        $rowData2 = str_getcsv($rows[2]);
        $rowData3 = str_getcsv($rows[3]);

        $this->assertEquals(['colB', 'colC'], $headerData);
        $this->assertEquals(['B1', 'C1'], $rowData1);
        $this->assertEquals(['B2', 'C2'], $rowData2);
        $this->assertEquals(['B3', 'C3'], $rowData3);
    }

    public function testExportMultipleResourcesMultipleHiddenCols(): void
    {
        $csvFile = $this->vfs->url().'/output.csv';

        // Hide colB, colD
        $mockExporter = $this->createMockExporter(
            outputPath: $csvFile,
            columnNames: ['colA', 'colB', 'colC', 'colD'],
            hiddenColumns: ['colB', 'colD'],
        );

        // Create and export 3 resources
        foreach (range(1, 3) as $i) {
            $mockResource = $this->createMockResource([
                'colA' => "A{$i}",
                'colB' => "B{$i}",
                'colC' => "C{$i}",
                'colD' => "D{$i}",
            ]);

            $mockExporter->exportResource($mockResource);
        }

        $this->assertTrue(file_exists($csvFile));

        $csvContent = file_get_contents($csvFile);
        $rows = str_getcsv($csvContent, "\n");

        // Should have four rows (one header, three data rows)
        $this->assertCount(4, $rows);

        $headerData = str_getcsv($rows[0]);
        $rowData1 = str_getcsv($rows[1]);
        $rowData2 = str_getcsv($rows[2]);
        $rowData3 = str_getcsv($rows[3]);

        $this->assertEquals(['colA', 'colC'], $headerData);
        $this->assertEquals(['A1', 'C1'], $rowData1);
        $this->assertEquals(['A2', 'C2'], $rowData2);
        $this->assertEquals(['A3', 'C3'], $rowData3);
    }
}
