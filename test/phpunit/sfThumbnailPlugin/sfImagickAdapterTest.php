<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../../plugins/sfThumbnailPlugin/lib/sfThumbnail.class.php';

require_once __DIR__.'/../../../plugins/sfThumbnailPlugin/lib/sfImagickAdapter.class.php';

/**
 * @internal
 *
 * @covers \sfImagickAdapter
 */
class sfImagickAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('The imagick extension is not available.');
        }

        if (!Imagick::queryFormats('SVG')) {
            $this->markTestSkipped('The SVG decoder is not available.');
        }
    }

    /**
     * Imagick reports SVGs as image/svg+xml. Accepting only image/svg rejects
     * otherwise readable uploads before thumbnails can be generated.
     */
    public function testCreatesRasterThumbnailFromSvg(): void
    {
        $thumbnail = new sfThumbnail(40, 40, true, false, 90, 'sfImagickAdapter');
        $thumbnail->loadFile(__DIR__.'/../../data/thumbnail.svg');

        $this->assertSame('image/svg+xml', $thumbnail->getMime());

        $image = new Imagick();

        try {
            $image->readImageBlob($thumbnail->toString('image/jpeg'));

            $this->assertSame('image/jpeg', $image->getImageMimeType());
            $this->assertSame(40, $image->getImageWidth());
            $this->assertSame(20, $image->getImageHeight());
            $color = $image->getImagePixelColor(20, 10)->getColor();
            $this->assertGreaterThan(240, $color['r']);
            $this->assertLessThan(15, $color['g']);
            $this->assertLessThan(15, $color['b']);
        } finally {
            $image->clear();
            $thumbnail->freeAll();
        }
    }
}
