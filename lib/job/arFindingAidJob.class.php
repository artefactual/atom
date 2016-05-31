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
 * @package    AccesstoMemory
 * @author     Mike G <mikeg@artefactual.com>
 */

class arFindingAidJob extends arBaseJob
{
  const GENERATED_STATUS = 1,
        UPLOADED_STATUS  = 2;

  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('objectId');

  private $resource = null;

  public function runJob($parameters)
  {
    $this->resource = QubitInformationObject::getById($parameters['objectId']);

    // Check that object exists and that it is not the root
    if (!isset($this->resource) || !isset($this->resource->parent))
    {
      $this->error($this->i18n->__('Error: Could not find an information object with id: %1', array('%1' => $parameters['objectId'])));

      return false;
    }

    $this->checkDownloadsExistsAndCreate();

    if (isset($parameters['delete']) && $parameters['delete'])
    {
      $result = $this->delete();
    }
    else if (isset($parameters['uploadPath']))
    {
      $result = $this->upload($parameters['uploadPath']);
    }
    else
    {
      $result = $this->generate();
    }

    if (!$result)
    {
      return false;
    }

    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }

  private function generate()
  {
    $this->info($this->i18n->__('Generating finding aid (%1)...', array('%1' => $this->resource->slug)));

    $appRoot = rtrim(sfConfig::get('sf_root_dir'), '/');

    // Determine language(s) used in the export
    $exportLanguage = sfContext::getInstance()->user->getCulture();
    $sourceLanguage = $this->resource->getSourceCulture();

    $eadFileHandle = tmpfile();
    $foFileHandle = tmpfile();

    if (!$eadFileHandle || !$foFileHandle)
    {
      $this->error($this->i18n->__('Failed to create temporary file.'));

      return false;
    }

    $eadFilePath = $this->getTmpFilePath($eadFileHandle);
    $foFilePath = $this->getTmpFilePath($foFileHandle);

    unlink($eadFilePath);

    $public = '';
    if ((null !== $setting = QubitSetting::getByName('publicFindingAid'))
      && $setting->getValue(array('sourceCulture' => true)))
    {
      $public = '--public';
    }

    // Call generate EAD task
    $slug = $this->resource->slug;
    $output = array();
    exec("php $appRoot/symfony export:bulk --single-slug=$slug $public $eadFilePath 2>&1", $output, $exitCode);

    if ($exitCode > 0)
    {
      $this->error($this->i18n->__('Exporting EAD has failed.'));
      $this->logCmdOutput($output, 'ERROR(EAD-EXPORT)');

      return false;
    }

    // Use XSL file selected in Finding Aid model setting
    $findingAidModel = 'inventory-summary';
    if (null !== $setting = QubitSetting::getByName('findingAidModel'))
    {
      $findingAidModel = $setting->getValue(array('sourceCulture' => true));
    }

    $eadXslFilePath = $appRoot . '/lib/task/pdf/ead-pdf-' . $findingAidModel . '.xsl';
    $saxonPath = $appRoot . '/lib/task/pdf/saxon9he.jar';

    // Crank the XML through XSL stylesheet and fix header / fonds URL
    $eadFileString = file_get_contents($eadFilePath);
    $eadFileString = $this->fixHeader($eadFileString, sfConfig::get('app_site_base_url', null));
    file_put_contents($eadFilePath, $eadFileString);

    // Transform EAD file with Saxon
    $pdfPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . self::getFindingAidPath($this->resource->id);
    $cmd = sprintf("java -jar '%s' -s:'%s' -xsl:'%s' -o:'%s' 2>&1", $saxonPath, $eadFilePath, $eadXslFilePath, $foFilePath);
    $this->info(sprintf('Running: %s', $cmd));
    $output = array();
    exec($cmd, $output, $exitCode);

    if ($exitCode > 0)
    {
      $this->error($this->i18n->__('Transforming the EAD with Saxon has failed.'));
      $this->logCmdOutput($output, 'ERROR(SAXON)');

      return false;
    }

    // Use FOP generated in previous step to generate PDF
    $cmd = sprintf("fop -r -q -fo '%s' -%s '%s' 2>&1", $foFilePath, self::getFindingAidFormat(), $pdfPath);
    $this->info(sprintf('Running: %s', $cmd));
    $output = array();
    exec($cmd, $output, $exitCode);

    if ($exitCode != 0)
    {
      $this->error($this->i18n->__('Converting the EAD FO to PDF has failed.'));
      $this->logCmdOutput($output, 'ERROR(FOP)');

      return false;
    }

    // Update or create 'findingAidStatus' property
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
    $criteria->add(QubitProperty::NAME, 'findingAidStatus');

    if (null === $property = QubitProperty::getOne($criteria))
    {
      $property = new QubitProperty;
      $property->objectId = $this->resource->id;
      $property->name = 'findingAidStatus';
    }

    $property->setValue(self::GENERATED_STATUS, array('sourceCulture' => true));
    $property->indexOnSave = false;
    $property->save();

    // Update ES document with finding aid status
    $partialData = array(
      'findingAid' => array(
        'status' => self::GENERATED_STATUS
    ));

    QubitSearch::getInstance()->partialUpdate($this->resource, $partialData);

    $this->info($this->i18n->__('Finding aid generated successfully: %1', array('%1' => $pdfPath)));

    fclose($eadFileHandle); // Will delete the tmp file
    fclose($foFileHandle);

    return true;
  }

  private function upload($path)
  {
    $this->info($this->i18n->__('Uploading finding aid (%1)...', array('%1' => $this->resource->slug)));

    // Update or create 'findingAidStatus' property
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
    $criteria->add(QubitProperty::NAME, 'findingAidStatus');

    if (null === $property = QubitProperty::getOne($criteria))
    {
      $property = new QubitProperty;
      $property->objectId = $this->resource->id;
      $property->name = 'findingAidStatus';
    }

    $property->setValue(self::UPLOADED_STATUS, array('sourceCulture' => true));
    $property->indexOnSave = false;
    $property->save();

    $partialData = array(
      'findingAid' => array(
        'transcript' => null,
        'status' => self::UPLOADED_STATUS
    ));

    $this->info($this->i18n->__('Finding aid uploaded successfully: %1', array('%1' => $path)));

    // Extract finding aid transcript
    $mimeType = 'application/' . self::getFindingAidFormat();

    if (!QubitDigitalObject::canExtractText($mimeType))
    {
      $message = $this->i18n->__('Could not obtain finding aid text.');
      $this->job->addNoteText($message);
      $this->info($message);
    }
    else
    {
      $this->info($this->i18n->__('Obtaining finding aid text...'));

      $command = sprintf('pdftotext %s - 2> /dev/null', $path);
      exec($command, $output, $status);

      if ($status != 0)
      {
        $message = $this->i18n->__('Obtaining the text has failed.');
        $this->job->addNoteText($message);
        $this->info($message);
        $this->logCmdOutput($output, 'WARNING(PDFTOTEXT)');
      }
      else if (0 < count($output))
      {
        $text = implode(PHP_EOL, $output);

        // Update or create 'findingAidTranscript' property
        $criteria = new Criteria;
        $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitProperty::NAME, 'findingAidTranscript');
        $criteria->add(QubitProperty::SCOPE, 'Text extracted from finding aid PDF file text layer using pdftotext');

        if (null === $property = QubitProperty::getOne($criteria))
        {
          $property = new QubitProperty;
          $property->objectId = $this->resource->id;
          $property->name = 'findingAidTranscript';
          $property->scope = 'Text extracted from finding aid PDF file text layer using pdftotext';
        }

        $property->setValue($text, array('sourceCulture' => true));
        $property->indexOnSave = false;
        $property->save();

        // Update partial data with transcript
        $partialData['findingAid']['transcript'] = $text;
      }
    }

    // Update ES document with finding aid status and transcript
    QubitSearch::getInstance()->partialUpdate($this->resource, $partialData);

    return true;
  }

  private function delete()
  {
    $this->info($this->i18n->__('Deleting finding aid (%1)...', array('%1' => $this->resource->slug)));

    foreach (self::getPossibleFilenames($this->resource->id) as $filename)
    {
      $path = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . $filename;

      if (file_exists($path))
      {
        unlink($path);
      }
    }

    // Delete 'findingAidTranscript' property if it exists
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
    $criteria->add(QubitProperty::NAME, 'findingAidTranscript');
    $criteria->add(QubitProperty::SCOPE, 'Text extracted from finding aid PDF file text layer using pdftotext');

    if (null !== $property = QubitProperty::getOne($criteria))
    {
      $this->info($this->i18n->__('Deleting finding aid transcript...'));

      $property->indexOnDelete = false;
      $property->delete();
    }

    // Delete 'findingAidStatus' property if it exists
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
    $criteria->add(QubitProperty::NAME, 'findingAidStatus');

    if (null !== $property = QubitProperty::getOne($criteria))
    {
      $property->indexOnDelete = false;
      $property->delete();
    }

    // Update ES document removing finding aid status and transcript
    $partialData = array(
      'findingAid' => array(
        'transcript' => null,
        'status' => null
    ));

    QubitSearch::getInstance()->partialUpdate($this->resource, $partialData);

    $this->info($this->i18n->__('Finding aid deleted successfully.'));

    return true;
  }

  private function logCmdOutput(array $output, $prefix = null)
  {
    if (empty($prefix))
    {
      $prefix = 'ERROR: ';
    }
    else
    {
      $prefix = $prefix.': ';
    }

    foreach ($output as $line)
    {
      $this->error($prefix.$line);
    }
  }

  private function fixHeader($xmlString, $url = null)
  {
    // Apache FOP requires certain namespaces to be included in the XML in order to process it.
    $xmlString = preg_replace('(<ead .*?>|<ead>)', '<ead xmlns:ns2="http://www.w3.org/1999/xlink" ' .
        'xmlns="urn:isbn:1-931666-22-9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">', $xmlString, 1);

    // TODO: Use new base url functionality in AtoM instead of doing this kludge
    if ($url !== null)
    {
      // Since we call the EAD generation from inside Symfony and not as part as a web request,
      // the url was returning symfony://weirdurlhere. We can get around this by passing the referring url into
      // the job as an option when the user clicks 'generate' and replace the url in the EAD manually.
      $xmlString = preg_replace('/<eadid(.*?)url=\".*?\"(.*?)>/', '<eadid$1url="' . $url . '"$2>', $xmlString, 1);
    }

    return $xmlString;
  }

  private function getTmpFilePath($handle)
  {
    $meta_data = stream_get_meta_data($handle);

    return $meta_data['uri'];
  }

  private function checkDownloadsExistsAndCreate()
  {
    $dlPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'downloads';
    if (!is_dir($dlPath))
    {
      mkdir($dlPath, 0755);
    }
  }

  public static function getStatus($id)
  {
    $sql = '
      SELECT j.status_id as statusId FROM
      job j JOIN object o ON j.id = o.id
      WHERE j.name = ? AND j.object_id = ?
      ORDER BY o.created_at DESC
    ';

    $ret = QubitPdo::fetchOne($sql, array(get_class(), $id));

    return $ret ? (int)$ret->statusId : null;
  }

  public static function getPossibleFilenames($id)
  {
    $filenames = array(
      $id . '.pdf',
      $id . '.rtf'
    );

    if (null !== $slug = QubitSlug::getByObjectId($id))
    {
      $filenames[] = $slug->slug . '.pdf';
      $filenames[] = $slug->slug . '.rtf';
    }

    return $filenames;
  }

  public static function getFindingAidPathForDownload($id)
  {
    foreach (self::getPossibleFilenames($id) as $filename)
    {
      $path = 'downloads' . DIRECTORY_SEPARATOR . $filename;

      if (file_exists(sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $path))
      {
        return $path;
      }
    }

    return null;
  }

  public static function getFindingAidPath($id)
  {
    if (null !== $slug = QubitSlug::getByObjectId($id))
    {
      $filename = $slug->slug;
    }

    if (!isset($filename))
    {
      $filename = $id;
    }

    return 'downloads' . DIRECTORY_SEPARATOR . $filename . '.' . self::getFindingAidFormat();
  }

  public static function getFindingAidFormat()
  {
    if (null !== $setting = QubitSetting::getByName('findingAidFormat'))
    {
      $format = $setting->getValue(array('sourceCulture' => true));
    }

    return isset($format) ? $format : 'pdf';
  }
}
