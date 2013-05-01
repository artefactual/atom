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
 */

/**
 * Extend functionality of propel generated "BaseDigitalObject" class
 *
 * @package    AccesstoMemory
 * @subpackage model
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitDigitalObject extends BaseDigitalObject
{
  const

    // Directory for generic icons
    GENERIC_ICON_DIR = 'generic-icons',

    // Mime-type for thumbnails (including reference image)
    THUMB_MIME_TYPE = 'image/jpeg',

    THUMB_EXTENSION = 'jpg';

  // Variables for save actions
  public
    $assets = array(),
    $indexOnSave = true, // Flag for updating search index on save or delete
    $createDerivatives = true;

  /*
   * The following mime-type array is taken from the Gallery 2 project
   * http://gallery.menalto.com
   */
  public static
    $qubitMimeTypes = array(

      /* This data was lifted from Apache's mime.types listing. */
      'z' => 'application/x-compress',
      'ai' => 'application/postscript',
      'aif' => 'audio/x-aiff',
      'aifc' => 'audio/x-aiff',
      'aiff' => 'audio/x-aiff',
      'asc' => 'text/plain',
      'au' => 'audio/basic',
      'avi' => 'video/x-msvideo',
      'bcpio' => 'application/x-bcpio',
      'bin' => 'application/octet-stream',
      'bmp' => 'image/bmp',
      'cdf' => 'application/x-netcdf',
      'class' => 'application/octet-stream',
      'cpio' => 'application/x-cpio',
      'cpt' => 'application/mac-compactpro',
      'csh' => 'application/x-csh',
      'css' => 'text/css',
      'dcr' => 'application/x-director',
      'dir' => 'application/x-director',
      'djv' => 'image/vnd.djvu',
      'djvu' => 'image/vnd.djvu',
      'dll' => 'application/octet-stream',
      'dms' => 'application/octet-stream',
      'doc' => 'application/msword',
      'dvi' => 'application/x-dvi',
      'dxr' => 'application/x-director',
      'eps' => 'application/postscript',
      'etx' => 'text/x-setext',
      'exe' => 'application/octet-stream',
      'ez' => 'application/andrew-inset',
      'gif' => 'image/gif',
      'gtar' => 'application/x-gtar',
      'gz' => 'application/x-gzip',
      'hdf' => 'application/x-hdf',
      'hqx' => 'application/mac-binhex40',
      'ice' => 'x-conference/x-cooltalk',
      'ief' => 'image/ief',
      'iges' => 'model/iges',
      'igs' => 'model/iges',
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpe' => 'image/jpeg',
      'js' => 'application/x-javascript',
      'kar' => 'audio/midi',
      'latex' => 'application/x-latex',
      'lha' => 'application/octet-stream',
      'lzh' => 'application/octet-stream',
      'm3u' => 'audio/x-mpegurl',
      'man' => 'application/x-troff-man',
      'me' => 'application/x-troff-me',
      'mesh' => 'model/mesh',
      'mid' => 'audio/midi',
      'midi' => 'audio/midi',
      'mif' => 'application/vnd.mif',
      'mov' => 'video/quicktime',
      'movie' => 'video/x-sgi-movie',
      'mp2' => 'audio/mpeg',
      'mp3' => 'audio/mpeg',
      'mpe' => 'video/mpeg',
      'mpeg' => 'video/mpeg',
      'mpg' => 'video/mpeg',
      'mpga' => 'audio/mpeg',
      'ms' => 'application/x-troff-ms',
      'msh' => 'model/mesh',
      'mxu' => 'video/vnd.mpegurl',
      'nc' => 'application/x-netcdf',
      'oda' => 'application/oda',
      'pbm' => 'image/x-portable-bitmap',
      'pdb' => 'chemical/x-pdb',
      'pdf' => 'application/pdf',
      'pgm' => 'image/x-portable-graymap',
      'pgn' => 'application/x-chess-pgn',
      'png' => 'image/png',
      'pnm' => 'image/x-portable-anymap',
      'ppm' => 'image/x-portable-pixmap',
      'ppt' => 'application/vnd.ms-powerpoint',
      'ps' => 'application/postscript',
      'qt' => 'video/quicktime',
      'ra' => 'audio/x-realaudio',
      'ram' => 'audio/x-pn-realaudio',
      'ras' => 'image/x-cmu-raster',
      'rgb' => 'image/x-rgb',
      'rm' => 'audio/x-pn-realaudio',
      'roff' => 'application/x-troff',
      'rpm' => 'audio/x-pn-realaudio-plugin',
      'rtf' => 'text/rtf',
      'rtx' => 'text/richtext',
      'sgm' => 'text/sgml',
      'sgml' => 'text/sgml',
      'sh' => 'application/x-sh',
      'shar' => 'application/x-shar',
      'silo' => 'model/mesh',
      'sit' => 'application/x-stuffit',
      'skd' => 'application/x-koan',
      'skm' => 'application/x-koan',
      'skp' => 'application/x-koan',
      'skt' => 'application/x-koan',
      'smi' => 'application/smil',
      'smil' => 'application/smil',
      'snd' => 'audio/basic',
      'so' => 'application/octet-stream',
      'spl' => 'application/x-futuresplash',
      'src' => 'application/x-wais-source',
      'sv4cpio' => 'application/x-sv4cpio',
      'sv4crc' => 'application/x-sv4crc',
      'svg' => 'image/svg+xml',
      'swf' => 'application/x-shockwave-flash',
      't' => 'application/x-troff',
      'tar' => 'application/x-tar',
      'tcl' => 'application/x-tcl',
      'tex' => 'application/x-tex',
      'texi' => 'application/x-texinfo',
      'texinfo' => 'application/x-texinfo',
      'tif' => 'image/tiff',
      'tiff' => 'image/tiff',
      'tr' => 'application/x-troff',
      'tsv' => 'text/tab-separated-values',
      'txt' => 'text/plain',
      'ustar' => 'application/x-ustar',
      'vcd' => 'application/x-cdlink',
      'vrml' => 'model/vrml',
      'vsd' => 'application/vnd.visio',
      'wav' => 'audio/x-wav',
      'wbmp' => 'image/vnd.wap.wbmp',
      'wbxml' => 'application/vnd.wap.wbxml',
      'wml' => 'text/vnd.wap.wml',
      'wmlc' => 'application/vnd.wap.wmlc',
      'wmls' => 'text/vnd.wap.wmlscript',
      'wmlsc' => 'application/vnd.wap.wmlscriptc',
      'wrl' => 'model/vrml',
      'xbm' => 'image/x-xbitmap',
      'xls' => 'application/vnd.ms-excel',
      'xpm' => 'image/x-xpixmap',
      'xsl' => 'text/xml',
      'xwd' => 'image/x-xwindowdump',
      'xyz' => 'chemical/x-xyz',
      'zip' => 'application/zip',

      /* And additions from Gallery2 - http://codex.gallery2.org  */
      /* From support.microsoft.com/support/kb/articles/Q284/0/94.ASP */
      'asf' => 'video/x-ms-asf',
      'asx' => 'video/x-ms-asx',
      'wmv' => 'video/x-ms-wmv',
      'wma' => 'audio/x-ms-wma',

      /* JPEG 2000: From RFC 3745: http://www.faqs.org/rfcs/rfc3745.html */
      'jp2' => 'image/jp2',
      'jpg2' => 'image/jp2',
      'jpf' => 'image/jpx',
      'jpx' => 'image/jpx',
      'mj2' => 'video/mj2',
      'mjp2' => 'video/mj2',
      'jpm' => 'image/jpm',
      'jpgm' => 'image/jpgm',

      /* Other */
      'psd' => 'application/photoshop',
      'pcd' => 'image/x-photo-cd',
      'jpgcmyk' => 'image/jpeg-cmyk',
      'tifcmyk' => 'image/tiff-cmyk',
      'wmf' => 'image/wmf',
      'tga' => 'image/tga',
      'flv' => 'video/x-flv',
      'mp4' => 'video/mp4',
      'tgz' => 'application/x-compressed');

  // Temporary path for local copy of an external object (see importFromUri
  // method)
  protected
    $localPath;

  // List of web compatible image formats (supported in most major browsers)
  protected static
    $webCompatibleImageFormats = array(
      'image/jpeg',
      'image/jpg',
      'image/jpe',
      'image/gif',
      'image/png'),

    // Qubit generic icon list
    $qubitGenericThumbs = array(
      'application/x-msaccess'        => 'icon-ms-access.gif',
      'application/vnd.ms-excel'      => 'icon-ms-excel.gif',
      'application/msword'            => 'icon-ms-word.gif',
      'application/vnd.ms-powerpoint' => 'icon-ms-powerpoint.gif'),

    $qubitGenericReference = array(
      '*/*' => 'no_reference_rep.png');

  public function __toString()
  {
    return (string) $this->name;
  }

  public function __get($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    switch ($name)
    {
      case 'thumbnail':

        if (!isset($this->values['thumbnail']))
        {
          $criteria = new Criteria;
          $criteria->add(QubitDigitalObject::PARENT_ID, $this->__get('id'));
          $criteria->add(QubitDigitalObject::USAGE_ID, QubitTerm::THUMBNAIL_ID);

          $this->values['thumbnail'] = QubitDigitalObject::get($criteria)->offsetGet(0);
        }

        return $this->values['thumbnail'];

      case 'reference':

        if (!isset($this->values['reference']))
        {
          $criteria = new Criteria;
          $criteria->add(QubitDigitalObject::PARENT_ID, $this->__get('id'));
          $criteria->add(QubitDigitalObject::USAGE_ID, QubitTerm::REFERENCE_ID);

          $this->values['reference'] = QubitDigitalObject::get($criteria)->offsetGet(0);
        }

        return $this->values['reference'];
    }

    return call_user_func_array(array($this, 'BaseDigitalObject::__get'), $args);
  }

  protected function insert($connection = null)
  {
    if (!isset($this->slug))
    {
      $this->slug = QubitSlug::slugify($this->__get('name', array('sourceCulture' => true)));
    }

    return parent::insert($connection);
  }

  public function save($connection = null)
  {
    // TODO: $cleanInformationObject = $this->informationObject->clean;
    $cleanInformationObjectId = $this->__get('informationObjectId', array('clean' => true));

    // Write assets to storage device
    if (0 < count($this->assets))
    {
      foreach ($this->assets as $asset)
      {
        if (null == $this->getChecksum() || $asset->getChecksum() != $this->getChecksum())
        {
          $this->writeToFileSystem($asset);
        }

        // TODO: allow setting multiple assets for different usage types
        // (e.g. a master, thumbnail and reference image)
        break;
      }
    }

    parent::save($connection);

    // Create child objects (derivatives)
    if (0 < count($this->assets) && $this->createDerivatives)
    {
      if (sfConfig::get('app_explode_multipage_files') && $this->getPageCount() > 1)
      {
        // If DO is a compound object, then create child objects and set to
        // display as compound object (with pager)
        $this->createCompoundChildren($connection);

        // Set parent digital object to be displayed as compound
        $this->setDisplayAsCompoundObject(1);

        // We don't need reference image because a compound will be displayed instead of it
        // But thumbnails are necessary for image flow
        $this->createThumbnail($connection);

        // Extract text and attach to parent digital object
        $this->extractText($connection);
      }
      else
      {
        // If DO is a single object, create various representations based on
        // intended usage
        $this->createRepresentations($this->usageId, $connection);
      }
    }

    // Add watermark to reference image
    if (QubitTerm::REFERENCE_ID == $this->usageId
        && $this->isImage()
        && is_readable($waterMarkPathName = sfConfig::get('sf_web_dir').'/watermark.png')
        && is_file($waterMarkPathName))
    {
      $filePathName = $this->getAbsolutePath();
      $command = 'composite -dissolve 15 -tile '.$waterMarkPathName.' '.escapeshellarg($filePathName).' '.escapeshellarg($filePathName);
      exec($command);
    }

    // Update search index for related info object
    if ($this->indexOnSave)
    {
      if ($this->informationObjectId != $cleanInformationObjectId && null !== QubitInformationObject::getById($cleanInformationObjectId))
      {
        QubitSearch::getInstance()->update(QubitInformationObject::getById($cleanInformationObjectId));
      }

      if (isset($this->informationObject))
      {
        QubitSearch::getInstance()->update($this->informationObject);
      }
    }

    return $this;
  }

  /**
   * Override base delete method to unlink related digital assets (thumbnail
   * and file)
   *
   * @param  sfConnection  A database connection
   */
  public function delete($connection = null)
  {
    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::PARENT_ID, $this->id);

    $children = QubitDigitalObject::get($criteria);

    // Delete children
    foreach ($children as $child)
    {
      foreach (QubitRelation::getBySubjectOrObjectId($this->id) as $item)
      {
        $item->delete();
      }

      $child->delete();
    }

    // Delete digital asset
    if (file_exists($this->getAbsolutePath()))
    {
      unlink($this->getAbsolutePath());
    }

    // Prune asset directory, if empty
    self::pruneEmptyDirs(sfConfig::get('sf_web_dir').$this->path);

    foreach (QubitRelation::getBySubjectOrObjectId($this->id) as $item)
    {
      $item->delete();
    }

    // Update search index before deleting self
    if (!empty($this->informationObjectId))
    {
      QubitSearch::getInstance()->update($this->getInformationObject());
    }

    // Delete self
    parent::delete($connection);
  }

  /**
   * Create a digital object representation of an asset
   *
   * @param mixed parent object (digital object or information object)
   * @param QubitAsset asset to represent
   * @param array options array of optional paramaters
   * @return QubitDigitalObject
   */
  public function writeToFileSystem($asset, $options = array())
  {
    // Fail if filename is empty
    if (0 == strlen($asset->getName()))
    {
      throw new sfException('Not a valid filename');
    }

    // Fail if "thumbnail" is not an image
    if (QubitTerm::THUMBNAIL_ID == $this->usageId && !QubitDigitalObject::isImageFile($asset->getName()))
    {
      throw new sfException('Thumbnail must be valid image type (jpeg, png, gif)');
    }

    // Get clean file name (no bad chars)
    $cleanFileName = self::sanitizeFilename($asset->getName());

    // If file has not extension, try to get it from asset mime type
    if (0 == strlen(pathinfo($cleanFileName, PATHINFO_EXTENSION)) && null !== ($assetMimeType = $asset->mimeType) && 0 < strlen(($newFileExtension = array_search($assetMimeType, self::$qubitMimeTypes))))
    {
      $cleanFileName .= '.'.$newFileExtension;
    }

    // Upload paths for this information object / digital object
    $infoObjectPath = $this->getAssetPath();
    $filePath       = sfConfig::get('sf_web_dir').$infoObjectPath.'/';
    $relativePath   = $infoObjectPath.'/';
    $filePathName   = $filePath.$cleanFileName;

    // make the target directory if necessary
    // NB: this will always return false if the path exists
    if (!file_exists($filePath))
    {
      mkdir($filePath, 0755, true);
    }

    // Write file
    // If the asset contents are not included but referred, move or copy
    if (null !== $assetPath = $asset->getPath())
    {
      if (false === @copy($assetPath, $filePathName))
      {
        throw new sfException('File write to '.$filePathName.' failed. See setting directory and file permissions documentation.');
      }
    }
    // If the asset contents are included (HTTP upload)
    else if (false === file_put_contents($filePathName, $asset->getContents()))
    {
      throw new sfException('File write to '.$filePathName.' failed. See setting directory and file permissions documentation.');
    }

    // Test asset checksum against generated checksum from file
    $this->generateChecksumFromFile($filePathName);
    if ($this->getChecksum() != $asset->getChecksum())
    {
      unlink($filePathName);
      rmdir($infoObjectPath);

      throw new sfException('Checksum values did not validate: '. $filePathName);
    }

    // set file permissions
    if (!chmod($filePathName, 0644))
    {
      throw new sfException('Failed to set permissions on '.$filePathName);
    }

    // Iterate through new directories and set permissions (mkdir() won't do this properly)
    $pathToDir = sfConfig::get('sf_web_dir');
    foreach (explode('/', $infoObjectPath) as $dir)
    {
      $pathToDir .= '/'.$dir;
      @chmod($pathToDir, 0755);
    }

    // Save digital object in database
    $this->setName($cleanFileName);
    $this->setPath($relativePath);
    $this->setByteSize(filesize($filePathName));
    $this->setMimeAndMediaType();

    return $this;
  }

  /**
   * Populate a digital object from a resource pointed to by a URI
   * This is for, eg. importing encoded digital objects from XML
   *
   * @param string  $uri  URI pointing to the resource
   * @return boolean  success or failure
   */
  public function importFromURI($uri, $options = array())
  {
    // Parse URL into components and get file/base name
    $uriComponents = parse_url($uri);

    // Initialize web browser
    $browser = new sfWebBrowser(array(), null, array('Timeout' => 10));

    // Add asset to digital object assets array
    if (true !== $browser->get($uri)->responseIsError() && 0 < strlen(($filename = basename($uriComponents['path']))))
    {
      $asset = new QubitAsset($uri, $browser->getResponseText());

      $this->assets[] = $asset;
    }
    else
    {
      throw new sfException('Encountered error fetching external resource.');
    }

    // Set digital object as external URI
    $this->usageId = QubitTerm::EXTERNAL_URI_ID;

    // Save filestream temporary, because sfImageMagickAdapter does not support load data from streams
    $this->localPath = Qubit::saveTemporaryFile($filename, $asset->getContents());

    $this->name = $filename;
    $this->path = $uri;
    $this->checksum = $asset->getChecksum();
    $this->checksumType = $asset->getChecksumAlgorithm();
    $this->byteSize = strlen($browser->getResponseText());
    $this->setMimeAndMediaType();
  }

  /**
   * Populate a digital object from a base64-encoded character stream.
   * This is for, eg. importing encoded digital objects from XML
   *
   * @param string  $encodedString  base64-encoded string
   * @return boolean  success or failure
   */
  public function importFromBase64($encodedString, $filename, $options = array())
  {
    $fileContents = base64_decode($encodedString);

    if (0 < strlen($fileContents))
    {
      $asset = new QubitAsset($filename, $fileContents);
    }
    else
    {
      throw new sfException('Could not read the file contents');
    }

    $this->assets[] = $asset;
  }

  /**
   * Remove undesirable characters from a filename
   *
   * @param string $filename incoming file name
   * @return string sanitized filename
   */
  protected static function sanitizeFilename($filename)
  {
    return preg_replace('/[^a-z0-9_\.-]/i', '_', $filename);
  }

  /**
   * Get count of digital objects by media-type
   */
  public static function getCount($mediaTypeId)
  {
    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::PARENT_ID, null, Criteria::ISNULL);

    $criteria->add(QubitDigitalObject::MEDIA_TYPE_ID, $mediaTypeId);
    $criteria->addJoin(QubitDigitalObject::INFORMATION_OBJECT_ID, QubitInformationObject::ID);
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    return BasePeer::doCount($criteria)->fetchColumn(0);
  }

  /**
   * Get path to asset, relative to sf_web_dir
   *
   * @return string  path to asset
   */
  public function getFullPath()
  {
    return $this->getPath().$this->getName();
  }

  /**
   * Get absolute path to asset
   *
   * @return string absolute path to asset
   */
  public function getAbsolutePath()
  {
    return sfConfig::get('sf_web_dir').$this->path.$this->name;
  }

  /**
   * Test that image will display in major web browsers
   *
   * @return boolean
   */
  public function isWebCompatibleImageFormat()
  {
    return in_array($this->mimeType, self::$webCompatibleImageFormats);
  }

  /**
   * Set Mime-type and Filetype all at once
   *
   */
  public function setMimeAndMediaType($mimeType = null)
  {
    if (null !== $mimeType)
    {
      $this->setMimeType($mimeType);
    }
    else
    {
      $this->setMimeType(QubitDigitalObject::deriveMimeType($this->getName()));
    }

    $this->setDefaultMediaType();
  }

  /**
   * Set default mediaTypeId based on digital asset's mime-type.  Media types
   * id's are defined in the QubitTerms db
   *
   * @return mixed  integer if mediatype mapped, null if no valid mapping
   */
  public function setDefaultMediaType()
  {
    // Make sure we have a valid mime-type (with a forward-slash).
    if (!strlen($this->mimeType) || !strpos($this->mimeType, '/'))
    {
      return null;
    }

    $mimePieces = explode('/', $this->mimeType);

    switch($mimePieces[0])
    {
      case 'audio':
        $mediaTypeId = QubitTerm::AUDIO_ID;
        break;
      case 'image':
        $mediaTypeId = QubitTerm::IMAGE_ID;
        break;
      case 'text':
        $mediaTypeId = QubitTerm::TEXT_ID;
        break;
      case 'video':
        $mediaTypeId = QubitTerm::VIDEO_ID;
        break;
      case 'application':
        switch ($mimePieces[1])
        {
          case 'pdf':
            $mediaTypeId = QubitTerm::TEXT_ID;
            break;
          default:
            $mediaTypeId = QubitTerm::OTHER_ID;
        }
        break;
      default:
        $mediaTypeId = QubitTerm::OTHER_ID;
    }

    $this->mediaTypeId = $mediaTypeId;
  }

  /**
   * Get this object's top ancestor, or self if it is the top of the branch
   *
   * return QubitInformationObject  Closest InformationObject ancestor
   */
  public function getTopAncestorOrSelf()
  {
    // Get the ancestor at array index "0"
    return $this->getAncestors()->andSelf()->offsetGet(0);
  }

  /**
   * Find *first* child of current digital object that matches $usageid.
   *
   * @param integer  Constant value from QubitTerm (THUMBNAIL_ID, REFERENCE_ID)
   */
  public function getChildByUsageId($usageId)
  {
    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::PARENT_ID, $this->id);
    $criteria->add(QubitDigitalObject::USAGE_ID, $usageId);

    $result = QubitDigitalObject::getOne($criteria);

    return $result;
  }

  /**
   * Get a representation for the given $usageId.  Currently only searches
   * direct children of current digital object.
   *
   * @param integer $usageId
   * @return mixed DigitalObject on success
   *
   * @todo look for matching usage id up and down object tree?
   */
  public function getRepresentationByUsage($usageId)
  {
    if ($usageId == $this->getUsageId())
    {
      return $this;
    }
    else
    {
      return $this->getChildByUsageId($usageId);
    }
  }

  /**
   * Return a compound representation for this digital object - generating the
   * rep if necessary
   *
   * @return QubitDigitalObject compound image representation
   */
  public function getCompoundRepresentation()
  {
    if (null === $compoundRep = $this->getRepresentationByUsage(QubitTerm::COMPOUND_ID))
    {
      // Generate a compound representation if one doesn't exist already
      $compoundRep = self::createImageDerivative(QubitTerm::COMPOUND_ID);
    }

    return $compoundRep;
  }

  /**
   * Determine if this digital object is an image, based on mimetype
   *
   * @return boolean
   */
  public function isImage()
  {
    return self::isImageFile($this->getName());
  }

  /**
   * Return true if this is a compound digital object
   *
   * @return boolean
   */
  public function isCompoundObject()
  {
    $isCompoundObjectProp = QubitProperty::getOneByObjectIdAndName($this->id, 'is_compound_object');

    return (null !== $isCompoundObjectProp && '1' == $isCompoundObjectProp->getValue(array('sourceCulture' => true)));
  }

  /**
   * Derive file path for a digital object asset
   *
   * All digital object paths are keyed by information object id that is the
   * nearest ancestor of the current digital object. Because we may not know
   * the id of the current digital object yet (i.e. it hasn't been saved to the
   * database yet), we pass the parent digital object or information object.
   *
   * To keep to a minimum the number of sub-directories in the uploads dir,
   * we break up information object path by using first and second digits of
   * the information object id as sub-directories (e.g. uploads/3/2/3235/).
   *
   * @return string  asset file path
   */
  public function getAssetPath()
  {
    if (isset($this->informationObject))
    {
      $infoObject = $this->informationObject;
    }
    else if (isset($this->parent))
    {
      $infoObject = $this->parent->informationObject;
    }

    if (!isset($infoObject))
    {
      throw new sfException('Couldn\'t find related information object for digital object');
    }

    $id = (string) $infoObject->id;

    // determine path for current repository
    $repoDir = '';
    if (null !== ($repo = $infoObject->getRepository(array('inherit' => true))))
    {
      $repoDir = $repo->slug;
    }
    else
    {
      $repoDir = 'null';
    }


    return '/'.QubitSetting::getByName('upload_dir')->__toString().'/r/'.$repoDir.'/'.$id[0].'/'.$id[1].'/'.$id;
  }

  /**
   * Get path to the appropriate generic icon for $mimeType
   *
   * @param string $mimeType
   * @return string
   */
  public static function getGenericIconPath($mimeType, $usageType)
  {
    $genericIconDir  = self::GENERIC_ICON_DIR;
    $matchedMimeType = null;

    switch ($usageType)
    {
      case QubitTerm::REFERENCE_ID:
      case QubitTerm::MASTER_ID:
        $genericIconList = QubitDigitalObject::$qubitGenericReference;
        break;
      default:
        $genericIconList = QubitDigitalObject::$qubitGenericThumbs;
    }

    if ('unknown' == $mimeType)
    {
      // Use "blank" icon for unknown file types
      return $genericIconPath = $genericIconDir.'/blank.png';
    }

    // Check the list for a generic icon matching this mime-type
    $mimeParts = explode('/', $mimeType);
    foreach ($genericIconList as $mimePattern => $icon)
    {
      $pattern = explode('/', $mimePattern);

      if (($mimeParts[0] == $pattern[0] || '*' == $pattern[0]) && ($mimeParts[1] == $pattern[1] || '*' == $pattern[1]))
      {
        $matchedMimeType = $mimePattern;
        break;
      }
    }

    if (null !== $matchedMimeType)
    {
      $genericIconPath = $genericIconDir.'/'.$genericIconList[$matchedMimeType];
    }
    else
    {
      // Use "blank" icon for unknown file types
      $genericIconPath = $genericIconDir.'/blank.png';
    }

    return $genericIconPath;
  }

  /**
   * Get a generic representation for the current digital object.
   *
   * @param string $mimeType
   * @return QubitDigitalObject
   */
  public static function getGenericRepresentation($mimeType, $usageType)
  {
    $representation = new QubitDigitalObject;
    $genericIconPath = QubitDigitalObject::getGenericIconPath($mimeType, $usageType);

    $representation->setPath(dirname($genericIconPath).'/');
    $representation->setName(basename($genericIconPath));

    return $representation;
  }

  /**
   * Derive a file's mime-type from it's filename extension.  The extension may
   * lie, but this should be "good enough" for the majority of cases.
   *
   * @param string   name of the file
   * @return string  mime-type of file (or "unknown" if no match)
   */
  public static function deriveMimeType($filename)
  {
    $mimeType     = 'unknown';
    $mimeTypeList = QubitDigitalObject::$qubitMimeTypes; // point to "master" mime-type array

    $filePieces = explode('.', basename($filename));
    array_splice($filePieces, 0, 1); // cut off "name" part of filename, leave extension(s)
    $rfilePieces = array_reverse($filePieces);  // Reverse the extension list

    // Go through extensions backwards, return value based on first hit
    // (assume last extension is most significant)
    foreach ($rfilePieces as $key => $ext)
    {
      $ext = strtolower($ext);  // Convert uppercase extensions to lowercase

      // Try to match this extension to a mime-type
      if (array_key_exists($ext, $mimeTypeList))
      {
        $mimeType = $mimeTypeList[$ext];
        break;
      }
    }

    return $mimeType;
  }

  /**
   * Create various representations for this digital object
   *
   * @param integer $usageId intended use of asset
   * @return QubitDigitalObject this object
   */
  public function createRepresentations($usageId, $connection = null)
  {
    switch ($this->mediaTypeId)
    {
      case QubitTerm::IMAGE_ID:
        // Scale images and create derivatives
        if ($this->canThumbnail())
        {
          if ($usageId == QubitTerm::EXTERNAL_URI_ID || $usageId == QubitTerm::MASTER_ID)
          {
            $this->createReferenceImage($connection);
            $this->createThumbnail($connection);
          }
          else if ($usageId == QubitTerm::REFERENCE_ID)
          {
            $this->resizeByUsageId(QubitTerm::REFERENCE_ID);
            $this->createThumbnail($connection);
          }
          else if ($usageId == QubitTerm::THUMBNAIL_ID)
          {
            $this->resizeByUsageId(QubitTerm::THUMBNAIL_ID);
          }
        }

        break;

      case QubitTerm::TEXT_ID:
        if ($usageId == QubitTerm::EXTERNAL_URI_ID || $usageId == QubitTerm::MASTER_ID)
        {
          // Thumbnail PDFs (may add other formats in future)
          if ($this->canThumbnail())
          {
            $this->createReferenceImage($connection);
            $this->createThumbnail($connection);
          }

          // Extract text
          $this->extractText($connection);
        }

        break;

      case QubitTerm::VIDEO_ID:
        if ($usageId == QubitTerm::EXTERNAL_URI_ID || $usageId == QubitTerm::MASTER_ID)
        {
          $this->createVideoDerivative(QubitTerm::REFERENCE_ID, $connection);
          $this->createVideoDerivative(QubitTerm::THUMBNAIL_ID, $connection);
        }

        break;

      case QubitTerm::AUDIO_ID:
        if ($usageId == QubitTerm::EXTERNAL_URI_ID || $usageId == QubitTerm::MASTER_ID)
        {
          $this->createAudioDerivative(QubitTerm::REFERENCE_ID, $connection);
        }

        break;
    }

    return $this;
  }

  /**
   * Set 'page_count' property for this asset
   *
   * NOTE: requires the ImageMagick library
   *
   * @return QubitDigitalObject this object
   */
  public function setPageCount()
  {
    if ($this->canThumbnail() && self::hasImageMagick())
    {
      if (QubitTerm::EXTERNAL_URI_ID == $this->usageId)
      {
        $command = 'identify '.$this->localPath;
      }
      else
      {
        $command = 'identify '.$this->getAbsolutePath();
      }

      exec($command, $output, $status);

      if ($status == 0)
      {
        // Add "number of pages" property
        $pageCount = new QubitProperty;
        $pageCount->setObjectId($this->id);
        $pageCount->setName('page_count');
        $pageCount->setScope('digital_object');
        $pageCount->setValue(count($output), array('sourceCulture' => true));
        $pageCount->save($connection);
      }
    }

    return $this;
  }

  /**
   * Get the number of pages in asset (multi-page file)
   *
   * @return integer number of pages
   */
  public function getPageCount()
  {
    if (null === $pageCount = QubitProperty::getOneByObjectIdAndName($this->id, 'page_count'))
    {
      $this->setPageCount();
      $pageCount = QubitProperty::getOneByObjectIdAndName($this->id, 'page_count');
    }

    if ($pageCount)
    {
      return (integer) $pageCount->getValue();
    }
  }

  public function getPage($index)
  {
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::PARENT_ID, $this->informationObject->id);
    $criteria->addJoin(QubitInformationObject::ID, QubitDigitalObject::INFORMATION_OBJECT_ID);
    $criteria->setLimit(1);
    $criteria->setOffset($index);

    return QubitDigitalObject::getOne($criteria);
  }

  /**
   * Explode multi-page asset into multiple image files
   *
   * @return unknown
   */
  public function explodeMultiPageAsset()
  {
    $pageCount = $this->getPageCount();

    if ($pageCount > 1 && $this->canThumbnail())
    {
      if (QubitTerm::EXTERNAL_URI_ID == $this->usageId)
      {
        $path = $this->localPath;
      }
      else
      {
        $path = $this->getAbsolutePath();
      }

      $filenameMinusExtension = preg_replace('/\.[a-zA-Z]{2,3}$/', '', $path);

      $command = 'convert -quality 100 ';
      $command .= $path;
      $command .= ' '.$filenameMinusExtension.'_%02d.'.self::THUMB_EXTENSION;
      exec($command, $output, $status);

      if ($status == 1)
      {
        throw new sfException('Encountered error'.(is_array($output) && count($output) > 0 ? ': '.implode('\n'.$output) : ' ').' while running convert (ImageMagick).');
      }

      // Build an array of the exploded file names
      for ($i = 0; $i < $pageCount; $i++)
      {
        $fileList[] = $filenameMinusExtension.sprintf('_%02d.', $i).self::THUMB_EXTENSION;
      }
    }

    return $fileList;
  }

  /**
   * Create an info and digital object tree for multi-page assets
   *
   * For digital objects that describe a multi-page digital asset (e.g. a
   * multi-page tif image), create a derived asset for each page, create a child
   * information object and linked child digital object and move the derived
   * asset to the appropriate directory for the new (child) info object
   *
   * NOTE: Requires the Imagemagick library for creating derivative assets
   *
   * @return QubitDigitalObject this object
   */
  public function createCompoundChildren($connection = null)
  {
    // Bail out if the imagemagick library is not installed
    if (false === self::hasImageMagick())
    {
      return $this;
    }

    $pages = $this->explodeMultiPageAsset();

    foreach ($pages as $i => $filepath)
    {
      // Create a new information object
      $newInfoObject = new QubitInformationObject;
      $newInfoObject->parentId = $this->getInformationObject()->id;
      $newInfoObject->setTitle($this->getInformationObject()->getTitle().' ('.($i + 1).')');
      $newInfoObject->setPublicationStatus(sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID));
      $newInfoObject->save($connection);

      // Create and link a new digital object
      $newDigiObject = new QubitDigitalObject;
      $newDigiObject->parentId = $this->id;
      $newDigiObject->setInformationObjectId($newInfoObject->id);
      $newDigiObject->save($connnection);

      // Derive new file path based on newInfoObject
      $assetPath = $newDigiObject->getAssetPath();
      $createPath = '';
      foreach (explode('/', $assetPath) as $d)
      {
        $createPath .= '/'.$d;
        if (!is_dir(sfConfig::get('sf_web_dir').$createPath))
        {
          mkdir(sfConfig::get('sf_web_dir').$createPath, 0755);
        }
        chmod(sfConfig::get('sf_web_dir').$createPath, 0755);
      }

      // Derive new name for file based on original file name + newDigitalObject
      // id
      $filename = basename($filepath);
      $newFilepath = sfConfig::get('sf_web_dir').$assetPath.'/'.$filename;

      // Move asset to new name and path
      rename($filepath, $newFilepath);
      chmod($newFilepath, 0644);

      // Save new file information
      $newDigiObject->setPath("$assetPath/");
      $newDigiObject->setName($filename);
      $newDigiObject->setByteSize(filesize($newFilepath));
      $newDigiObject->usageId = QubitTerm::MASTER_ID;
      $newDigiObject->setMimeType(QubitDigitalObject::deriveMimeType($filename));
      $newDigiObject->mediaTypeId = $this->mediaTypeId;
      $newDigiObject->setPageCount();
      $newDigiObject->setSequence($i + 1);
      $newDigiObject->save($connnection);

      // And finally create reference and thumb images for child asssets
      $newDigiObject->createRepresentations($newDigiObject->getUsageId(), $connection);
    }

    return $this;
  }

  /**
   * Test various php settings that affect file upload size and report the
   * most limiting one.
   *
   * @return integer max upload file size in bytes
   */
  public static function getMaxUploadSize()
  {
    $settings = array();
    $settings[] = self::returnBytes(ini_get('post_max_size'));
    $settings[] = self::returnBytes(ini_get('upload_max_filesize'));
    $settings[] = self::returnBytes(ini_get('memory_limit'));

    foreach ($settings as $index => $value)
    {
      if ($value == 0)
      {
        unset($settings[$index]);
      }
    }

    if (0 == count($settings))
    {
      // Unlimited
      return -1;
    }
    else
    {
      return min($settings);
    }
  }

  /**
   * Transform the php.ini notation for numbers (like '2M') to number of bytes
   *
   * Taken from http://ca2.php.net/manual/en/function.ini-get.php
   *
   * @param string $value A string denoting byte size by multiple (e.g. 2M)
   * @return integer size in bytes
   */
  protected static function returnBytes($val)
  {
    $val = trim($val);
    $last = strtolower(substr($val, -1));
    switch($last) {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
        $val *= 1024;
      case 'm':
        $val *= 1024;
      case 'k':
        $val *= 1024;
    }

    return $val;
  }

  /*
   * -----------------------------------------------------------------------
   * IMAGE MANIPULATION METHODS
   * -----------------------------------------------------------------------
   */

  /**
   * Create a thumbnail derivative for the current digital object
   *
   * @return QubitDigitalObject
   */
  public function createThumbnail($connection = null)
  {
    // Create a thumbnail
    $derivative = $this->createImageDerivative(QubitTerm::THUMBNAIL_ID, $connection);

    return $derivative;
  }

  /**
   * Create a reference derivative for the current digital object
   *
   * @return QubitDigitalObject  The new derived reference digital object
   */
  public function createReferenceImage($connection = null)
  {
    // Create derivative
    $derivative = $this->createImageDerivative(QubitTerm::REFERENCE_ID, $connection);

    return $derivative;
  }

  /**
   * Create an derivative of an image (a smaller image ;)
   *
   * @param integer  $usageId  usage type id
   * @return QubitDigitalObject derivative object
   */
  public function createImageDerivative($usageId, $connection = null)
  {
    // Get max dimensions
    $maxDimensions = self::getImageMaxDimensions($usageId);

    // Build new filename and path
    if (QubitTerm::EXTERNAL_URI_ID == $this->usageId)
    {
      $originalFullPath = $this->localPath;
    }
    else
    {
      $originalFullPath = $this->getAbsolutePath();
    }

    $extension = '.'.self::THUMB_EXTENSION;
    list($originalNameNoExtension) = explode('.', $this->getName());
    $derivativeName = $originalNameNoExtension.'_'.$usageId.$extension;

    // Resize
    $resizedImage = QubitDigitalObject::resizeImage($originalFullPath, $maxDimensions[0], $maxDimensions[1]);

    if (0 < strlen($resizedImage))
    {
      $derivative = new QubitDigitalObject;
      $derivative->parentId = $this->id;
      $derivative->usageId = $usageId;
      $derivative->createDerivatives = false;
      $derivative->indexOnSave = false;
      $derivative->assets[] = new QubitAsset($derivativeName, $resizedImage);
      $derivative->save($connection);

      return $derivative;
    }
  }

  /**
   * Resize this digital object (image)
   *
   * @param integer $maxwidth  Max width of resized image
   * @param integer $maxheight Max height of resized image
   *
   * @return boolean success or failure
   */
  public function resize($maxwidth, $maxheight=null)
  {
    // Only operate on digital objects that are images
    if ($this->isImage())
    {
      $filename = $this->getAbsolutePath();
      return QubitDigitalObject::resizeImage($filename, $maxwidth, $maxheight);
    }

    return false;
  }

  /**
   * Resize current digital object according to a specific usage type
   *
   * @param integer $usageId
   * @return boolean success or failure
   */
  public function resizeByUsageId($usageId)
  {
    if ($usageId == QubitTerm::REFERENCE_ID)
    {
      $maxwidth = (sfConfig::get('app_reference_image_maxwidth')) ? sfConfig::get('app_reference_image_maxwidth') : 480;
      $maxheight = null;
    }
    else if ($usageId == QubitTerm::THUMBNAIL_ID)
    {
      $maxwidth = 100;
      $maxheight = 100;
    }
    else
    {
      return false;
    }

    return $this->resize($maxwidth, $maxheight);
  }

  /**
   * Allow multiple ways of getting the max dimensions for image by usage
   *
   * @param integer $usageId  the usage type
   * @return array $maxwidth, $maxheight
   *
   * @todo Add THUMBNAIL_MAX_DIMENSION to Qubit Settings
   */
  public static function getImageMaxDimensions($usageId)
  {
    $maxwidth = $maxheight = null;

    switch ($usageId)
    {
      case QubitTerm::REFERENCE_ID:
        // Backwards compatiblity - if maxwidth Qubit setting doesn't exist
        if (!$maxwidth = sfConfig::get('app_reference_image_maxwidth'))
        {
          $maxwidth = 480;
        }
        $maxheight = $maxwidth;
        break;
      case QubitTerm::THUMBNAIL_ID:
        $maxwidth = 270;
        $maxheight = 1024;
        break;
      case QubitTerm::COMPOUND_ID:
        if (!$maxwidth = sfConfig::get('app_reference_image_maxwidth'))
        {
          $maxwidth = 480;
        }
        $maxheight = $maxwidth; // Full maxwidth dimensions (480 default)
        $maxwidth = floor($maxwidth / 2) - 10; // 1/2 size - gutter (230 default)
        break;
    }

    return array($maxwidth, $maxheight);
  }

  /**
   * Resize an image using the sfThubmnail Plugin.
   *
   * @param string $originalImageName
   * @param integer $width
   * @param integer $height
   *
   * @return string (thumbnail's bitstream)
   */
  public static function resizeImage($originalImageName, $width=null, $height=null)
  {
    $mimeType = QubitDigitalObject::deriveMimeType($originalImageName);

    // Get thumbnail adapter
    if (!$adapter = self::getThumbnailAdapter())
    {
      return false;
    }

    // Check that this file can be thumbnailed, or return false
    if (self::canThumbnailMimeType($mimeType) == false)
    {
      return false;
    }

    // Create a thumbnail
    try
    {
      $newImage = new sfThumbnail($width, $height, true, false, 75, $adapter, array('extract' => 1));
      $newImage->loadFile($originalImageName);
    }
    catch (Exception $e)
    {
      return false;
    }

    return $newImage->toString('image/jpeg');
  }

  /**
   * Get a valid adapter for the sfThumbnail library (either GD or ImageMagick)
   * Cache the adapter value because is very expensive to calculate it
   *
   * @return mixed  name of adapter on success, false on failure
   */
  public static function getThumbnailAdapter()
  {
    $adapter = false;

    $context = sfContext::getInstance();
    if ($context->has('thumbnailAdapter'))
    {
      return $context->get('thumbnailAdapter');
    }

    if (QubitDigitalObject::hasImageMagick())
    {
      $adapter = 'sfImageMagickAdapter';
    }
    else if (QubitDigitalObject::hasGdExtension())
    {
      $adapter = 'sfGDAdapter';
    }

    $context->set('thumbnailAdapter', $adapter);

    return $adapter;
  }

  /**
   * Test if ImageMagick library is installed
   *
   * @return boolean  true if ImageMagick is found
   */
  public static function hasImageMagick()
  {
    $command = 'convert -version';
    exec($command, $output, $status);

    return 0 < count($output) && false !== strpos($output[0], 'ImageMagick');
  }

  /**
   * Test if GD Extension for PHP is installed
   *
   * @return boolean true if GD extension found
   */
  public static function hasGdExtension()
  {
    return extension_loaded('gd');
  }

  /**
   * Wrapper for canThumbnailMimeType() for use on instantiated objects
   *
   * @return boolean
   * @see canThumbnailMimeType
   */
  public function canThumbnail()
  {
    return self::canThumbnailMimeType($this->mimeType);
  }

  /**
   * Test if current digital object can be thumbnailed
   *
   * @param string    The current thumbnailing adapter
   * @return boolean  true if thumbnail is possible
   */
  public static function canThumbnailMimeType($mimeType)
  {
    if (!$adapter = self::getThumbnailAdapter())
    {
      return false;
    }

    $canThumbnail = false;

    // For Images, we can create thumbs with either GD or ImageMagick
    if (substr($mimeType, 0, 5) == 'image' && strlen($adapter))
    {
      $canThumbnail = true;
    }

    // For PDFs we can only create thumbs with ImageMagick
    else if ($mimeType == 'application/pdf' && $adapter == 'sfImageMagickAdapter')
    {
      $canThumbnail = true;
    }

    return $canThumbnail;
  }

  /**
   * Return true if derived mimeType is "image/*"
   *
   * @param string $filename
   * @return boolean
   */
  public static function isImageFile($filename)
  {
    $mimeType = self::deriveMimeType($filename);
    if (strtolower(substr($mimeType, 0, 5)) == 'image')
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  public static function isAudioFile($filename)
  {
    $mimeType = self::deriveMimeType($filename);
    if (strtolower(substr($mimeType, 0, 5)) == 'audio')
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /*
   * -----------------------------------------------------------------------
   * VIDEO
   * -----------------------------------------------------------------------
   */
  public function createAudioDerivative($usageId, $connection = null)
  {
    if (QubitTerm::REFERENCE_ID != $usageId)
    {
      return false;
    }

    if (QubitTerm::EXTERNAL_URI_ID == $this->usageId)
    {
      $originalFullPath = $this->localPath;

      list($originalNameNoExtension) = explode('.', $this->getName());
      $derivativeName = $originalNameNoExtension.'_'.$usageId.'.mp3';

      $pathParts = pathinfo($this->localPath);

      $derivativeFullPath = $pathParts['dirname'].'/'.$derivativeName;

      self::convertAudioToMp3($originalFullPath, $derivativeFullPath);

      if (file_exists($derivativeFullPath) && 0 < ($byteSize = filesize($derivativeFullPath)))
      {
        $derivative = new QubitDigitalObject;
        $derivative->parentId = $this->id;
        $derivative->usageId = $usageId;
        $derivative->assets[] = new QubitAsset($derivativeName, file_get_contents($derivativeFullPath));
        $derivative->createDerivatives = false;
        $derivative->indexOnSave = false;
        $derivative->save($connection);
      }
    }
    else
    {
      $originalFullPath = $this->getAbsolutePath();

      list($originalNameNoExtension) = explode('.', $this->getName());
      $derivativeName = $originalNameNoExtension.'_'.$usageId.'.mp3';

      $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;

      self::convertAudioToMp3($originalFullPath, $derivativeFullPath);

      if (file_exists($derivativeFullPath) && 0 < ($byteSize = filesize($derivativeFullPath)))
      {
        $derivative = new QubitDigitalObject;
        $derivative->setPath($this->getPath());
        $derivative->setName($derivativeName);
        $derivative->parentId = $this->id;
        $derivative->setByteSize($byteSize);
        $derivative->usageId = $usageId;
        $derivative->setMimeAndMediaType();
        $derivative->createDerivatives = false;
        $derivative->indexOnSave = false;
        $derivative->save($connection);
      }
    }
  }

  public static function convertAudioToMp3($originalPath, $newPath)
  {
    // Test for FFmpeg library
    if (!self::hasFfmpeg())
    {
      return false;
    }

    $command = 'ffmpeg -y -i '.$originalPath.' '.$newPath.' 2>&1';
    exec($command, $output, $status);

    if ($status)
    {
      $error = true;

      for ($i = count($output) - 1; $i >= 0; $i--)
      {
        if (strpos($output[$i], 'output buffer too small'))
        {
          $error = false;

          break;
        }
      }
    }

    chmod($newPath, 0644);

    return true;
  }

  /*
   * -----------------------------------------------------------------------
   * VIDEO
   * -----------------------------------------------------------------------
   */

  /**
   * Create video derivatives (either flv movie or thumbnail)
   *
   * @param integer  $usageId  usage type id
   * @return QubitDigitalObject derivative object
   */
  public function createVideoDerivative($usageId, $connection = null)
  {
    // Build new filename and path
    $originalFullPath = $this->getAbsolutePath();
    list($originalNameNoExtension) = explode('.', $this->getName());

    switch ($usageId)
    {
      case QubitTerm::REFERENCE_ID:
        $derivativeName = $originalNameNoExtension.'_'.$usageId.'.flv';
        $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;
        self::convertVideoToFlash($originalFullPath, $derivativeFullPath);
        break;
      case QubitTerm::THUMBNAIL_ID:
      default:
        $extension = '.'.self::THUMB_EXTENSION;
        $derivativeName = $originalNameNoExtension.'_'.$usageId.$extension;
        $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;
        $maxDimensions = self::getImageMaxDimensions($usageId);
        self::convertVideoToThumbnail($originalFullPath, $derivativeFullPath, $maxDimensions[0], $maxDimensions[1]);
    }

    if (file_exists($derivativeFullPath) && 0 < ($byteSize = filesize($derivativeFullPath)))
    {
      $derivative = new QubitDigitalObject;
      $derivative->setPath($this->getPath());
      $derivative->setName($derivativeName);
      $derivative->parentId = $this->id;
      $derivative->setByteSize($byteSize);
      $derivative->usageId = $usageId;
      $derivative->setMimeAndMediaType();
      $derivative->createDerivatives = false;
      $derivative->indexOnSave = false;
      $derivative->save($connection);

      return $derivative;
    }
    $originalFullPath = $this->getAbsolutePath();
    list($originalNameNoExtension) = explode('.', $this->getName());

    switch ($usageId)
    {
      case QubitTerm::REFERENCE_ID:
        $derivativeName = $originalNameNoExtension.'_'.$usageId.'.flv';
        $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;
        self::convertVideoToFlash($originalFullPath, $derivativeFullPath);
        break;
      case QubitTerm::THUMBNAIL_ID:
      default:
        $extension = '.'.self::THUMB_EXTENSION;
        $derivativeName = $originalNameNoExtension.'_'.$usageId.$extension;
        $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;
        $maxDimensions = self::getImageMaxDimensions($usageId);
        self::convertVideoToThumbnail($originalFullPath, $derivativeFullPath, $maxDimensions[0], $maxDimensions[1]);
    }

    if (file_exists($derivativeFullPath) && 0 < ($byteSize = filesize($derivativeFullPath)))
    {
      $derivative = new QubitDigitalObject;
      $derivative->setPath($this->getPath());
      $derivative->setName($derivativeName);
      $derivative->parentId = $this->id;
      $derivative->setByteSize($byteSize);
      $derivative->usageId = $usageId;
      $derivative->setMimeAndMediaType();
      $derivative->createDerivatives = false;
      $derivative->indexOnSave = false;
      $derivative->save($connection);

      return $derivative;
    }
  }

  /**
   * Test if FFmpeg library is installed
   *
   * @return boolean  true if FFmpeg is found
   */
  public static function hasFfmpeg()
  {
    $command = 'ffmpeg -version 2>&1';
    exec($command, $output, $status);

    return 0 < count($output) && false !== strpos(strtolower($output[0]), 'ffmpeg');
  }

  /**
   * Create a flash video derivative using the FFmpeg library.
   *
   * @param string  $originalPath path to original video
   * @param string  $newPath      path to derivative video
   * @param integer $maxwidth     derivative video maximum width
   * @param integer $maxheight    derivative video maximum height
   *
   * @return boolean  success or failure
   *
   * @todo implement $maxwidth and $maxheight constraints on video
   */
  public static function convertVideoToFlash($originalPath, $newPath, $width=null, $height=null)
  {
    // Test for FFmpeg library
    if (!self::hasFfmpeg())
    {
      return false;
    }

    $command = 'ffmpeg -y -i '.$originalPath.' -ar 44100 '.$newPath.' 2>&1';
    exec($command, $output, $status);

    chmod($newPath, 0644);

    return true;
  }

  /**
   * Create a flash video derivative using the FFmpeg library.
   *
   * @param string  $originalPath path to original video
   * @param string  $newPath      path to derivative video
   * @param integer $maxwidth     derivative video maximum width
   * @param integer $maxheight    derivative video maximum height
   *
   * @return boolean  success or failure
   *
   * @todo implement $maxwidth and $maxheight constraints on video
   */
  public static function convertVideoToThumbnail($originalPath, $newPath, $width = null, $height = null)
  {
    // Test for FFmpeg library
    if (!self::hasFfmpeg())
    {
      return false;
    }

    // Do conversion to jpeg
    $command = 'ffmpeg -itsoffset -30 -i '.$originalPath.' -vframes 1 -an -f image2 -s '.$width.'x'.$height.' '.$newPath;
    exec($command.' 2>&1', $output, $status);

    chmod($newPath, 0644);

    return true;
  }

  /**
   * Return true if derived mimeType is "video/*"
   *
   * @param string $filename
   * @return boolean
   */
  public static function isVideoFile($filename)
  {
    $mimeType = self::deriveMimeType($filename);
    if (strtolower(substr($mimeType, 0, 5)) == 'video')
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Create a thumbnail from a video file using FFmpeg library
   *
   * @param string $originalImageName
   * @param integer $width
   * @param integer $height
   *
   * @return string (thumbnail's bitstream)
   */
  public static function createThumbnailFromVideo($originalPath, $width=null, $height=null)
  {
    // Test for FFmpeg library
    if (!self::hasFfmpeg())
    {
      return false;
    }

    $tmpDir = sfConfig::get('sf_upload_dir').'/tmp';
    if (!file_exists($tmpDir))
    {
      mkdir($tmpDir);
      chmod($tmpDir, 0775);
    }

    // Get a unique file name (to avoid clashing file names)
    $tmpFileName = null;
    $tmpFilePath = null;
    while (file_exists($tmpFilePath) || null === $tmpFileName)
    {
      $uniqueString = substr(md5(time().$tmpFileName), 0, 8);
      $tmpFileName = 'TMP'.$uniqueString;
      $tmpFilePath = $tmpDir.'/'.$tmpFileName.'.jpg';
    }

    // Do conversion to jpeg
    $command = 'ffmpeg -i '.$originalPath.' -vframes 1 -an -f image2 -s '.$width.'x'.$height.' '.$tmpFilePath.' 2>&1';
    exec($command, $output, $status);

    chmod($tmpFilePath, 0644);

    return file_get_contents($tmpFilePath);
  }


  /*
   * -----------------------------------------------------------------------
   * TEXT METHODS
   * -----------------------------------------------------------------------
   */

  public static function hasPdfToText()
  {
    exec('which pdftotext', $output, $status);

    return 0 == $status && 0 < count($output);
  }

  /**
   * Test if text extraction is possible
   *
   * @param string mime-type
   * @return boolean true if extraction is supported
   */
  public static function canExtractText($mimeType)
  {
    // Only works for PDFs
    if ('application/pdf' != $mimeType)
    {
      return false;
    }

    // Requires pdftotext binary
    if (!self::hasPdfToText())
    {
      return false;
    }

    return true;
  }

  /**
   * Create a thumbnail derivative for the current digital object
   *
   * @return QubitDigitalObject
   */
  public function extractText($connection = null)
  {
    if (!self::canExtractText($this->mimeType))
    {
      return;
    }

    $command = sprintf('pdftotext %s - 2> /dev/null', $this->getAbsolutePath());
    exec($command, $output, $status);

    if (0 == $status && 0 < count($output))
    {
      $text = implode(PHP_EOL, $output);

      $property = new QubitProperty;
      $property->objectId = $this->id;
      $property->name = 'transcript';
      $property->scope = 'Text extracted from source PDF file\'s text layer using pdftotext';
      $property->value = $text;
      $property->indexOnSave = false;

      $property->save($connection);

      return $text;
    }
  }

  /* -----------------------------------------------------------------------
   * CHECKSUMS
   * --------------------------------------------------------------------- */

  /**
   * Set a checksum value for this digital object
   *
   * @param string $value   the checksum string
   * @param array  $options optional parameters
   *
   * @return QubitDigitalObject this object
   */
  public function setChecksum($value, $options)
  {
    if (isset($options['checksumType']))
    {
      $this->setChecksumType($options['checksumType']);
    }

    $this->checksum = $value;

    return $this;
  }

  /**
   * Generate a checksum from the file specified
   *
   * @param string $filename name of file
   * @return string checksum
   */
  public function generateChecksumFromFile($filename)
  {
    if (!isset($this->checksumType))
    {
      $this->checksumType = 'sha256';
    }

    if (!in_array($this->checksumType, hash_algos()))
    {
      throw new Exception('Invalid checksum this->checksumType "'.$this->checksumType.'"');
    }

    $this->checksum = hash_file($this->checksumType, $filename);

    return $this;
  }

  /* -----------------------------------------------------------------------
   * Display as compound object
   * --------------------------------------------------------------------- */

  /**
   * Setter for "displayAsCompound" property
   *
   * @param string $value new value for property
   * @return QubitInformationObject this object
   */
  public function setDisplayAsCompoundObject($value)
  {
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $this->id);
    $criteria->add(QubitProperty::NAME, 'displayAsCompound');

    $displayAsCompoundProp = QubitProperty::getOne($criteria);
    if (is_null($displayAsCompoundProp))
    {
      $displayAsCompoundProp = new QubitProperty;
      $displayAsCompoundProp->setObjectId($this->id);
      $displayAsCompoundProp->setName('displayAsCompound');
    }

    $displayAsCompoundProp->setValue($value, array('sourceCulture' => true));
    $displayAsCompoundProp->save();

    return $this;
  }

  /**
   * Getter for related "displayAsCompound" property
   *
   * @return string property value
   */
  public function getDisplayAsCompoundObject()
  {
    $displayAsCompoundProp = QubitProperty::getOneByObjectIdAndName($this->id, 'displayAsCompound');
    if (null !== $displayAsCompoundProp)
    {
      return $displayAsCompoundProp->getValue(array('sourceCulture' => true));
    }
  }

  /**
   * Decide whether to show child digital objects as a compound object based
   * on 'displayAsCompound' toggle and available digital objects.
   *
   * @return boolean
   */
  public function showAsCompoundDigitalObject()
  {
    // Return false if this digital object is not linked directly to an
    // information object
    if (null === $this->informationObjectId)
    {
      return false;
    }

    // Return false if "show compound" toggle is not set to '1' (yes)
    $showCompoundProp = QubitProperty::getOneByObjectIdAndName($this->id, 'displayAsCompound');
    if (null === $showCompoundProp || '1' != $showCompoundProp->getValue(array('sourceCulture' => true)) )
    {
      return false;
    }

    // Return false if this object has no children with digital objects
    $criteria = new Criteria;
    $criteria->addJoin(QubitInformationObject::ID, QubitDigitalObject::INFORMATION_OBJECT_ID);
    $criteria->add(QubitInformationObject::PARENT_ID, $this->informationObjectId);

    if (0 === count(QubitDigitalObject::get($criteria)))
    {
      return false;
    }

    return true;
  }

  /**
   * Recursively remove empty directories
   *
   * @param string $dir directory name
   *
   * @return void
   */
  public static function pruneEmptyDirs($dir)
  {
    // Remove any extra whitespace or trailing slash
    $dir = rtrim(trim($dir), '/');

    do
    {
      if (sfConfig::get('sf_upload_dir') == $dir || sfConfig::get('sf_upload_dir').'/r' == $dir)
      {
        return; // Protect uploads/ and uploads/r/
      }

      if (self::isEmptyDir($dir))
      {
        rmdir($dir);
      }
      else
      {
        return;
      }
    } while (strrpos($dir, '/') && $dir = substr($dir, 0, strrpos($dir, '/')));
  }

  /**
   * Check if directory is empty
   *
   * @param string $dir directory name
   *
   * @return boolean true if empty
   */
  public static function isEmptyDir($dir)
  {
    if (is_dir($dir))
    {
      $files = scandir($dir);

      return (2 >= count($files)); // Always have "." and ".." dirs
    }
  }
}
