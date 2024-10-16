<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2007 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfThumbnail provides a mechanism for creating thumbnail images.
 *
 * This is taken from Harry Fueck's Thumbnail class and
 * converted for PHP5 strict compliance for use with symfony.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Benjamin Meynell <bmeynell@colorado.edu>
 */
class sfThumbnail
{
    /**
     * Width of thumbnail in pixels.
     */
    protected $thumbWidth;

    /**
     * Height of thumbnail in pixels.
     */
    protected $thumbHeight;

    /**
     * Temporary file if the source is not local.
     */
    protected $tempFile;

    /**
     * Thumbnail constructor.
     *
     * @param int (optional) max width of thumbnail
     * @param int (optional) max height of thumbnail
     * @param bool (optional) if true image scales
     * @param bool (optional) if true inflate small images
     * @param string (optional) adapter class name
     * @param array (optional) adapter options
     * @param null|mixed $maxWidth
     * @param null|mixed $maxHeight
     * @param mixed      $scale
     * @param mixed      $inflate
     * @param mixed      $quality
     * @param null|mixed $adapterClass
     * @param mixed      $adapterOptions
     */
    public function __construct($maxWidth = null, $maxHeight = null, $scale = true, $inflate = true, $quality = 75, $adapterClass = null, $adapterOptions = [])
    {
        if (!$adapterClass) {
            if (extension_loaded('gd')) {
                $adapterClass = 'sfGDAdapter';
            } else {
                $adapterClass = 'sfImageMagickAdapter';
            }
        }
        $this->adapter = new $adapterClass($maxWidth, $maxHeight, $scale, $inflate, $quality, $adapterOptions);
    }

    public function __destruct()
    {
        $this->freeAll();
    }

    /**
     * Loads an image from a file or URL and creates an internal thumbnail out of it.
     *
     * @param string filename (with absolute path) of the image to load. If the filename is a http(s) URL, then an attempt to download the file will be made.
     * @param mixed $image
     *
     * @return bool True if the image was properly loaded
     *
     * @throws Exception If the image cannot be loaded, or if its mime type is not supported
     */
    public function loadFile($image)
    {
        if (preg_match('/http(s)?:\//i', $image)) {
            if (class_exists('sfWebBrowser')) {
                if (!is_null($this->tempFile)) {
                    unlink($this->tempFile);
                }
                $this->tempFile = tempnam('/tmp', 'sfThumbnailPlugin');

                $b = new sfWebBrowser();

                try {
                    $b->get($image);
                    if (200 != $b->getResponseCode()) {
                        throw new Exception(sprintf('%s returned error code %s', $image, $b->getResponseCode()));
                    }
                    file_put_contents($this->tempFile, $b->getResponseText());
                    if (!filesize($this->tempFile)) {
                        throw new Exception('downloaded file is empty');
                    }
                    $image = $this->tempFile;
                } catch (Exception $e) {
                    throw new Exception('Source image is a URL but it cannot be used because '.$e->getMessage());
                }
            } else {
                throw new Exception('Source image is a URL but sfWebBrowserPlugin is not installed');
            }
        } else {
            if (!is_readable($image)) {
                throw new Exception(sprintf('The file "%s" is not readable.', $image));
            }
        }

        $this->adapter->loadFile($this, $image);
    }

    /**
     * Loads an image from a string (e.g. database) and creates an internal thumbnail out of it.
     *
     * @param string the image string (must be a format accepted by imagecreatefromstring())
     * @param string mime type of the image
     * @param mixed $image
     * @param mixed $mime
     *
     * @return bool True if the image was properly loaded
     *
     * @throws Exception If image mime type is not supported
     */
    public function loadData($image, $mime)
    {
        $this->adapter->loadData($this, $image, $mime);
    }

    /**
     * Saves the thumbnail to the filesystem
     * If no target mime type is specified, the thumbnail is created with the same mime type as the source file.
     *
     * @param string the image thumbnail file destination (with absolute path)
     * @param string The mime-type of the thumbnail (possible values are 'image/jpeg', 'image/png', and 'image/gif')
     * @param mixed      $thumbDest
     * @param null|mixed $targetMime
     */
    public function save($thumbDest, $targetMime = null)
    {
        $this->adapter->save($this, $thumbDest, $targetMime);
    }

    /**
     * Returns the thumbnail as a string
     * If no target mime type is specified, the thumbnail is created with the same mime type as the source file.
     *
     * @param string The mime-type of the thumbnail (possible values are adapter dependent)
     * @param null|mixed $targetMime
     *
     * @return string
     */
    public function toString($targetMime = null)
    {
        return $this->adapter->toString($this, $targetMime);
    }

    public function toResource()
    {
        return $this->adapter->toResource($this);
    }

    public function freeSource()
    {
        if (!is_null($this->tempFile)) {
            unlink($this->tempFile);
        }
        $this->adapter->freeSource();
    }

    public function freeThumb()
    {
        $this->adapter->freeThumb();
    }

    public function freeAll()
    {
        $this->adapter->freeSource();
        $this->adapter->freeThumb();
    }

    /**
     * Returns the width of the thumbnail.
     */
    public function getThumbWidth()
    {
        return $this->thumbWidth;
    }

    /**
     * Returns the height of the thumbnail.
     */
    public function getThumbHeight()
    {
        return $this->thumbHeight;
    }

    /**
     * Returns the mime type of the source image.
     */
    public function getMime()
    {
        return $this->adapter->getSourceMime();
    }

    /**
     * Computes the thumbnail width and height
     * Used by adapter.
     *
     * @param mixed $sourceWidth
     * @param mixed $sourceHeight
     * @param mixed $maxWidth
     * @param mixed $maxHeight
     * @param mixed $scale
     * @param mixed $inflate
     */
    public function initThumb($sourceWidth, $sourceHeight, $maxWidth, $maxHeight, $scale, $inflate)
    {
        if ($maxWidth > 0) {
            $ratioWidth = $maxWidth / $sourceWidth;
        }
        if ($maxHeight > 0) {
            $ratioHeight = $maxHeight / $sourceHeight;
        }

        if ($scale) {
            if ($maxWidth && $maxHeight) {
                $ratio = ($ratioWidth < $ratioHeight) ? $ratioWidth : $ratioHeight;
            }
            if ($maxWidth xor $maxHeight) {
                $ratio = (isset($ratioWidth)) ? $ratioWidth : $ratioHeight;
            }
            if ((!$maxWidth && !$maxHeight) || (!$inflate && $ratio > 1)) {
                $ratio = 1;
            }

            $this->thumbWidth = floor($ratio * $sourceWidth);
            $this->thumbHeight = ceil($ratio * $sourceHeight);
        } else {
            if (!isset($ratioWidth) || (!$inflate && $ratioWidth > 1)) {
                $ratioWidth = 1;
            }
            if (!isset($ratioHeight) || (!$inflate && $ratioHeight > 1)) {
                $ratioHeight = 1;
            }
            $this->thumbWidth = floor($ratioWidth * $sourceWidth);
            $this->thumbHeight = ceil($ratioHeight * $sourceHeight);
        }
    }
}
