<?php

/**
 * @covers \QubitFindingAidGenerator
 *
 * @internal
 */
class QubitFindingAidGeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testSetResourceFromConstructor()
    {
        $resource = new QubitInformationObject();
        $generator = new QubitFindingAidGenerator($resource);

        $this->assertSame($resource, $generator->getResource());
    }

    public function testSetResource()
    {
        $resource1 = new QubitInformationObject();
        $resource2 = new QubitInformationObject();
        $resource2->id = '11111';

        $generator = new QubitFindingAidGenerator($resource1);
        $generator->setResource($resource2);

        $this->assertSame($resource2, $generator->getResource());
    }

    public function testSetResourceRootException()
    {
        $resource = new QubitInformationObject();
        $resource->id = 1;

        $this->expectException(UnexpectedValueException::class);
        $generator = new QubitFindingAidGenerator($resource);
    }

    public function testSetResourceTypeError()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $this->expectException(TypeError::class);
        $generator->setResource('foo');
    }

    public function testSetLogger()
    {
        $logger = new sfNoLogger(new sfEventDispatcher());
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $generator->setLogger($logger);

        $this->assertSame($logger, $generator->getLogger());
    }

    public function testSetLoggerFromConstructorOption()
    {
        $logger = new sfNoLogger(new sfEventDispatcher());
        $generator = new QubitFindingAidGenerator(
            new QubitInformationObject(),
            ['logger' => $logger]
        );

        $this->assertSame($logger, $generator->getLogger());
    }

    public function testValidateSetting()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());

        $this->assertTrue(
            $generator->validateSetting('val1', ['val1', 'val2'])
        );
    }

    public function testValidateSettingUnexpectedValue()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $this->expectException(UnexpectedValueException::class);

        $generator->validateSetting('test', ['val1', 'val2']);
    }

    public function testSetAuthLevel()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $generator->setAuthLevel('public');

        $this->assertSame('public', $generator->getAuthLevel());
    }

    public function testSetAppRoot()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $generator->setAppRoot('/tmp/foo');

        $this->assertSame('/tmp/foo', $generator->getAppRoot());
    }

    public function testSetAppRootFromConstructorOptions()
    {
        $generator = new QubitFindingAidGenerator(
            new QubitInformationObject(),
            ['appRoot' => '/tmp/foo']
        );

        $this->assertSame('/tmp/foo', $generator->getAppRoot());
    }

    public function testSetFormat()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $generator->setFormat('rtf');

        $this->assertSame('rtf', $generator->getFormat());
    }

    public function testSetFormatInvalidValue()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());

        $this->expectException(UnexpectedValueException::class);
        $generator->setFormat('foo');
    }

    public function testGetModelDefaultValue()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());

        $this->assertSame('inventory-summary', $generator->getModel());
    }

    public function testSetModel()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $generator->setModel('full-details');

        $this->assertSame('full-details', $generator->getModel());
    }

    public function testGetXslFilePath()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $generator->setAppRoot('/tmp');

        $this->assertSame(
            '/tmp/lib/task/pdf/ead-pdf-inventory-summary.xsl',
            $generator->getXslFilePath()
        );
    }

    public function testGetCatalogPath()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $generator->setAppRoot('/tmp');

        $this->assertSame(
            '/tmp/vendor/resolver.jar',
            $generator->getResolverPath()
        );
    }

    public function testGetSaxonPath()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $generator->setAppRoot('/tmp');

        $this->assertSame(
            '/tmp/vendor/saxon-he-10.6.jar',
            $generator->getSaxonPath()
        );
    }

    public function testGetResolverPath()
    {
        $generator = new QubitFindingAidGenerator(new QubitInformationObject());
        $generator->setAppRoot('/tmp');

        $this->assertSame(
            '/tmp/data/xml/catalog.xml',
            $generator->getCatalogPath()
        );
    }
}
