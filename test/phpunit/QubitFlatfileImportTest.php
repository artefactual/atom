<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitFlatfileImport
 */
class QubitFlatfileImportTest extends TestCase
{
    /**
     * Test that handleClearAndUpdate deletes digital object when keepDigitalObjects is false.
     */
    public function testHandleClearAndUpdateDeletesDigitalObject(): void
    {
        // Create mock digital object
        $mockDigitalObject = $this->createMock(QubitDigitalObject::class);
        $mockDigitalObject->expects($this->once())
            ->method('delete');

        // Create mock information object
        $mockInformationObject = $this->getMockBuilder(QubitInformationObject::class)
            ->onlyMethods([
                'getDigitalObject',
                'getProperties',
            ])
            ->getMock();

        $mockInformationObject->method('getDigitalObject')
            ->willReturn($mockDigitalObject);

        $mockInformationObject->method('getProperties')
            ->willReturn([]);

        $mockInformationObject->informationObjectI18ns = new ArrayObject();

        // Create importer with keepDigitalObjects = false
        $import = new QubitFlatfileImport([
            'columnNames' => ['title', 'culture'],
            'standardColumns' => ['title'],
            'keepDigitalObjects' => false,
        ]);

        $import->object = $mockInformationObject;
        $import->setStatus('row', ['Test title', 'en']);

        // Use reflection to call private method
        $reflection = new ReflectionClass($import);
        $method = $reflection->getMethod('handleClearAndUpdate');
        $method->setAccessible(true);

        $method->invoke($import);
    }

    /**
     * Test that handleClearAndUpdate keeps digital object when keepDigitalObjects is true.
     */
    public function testHandleClearAndUpdateKeepsDigitalObjectWhenOptionSet(): void
    {
        // Create mock digital object - should NOT have delete called
        $mockDigitalObject = $this->createMock(QubitDigitalObject::class);
        $mockDigitalObject->expects($this->never())
            ->method('delete');

        // Create mock information object
        $mockInformationObject = $this->getMockBuilder(QubitInformationObject::class)
            ->onlyMethods([
                'getDigitalObject',
                'getProperties',
            ])
            ->getMock();

        $mockInformationObject->method('getDigitalObject')
            ->willReturn($mockDigitalObject);

        $mockInformationObject->method('getProperties')
            ->willReturn([]);

        $mockInformationObject->informationObjectI18ns = new ArrayObject();

        // Create importer with keepDigitalObjects = true
        $import = new QubitFlatfileImport([
            'columnNames' => ['title', 'culture'],
            'standardColumns' => ['title'],
            'keepDigitalObjects' => true,
        ]);

        $import->object = $mockInformationObject;
        $import->setStatus('row', ['Test title', 'en']);

        // Use reflection to call private method
        $reflection = new ReflectionClass($import);
        $method = $reflection->getMethod('handleClearAndUpdate');
        $method->setAccessible(true);

        $method->invoke($import);
    }

    /**
     * Test that handleClearAndUpdate handles case when no digital object exists.
     */
    public function testHandleClearAndUpdateHandlesNoDigitalObject(): void
    {
        // Create mock information object with no digital object
        $mockInformationObject = $this->getMockBuilder(QubitInformationObject::class)
            ->onlyMethods([
                'getDigitalObject',
                'getProperties',
            ])
            ->getMock();

        $mockInformationObject->method('getDigitalObject')
            ->willReturn(null);

        $mockInformationObject->method('getProperties')
            ->willReturn([]);

        $mockInformationObject->informationObjectI18ns = new ArrayObject();

        // Create importer with keepDigitalObjects = false
        $import = new QubitFlatfileImport([
            'columnNames' => ['title', 'culture'],
            'standardColumns' => ['title'],
            'keepDigitalObjects' => false,
        ]);

        $import->object = $mockInformationObject;
        $import->setStatus('row', ['Test title', 'en']);

        // Use reflection to call private method - should not throw
        $reflection = new ReflectionClass($import);
        $method = $reflection->getMethod('handleClearAndUpdate');
        $method->setAccessible(true);

        // This should complete without error
        $method->invoke($import);

        $this->assertTrue(true);
    }
}
