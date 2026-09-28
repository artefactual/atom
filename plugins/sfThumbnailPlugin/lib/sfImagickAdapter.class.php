<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 *
 * sfImagickAdapter converts images to thumbnails using the Imagick library.
 *
 * @see https://github.com/Imagick/imagick
 *
 * @author Daniel Lovegrove <d.lovegrove11@gmail.com>
 */
class sfImagickAdapter
{
    protected const PDF_EXTRACT_BACKGROUND = 'white';

    protected $sourceWidth;
    protected $sourceHeight;
    protected $sourceMime;
    protected $maxWidth;
    protected $maxHeight;
    protected $scale;
    protected $inflate;
    protected $quality;
    protected $options;

    protected $source;  // The image object
    protected $image;  // The image path

    /**
     * Mime types this adapter supports.
     */
    protected $imgTypes = [
        'application/pdf',
        'application/postscript',
        'application/vnd.palm',
        'application/x-icb',
        'application/x-mif',
        'image/dcx',
        'image/g3fax',
        'image/gif',
        'image/jng',
        'image/jpeg',
        'image/pbm',
        'image/pcd',
        'image/pict',
        'image/pjpeg',
        'image/png',
        'image/ras',
        'image/sgi',
        'image/svg',
        'image/svg+xml',
        'image/tga',
        'image/tiff',
        'image/vda',
        'image/vnd.wap.wbmp',
        'image/vst',
        'image/x-fits',
        'image/x-ms-bmp',
        'image/x-otb',
        'image/x-palm',
        'image/x-pcx',
        'image/x-pgm',
        'image/x-photoshop',
        'image/x-ppm',
        'image/x-ptiff',
        'image/x-viff',
        'image/x-win-bitmap',
        'image/x-xbitmap',
        'image/x-xv',
        'image/xpm',
        'image/xwd',
        'text/plain',
        'video/mng',
        'video/mpeg',
        'video/mpeg2',
    ];

    /**
     * Imagemagick-specific Type to Mime type map.
     */
    protected $mimeMap = [
        'bmp' => 'image/bmp',
        'bmp2' => 'image/bmp',
        'bmp3' => 'image/bmp',
        'cur' => 'image/x-win-bitmap',
        'dcx' => 'image/dcx',
        'epdf' => 'application/pdf',
        'epi' => 'application/postscript',
        'eps' => 'application/postscript',
        'eps2' => 'application/postscript',
        'eps3' => 'application/postscript',
        'epsf' => 'application/postscript',
        'epsi' => 'application/postscript',
        'ept' => 'application/postscript',
        'ept2' => 'application/postscript',
        'ept3' => 'application/postscript',
        'fax' => 'image/g3fax',
        'fits' => 'image/x-fits',
        'g3' => 'image/g3fax',
        'gif' => 'image/gif',
        'gif87' => 'image/gif',
        'icb' => 'application/x-icb',
        'ico' => 'image/x-win-bitmap',
        'icon' => 'image/x-win-bitmap',
        'jng' => 'image/jng',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'm2v' => 'video/mpeg2',
        'miff' => 'application/x-mif',
        'mng' => 'video/mng',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'otb' => 'image/x-otb',
        'p7' => 'image/x-xv',
        'palm' => 'image/x-palm',
        'pbm' => 'image/pbm',
        'pcd' => 'image/pcd',
        'pcds' => 'image/pcd',
        'pcl' => 'application/pcl',
        'pct' => 'image/pict',
        'pcx' => 'image/x-pcx',
        'pdb' => 'application/vnd.palm',
        'pdf' => 'application/pdf',
        'pgm' => 'image/x-pgm',
        'picon' => 'image/xpm',
        'pict' => 'image/pict',
        'pjpeg' => 'image/pjpeg',
        'png' => 'image/png',
        'png24' => 'image/png',
        'png32' => 'image/png',
    ];

    public function __construct($maxWidth, $maxHeight, $scale, $inflate, $quality, $options)
    {
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->scale = $scale;
        $this->inflate = $inflate;
        $this->quality = $quality;
        $this->options = $options;
    }

    /**
     * Create the thumbnail as a binary stream.
     *
     * @param sfThumbnail $thumbnail  The thumbnail object
     * @param ?string     $targetMime The MIME type to write the output image in
     *
     * @return bool|string
     */
    public function toString(sfThumbnail $thumbnail, ?string $targetMime = null)
    {
        ob_start();
        $this->save($thumbnail, null, $targetMime);

        return ob_get_clean();
    }

    public function toResource()
    {
        throw new Exception('The sfImagickAdapter class does not support the toResource method.');
    }

    /**
     * Read image data including dimensions and MIME type. Sets the image to the page selected in
     * the options (if a page has been set). The first image is used by default.
     *
     * @param sfThumbnail $thumbnail The thumbnail object
     * @param string      $image     The path to the image
     *
     * @return bool
     */
    public function loadFile(sfThumbnail $thumbnail, string $image)
    {
        $this->image = $image;

        try {
            $this->source = new Imagick();
            $this->source->pingImage($this->image);
        } catch (ImagickException $e) {
            throw new Exception(
                sprintf('Could not open the file "%s" as an image', $this->image)
            );
        }

        $this->sourceMime = $this->source->getImageMimeType();

        if (!in_array($this->sourceMime, $this->imgTypes)) {
            throw new Exception(
                sprintf(
                    'Cannot handle the file "%s" with the MIME type "%s"',
                    $image,
                    $this->sourceMime,
                )
            );
        }

        $extractIndex = $this->getExtract();

        // Read a specific page if a PDF or multi-page image (like a .tif file)
        if ('application/pdf' == $this->sourceMime || $extractIndex > 0 || $this->source->getNumberImages() > 1) {
            $index = sprintf('[%d]', $extractIndex);
            $this->source = new Imagick($this->image.$index);
        } else {
            $this->source = new Imagick($this->image);
        }

        $this->sourceWidth = $this->source->getImageWidth();
        $this->sourceHeight = $this->source->getImageHeight();

        $thumbnail->initThumb($this->sourceWidth, $this->sourceHeight, $this->maxWidth, $this->maxHeight, $this->scale, $this->inflate);

        return true;
    }

    public function loadData($thumbnail, $image, $mime)
    {
        throw new Exception('The sfImagickAdapter class does not support the loadData method.');
    }

    /**
     * Transform the image and write it to the output file or as a binary stream if the destination
     * is null.
     *
     * @param sfThumbnail $thumbnail  The thumbnail object with the proper dimensions for this image
     * @param ?string     $thumbDest  The destination to write the image to (can be null)
     * @param ?string     $targetMime The targeted MIME type for the output image to be formatted in
     */
    public function save(sfThumbnail $thumbnail, ?string $thumbDest = null, ?string $targetMime = null)
    {
        // Show warning in case the method is used
        if (!empty($this->options['method'])) {
            trigger_error(
                sprintf(
                    'The "method" option (%s) is not supported by sfImagickAdapter and will be ignored',
                    $this->options['method'],
                ),
                E_USER_WARNING,
            );
        }

        $this->autoRotate();
        $this->mergePdfImageLayers();
        $this->resize($thumbnail->getThumbWidth(), $thumbnail->getThumbHeight(), $this->scale);
        $this->setQuality($targetMime);
        $this->setFormat($targetMime);

        if (null === $thumbDest) {
            echo $this->source->getImageBlob();
        } else {
            $this->source->writeImage($thumbDest);
        }
    }

    public function freeSource()
    {
        if (null !== $this->source) {
            $this->source->clear();
            $this->source = null;
        }
    }

    public function freeThumb()
    {
        return true;
    }

    public function getSourceMime()
    {
        return $this->sourceMime;
    }

    /**
     * Get the image index to extract the thumbnail for.
     *
     * The 'extract' number in the options is a 1-based page number, but the image index is
     * 0-based. Convert the 1-based page to a 0-based index, and return the last page if the index
     * is outside the range of available images.
     */
    private function getExtract()
    {
        if (
            empty($this->options['extract'])
            || !is_int($this->options['extract'])
            || $this->options['extract'] < 1
            || !$this->source
        ) {
            return 0;
        }

        // Convert 1-based page number to index
        $extractOption0 = $this->options['extract'] - 1;
        $pageCount = $this->source->getNumberImages();

        if ($extractOption0 < $pageCount) {
            return $extractOption0;
        }

        return $pageCount - 1;
    }

    /**
     * Rotate image so it is right-side-up.
     */
    private function autoRotate()
    {
        $orientation = $this->source->getImageOrientation();

        switch ($orientation) {
            case Imagick::ORIENTATION_BOTTOMRIGHT:
                $this->source->rotateImage(new ImagickPixel(), 180);

                break;

            case Imagick::ORIENTATION_RIGHTTOP:
                $this->source->rotateImage(new ImagickPixel(), 90);

                break;

            case Imagick::ORIENTATION_LEFTBOTTOM:
                $this->source->rotateImage(new ImagickPixel(), -90);

                break;
        }

        $this->source->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
    }

    /**
     * Merge image layers and set background to white.
     *
     * Does nothing if the image is not a PDF.
     */
    private function mergePdfImageLayers(): void
    {
        if ('application/pdf' == $this->getSourceMime()) {
            $this->source->setImageBackgroundColor(self::PDF_EXTRACT_BACKGROUND);
            $this->source = $this->source->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        }
    }

    /**
     * Resize the image based on the pre-calculated thumbnail dimensions. Preserve aspect ratio
     * when $bestFit is true.
     *
     * @param mixed $width   The pre-computed thumbnail width
     * @param mixed $height  The pre-computed thumbnail height
     * @param mixed $bestFit Preserve aspect ratio if true
     */
    private function resize(int $width, int $height, bool $bestFit): void
    {
        $this->source->thumbnailImage($width, $height, $bestFit);
    }

    /**
     * Set the image quality if the targeted MIME type is JPEG.
     *
     * @param mixed $targetMime The output MIME type
     */
    private function setQuality(?string $targetMime = null): void
    {
        if ($targetMime && $this->quality && 'image/jpeg' == $targetMime) {
            $this->source->setImageCompressionQuality($this->quality);
        }
    }

    /**
     * Set the output image format.
     *
     * @param mixed $targetMime The output MIME type
     */
    private function setFormat(?string $targetMime = null): void
    {
        if ($targetMime) {
            $format = array_search($targetMime, $this->mimeMap);
            if ($format) {
                $this->source->setImageFormat($format);
            }
        }
    }
}
