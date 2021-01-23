<?php

use org\bovigo\vfs\vfsStream;

/**
 * @covers \QubitFindingAid
 *
 * @internal
 */
class QubitFindingAidTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        // define virtual file system
        $directory = [
            'foobar.pdf' => 'foobar',
        ];

        // setup and cache the virtual file system
        $this->vfs = vfsStream::setup('root', null, $directory);
    }

    public function testSetResourceFromConstructor()
    {
        $resource = new QubitInformationObject();
        $findingAid = new QubitFindingAid($resource);

        $this->assertSame($resource, $findingAid->getResource());
    }

    public function testSetResource()
    {
        $resource1 = new QubitInformationObject();
        $resource2 = new QubitInformationObject();
        $resource2->id = '11111';

        $findingAid = new QubitFindingAid($resource1);

        $findingAid->setResource($resource2);

        $this->assertSame($resource2, $findingAid->getResource());
    }

    public function testSetResourceTypeError()
    {
        $findingAid = new QubitFindingAid(new QubitInformationObject());

        $this->expectException(TypeError::class);
        $findingAid->setResource('foo');
    }

    public function testSetLogger()
    {
        $logger = new sfNoLogger(new sfEventDispatcher());
        $findingAid = new QubitFindingAid(new QubitInformationObject());

        $findingAid->setLogger($logger);

        $this->assertSame($logger, $findingAid->getLogger());
    }

    public function testSetLoggerFromConstructorOption()
    {
        $logger = new sfNoLogger(new sfEventDispatcher());
        $findingAid = new QubitFindingAid(
            new QubitInformationObject(),
            ['logger' => $logger]
        );

        $this->assertSame($logger, $findingAid->getLogger());
    }

    public function testSetHomeDir()
    {
        $findingAid = new QubitFindingAid(new QubitInformationObject());
        $findingAid->setHomeDir('/foo/');

        $this->assertSame('/foo/', $findingAid->getHomeDir());
    }

    public function testGetHomeDirDefault()
    {
        $findingAid = new QubitFindingAid(new QubitInformationObject());

        $this->assertSame(
            sfConfig::get('sf_web_dir').'/downloads/',
            $findingAid->getHomeDir()
        );
    }

    public function testGetPossibleFilenames()
    {
        $resource = new QubitInformationObject();
        $resource->id = '12345';
        $resource->slug = 'foobar';
        $findingAid = new QubitFindingAid($resource);

        $this->assertSame(
            ['12345.pdf', '12345.rtf', 'foobar.pdf', 'foobar.rtf'],
            $findingAid->getPossibleFilenames()
        );
    }

    public function testSetPath()
    {
        $path = '/path/to/foo.pdf';
        $findingAid = new QubitFindingAid(new QubitInformationObject());
        $findingAid->setPath($path);

        $this->assertSame($path, $findingAid->getPath());
    }

    public function testGetPathByInference()
    {
        $resource = new QubitInformationObject();
        $resource->id = '12345';
        $resource->slug = 'foobar';

        $findingAid = new QubitFindingAid($resource);
        $findingAid->setHomeDir($this->vfs->url().'/');

        $this->assertSame(
            $this->vfs->url().'/foobar.pdf',
            $findingAid->getPath()
        );
    }

    public function testGetFormat()
    {
        $findingAid = new QubitFindingAid(new QubitInformationObject());
        $findingAid->setPath($this->vfs->url().'/foobar.pdf');

        $this->assertSame(
            'pdf',
            $findingAid->getFormat()
        );
    }
}
