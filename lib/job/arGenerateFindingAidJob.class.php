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

class arGenerateFindingAidJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('objectId');

  private $resourceId = 0;

  public function runJob($parameters)
  {
    $appRoot = rtrim(sfConfig::get('sf_root_dir'), '/');

    $this->resourceId = $parameters['objectId'];
    $resource = QubitInformationObject::getById($this->resourceId);

    if (!$resource)
    {
      $this->error('Error: Could not find an information object with id=' . $this->resourceId);
      return false;
    }

    $this->checkDownloadsExistsAndCreate();
    $this->info("Generating finding aid ({$resource->slug})...");

    // Determine language(s) used in the export
    $exportLanguage = sfContext::getInstance()->user->getCulture();
    $sourceLanguage = $resource->getSourceCulture();

    $eadFileHandle = tmpfile();
    $foFileHandle = tmpfile();

    if (!$eadFileHandle || !$foFileHandle)
    {
      $this->error('Failed to create temporary file.');
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
    $output = array();
    exec("php $appRoot/symfony export:bulk --single-slug=$resource->slug $public $eadFilePath 2>&1", $output, $exitCode);

    if ($exitCode > 0)
    {
      $this->error('Exporting EAD has failed.');

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

    $pdfPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR .
      self::getFindingAidPath($this->resourceId);

    $cmd = sprintf("java -jar '%s' -s:'%s' -xsl:'%s' -o:'%s' 2>&1", $saxonPath, $eadFilePath, $eadXslFilePath, $foFilePath);
    $this->info(sprintf('Running: %s', $cmd));
    $output = array();
    exec($cmd, $output, $exitCode);

    if ($exitCode > 0)
    {
      $this->error('Transforming the EAD with Saxon has failed.');

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
      $this->error('Converting the EAD FO to PDF has failed.');

      $this->logCmdOutput($output, 'ERROR(FOP)');

      return false;
    }

    $this->info("PDF generated successfully: $pdfPath");

    fclose($eadFileHandle); // Will delete the tmp file
    fclose($foFileHandle);

    $this->job->setStatusCompleted();
    $this->job->save();

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

  public static function getStatusString($id)
  {
    switch (self::getStatus($id))
    {
      case QubitTerm::JOB_STATUS_COMPLETED_ID:
        return 'completed';
      case QubitTerm::JOB_STATUS_IN_PROGRESS_ID:
        return 'generating';
      case QubitTerm::JOB_STATUS_ERROR_ID:
        return 'error';
      default:
        return 'unknown';
    }
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
