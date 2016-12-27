<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2007 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfImageMagickAdapter provides a mechanism for creating thumbnail images.
 * @see http://www.imagemagick.org
 *
 * @package    sfThumbnailPlugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Benjamin Meynell <bmeynell@colorado.edu>
 */

class sfImageMagickAdapter
{

  protected
    $sourceWidth,
    $sourceHeight,
    $sourceMime,
    $maxWidth,
    $maxHeight,
    $scale,
    $inflate,
    $quality,
    $source,
    $magickCommands;

  /**
   * Mime types this adapter supports
   */
  protected $imgTypes = array(
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
  );

  /**
   * Imagemagick-specific Type to Mime type map
   */
  protected $mimeMap = array(
    'bmp'   => 'image/bmp',
    'bmp2'  => 'image/bmp',
    'bmp3'  => 'image/bmp',
    'cur'   => 'image/x-win-bitmap',
    'dcx'   => 'image/dcx',
    'epdf'  => 'application/pdf',
    'epi'   => 'application/postscript',
    'eps'   => 'application/postscript',
    'eps2'  => 'application/postscript',
    'eps3'  => 'application/postscript',
    'epsf'  => 'application/postscript',
    'epsi'  => 'application/postscript',
    'ept'   => 'application/postscript',
    'ept2'  => 'application/postscript',
    'ept3'  => 'application/postscript',
    'fax'   => 'image/g3fax',
    'fits'  => 'image/x-fits',
    'g3'    => 'image/g3fax',
    'gif'   => 'image/gif',
    'gif87' => 'image/gif',
    'icb'   => 'application/x-icb',
    'ico'   => 'image/x-win-bitmap',
    'icon'  => 'image/x-win-bitmap',
    'jng'   => 'image/jng',
    'jpeg'  => 'image/jpeg',
    'jpg'   => 'image/jpeg',
    'm2v'   => 'video/mpeg2',
    'miff'  => 'application/x-mif',
    'mng'   => 'video/mng',
    'mpeg'  => 'video/mpeg',
    'mpg'   => 'video/mpeg',
    'otb'   => 'image/x-otb',
    'p7'    => 'image/x-xv',
    'palm'  => 'image/x-palm',
    'pbm'   => 'image/pbm',
    'pcd'   => 'image/pcd',
    'pcds'  => 'image/pcd',
    'pcl'   => 'application/pcl',
    'pct'   => 'image/pict',
    'pcx'   => 'image/x-pcx',
    'pdb'   => 'application/vnd.palm',
    'pdf'   => 'application/pdf',
    'pgm'   => 'image/x-pgm',
    'picon' => 'image/xpm',
    'pict'  => 'image/pict',
    'pjpeg' => 'image/pjpeg',
    'png'   => 'image/png',
    'png24' => 'image/png',
    'png32' => 'image/png',
  );

  public function __construct($maxWidth, $maxHeight, $scale, $inflate, $quality, $options)
  {
    $this->magickCommands = array();
    $this->magickCommands['convert'] = isset($options['convert']) ? escapeshellcmd($options['convert']) : 'convert';
    $this->magickCommands['identify'] = isset($options['identify']) ? escapeshellcmd($options['identify']) : 'identify';

    exec($this->magickCommands['convert'], $stdout);
    if (strpos($stdout[0], 'ImageMagick') === false)
    {
      throw new Exception(sprintf("ImageMagick convert command not found"));
    }

    exec($this->magickCommands['identify'], $stdout);
    if (strpos($stdout[0], 'ImageMagick') === false)
    {
      throw new Exception(sprintf("ImageMagick identify command not found"));
    }

    $this->maxWidth = $maxWidth;
    $this->maxHeight = $maxHeight;
    $this->scale = $scale;
    $this->inflate = $inflate;
    $this->quality = $quality;
    $this->options = $options;
  }

  public function toString($thumbnail, $targetMime = null)
  {
    ob_start();
    $this->save($thumbnail, null, $targetMime);

    return ob_get_clean();
  }

  public function toResource()
  {
    throw new Exception('The ImageMagick adapter does not support the toResource method.');
  }

  public function loadFile($thumbnail, $image)
  {
    // try and use getimagesize()
    // on failure, use identify instead
    $imgData = @getimagesize($image);
    if (!$imgData)
    {
      // Get MIME from php finfo and save to sourceMime property.
      // 'sourceMime' needs to be set before running getExtract.
      $this->sourceMime = $this->getMimeType($image);
      $extract = $this->getExtract($image);
      exec($this->magickCommands['identify'].' '.escapeshellarg($image).$extract, $stdout, $retval);
      if ($retval === 1)
      {
        throw new Exception('Image could not be identified.');
      }
      else
      {
        // get image data via identify
        list($img, $type, $dimen) = explode(' ', $stdout[0]);
        list($width, $height) = explode('x', $dimen);

        $this->sourceWidth = $width;
        $this->sourceHeight = $height;
      }
    }
    else
    {
      // use image data from getimagesize()
      $this->sourceWidth = $imgData[0];
      $this->sourceHeight = $imgData[1];
      $this->sourceMime = $imgData['mime'];
    }
    $this->image = $image;

    // open file resource
    $source = fopen($image, 'r');

    $this->source = $source;

    $thumbnail->initThumb($this->sourceWidth, $this->sourceHeight, $this->maxWidth, $this->maxHeight, $this->scale, $this->inflate);

    return true;
  }

  /**
   * Determine file mime type using the PHP fileinfo library.
   *
   * @param string  file we want the mime type for
   *
   * @return string  Mime type
   */
  public static function getMimeType($file)
  {
    // Use fileinfo to figure out file mimetype.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    return finfo_file($finfo, $file);
  }

  public function loadData($thumbnail, $image, $mime)
  {
    throw new Exception('This function is not yet implemented. Try a different adapter.');
  }

  public function save($thumbnail, $thumbDest, $targetMime = null)
  {
    $command = '';
    $width  = $this->sourceWidth;
    $height = $this->sourceHeight;
    $x = $y = 0;
    switch (@$this->options['method'])
    {
      case "shave_all":
        $proportion['source'] = $width / $height;
        $proportion['thumb'] = $thumbnail->getThumbWidth() / $thumbnail->getThumbHeight();

        if ($proportion['source'] > 1 && $proportion['thumb'] < 1)
        {
          $x = ($width - $height * $proportion['thumb']) / 2;
        }
        else
        {
          if ($proportion['source'] > $proportion['thumb'])
          {
            $x = ($width - $height * $proportion['thumb']) / 2;
          }
          else
          {
            $y = ($height - $width / $proportion['thumb']) / 2;
          }
        }

        $command = sprintf(" -shave %dx%d", $x, $y);
        break;

      case "shave_bottom":
        if ($width > $height)
        {
          $x = ceil(($width - $height) / 2 );
          $width = $height;
        }
        elseif ($height > $width)
        {
          $y = 0;
          $height = $width;
        }

        if (is_null($thumbDest))
        {
          $command = sprintf(
            " -crop %dx%d+%d+%d %s '-' | %s",
            $width, $height,
            $x, $y,
            escapeshellarg($this->image),
            $this->magickCommands['convert']
          );

          $this->image = '-';
        }
        else
        {
          $command = sprintf(
            " -crop %dx%d+%d+%d %s %s && %s",
            $width, $height,
            $x, $y,
            escapeshellarg($this->image), escapeshellarg($thumbDest),
            $this->magickCommands['convert']
          );

          $this->image = $thumbDest;
        }

        break;
      case 'custom':
      	$coords = $this->options['coords'];
      	if (empty($coords)) break;

      	$x = $coords['x1'];
      	$y = $coords['y1'];
      	$width = $coords['x2'] - $coords['x1'];
      	$height = $coords['y2'] - $coords['y1'];

        if (is_null($thumbDest))
        {
          $command = sprintf(
            " -crop %dx%d+%d+%d %s '-' | %s",
            $width, $height,
            $x, $y,
            escapeshellarg($this->image),
            $this->magickCommands['convert']
          );

          $this->image = '-';
        }
        else
        {
          $command = sprintf(
            " -crop %dx%d+%d+%d %s %s && %s",
            $width, $height,
            $x, $y,
            escapeshellarg($this->image), escapeshellarg($thumbDest),
            $this->magickCommands['convert']
          );

          $this->image = $thumbDest;
        }
      	break;
    } // end switch

    $command .= ' -thumbnail ';
    $command .= $thumbnail->getThumbWidth().'x'.$thumbnail->getThumbHeight();

    // See Qubit issue 2380
    if ('application/pdf' == $this->getSourceMime())
    {
      $command .= ' -background white -flatten ';
    }

    // absolute sizing
    if (!$this->scale)
    {
      $command .= '!';
    }

    if ($this->quality && $targetMime == 'image/jpeg')
    {
      $command .= ' -quality '.$this->quality.'% ';
    }

    // extract images such as pages from a pdf doc
    $extract = $this->getExtract($this->image);

    $output = (is_null($thumbDest))?'-':$thumbDest;
    $output = (($mime = array_search($targetMime, $this->mimeMap))?$mime.':':'').$output;

    $cmd = $this->magickCommands['convert'].' '.$command.' '.escapeshellarg($this->image).$extract.' '.escapeshellarg($output);
    (is_null($thumbDest))?passthru($cmd):exec($cmd);
  }

  public function freeSource()
  {
    if (is_resource($this->source))
    {
      fclose($this->source);
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
   * If failure, this method returns 0.
   */
  private function getCount($image)
  {
    $extension = pathinfo($image, PATHINFO_EXTENSION);

    // If processing a PDF, attempt to use pdfinfo as it's faster
    if ('application/pdf' == $this->getSourceMime())
    {
      return sfImageMagickAdapter::getPdfPageCount($image);
    }

    $command = $this->magickCommands['identify'].' -format %n '.escapeshellarg($image);
    exec($command, $stdout, $retval);
    if ($retval === 1)
    {
      throw new Exception('Image could not be identified.');
    }

    return intval(@$stdout[0]);
  }

  public static function pdfinfoToolAvailable()
  {
    return !empty(shell_exec('which pdfinfo'));
  }

  public static function getPdfPageCount($filename)
  {
    // Default to 1 if pdfinfo not installed
    if (!sfImageMagickAdapter::pdfinfoToolAvailable())
    {
      return 1;
    }

    return sfImageMagickAdapter::getPdfinfoPageCount($filename);
  }

  public static function getPdfinfoPageCount($filename)
  {
    exec('pdfinfo '. escapeshellarg($filename), $stdout, $retval);

    if ($retval === 1)
    {
      throw new Exception('PDF could not be analyzed.');
    }

    // Parse page number from output
    foreach ($stdout as $line)
    {
      if (preg_match('/Pages:\s*(\d+)/i', $line, $matches) === 1)
      {
        return intval($matches[1]);
      }
    }

    throw new Exception('PDF analysis incomplete.');
  }

  private function getExtract($image, array $options = array())
  {
    $extract = '';
    if (empty($this->options['extract']) && !is_int($this->options['extract']))
    {
      return $extract;
    }

    // Make sure that we are no trying to extract a page that is out of the
    // range. If so, we'll extract the last page of the document.
    try
    {
      $count = $this->getCount($image);
      if ($count > 0 && $count < $this->options['extract'])
      {
        $this->options['extract'] = $count;
      }
    }
    catch (Exception $e)
    {
      // It defaults to the first page
      $this->options['extract'] = 0;
    }

    // ImageMagick's initial element index is zero
    $n = $this->options['extract'];
    if ($n > 0)
    {
      $n--;
    }

    $extract = '['.escapeshellarg($n).'] ';

    return $extract;
  }
}
