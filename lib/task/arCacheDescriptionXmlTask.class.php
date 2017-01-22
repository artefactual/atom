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
 * Cycle through all information objects and export their EAD and DC XML
 * representations as files
 *
 * @package     AccesstoMemory
 * @subpackage  cache
 */
class arCacheDescriptionXmlTask extends arBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel')));

    $this->namespace = 'cache';
    $this->name = 'xml-representations';

    $this->briefDescription = 'Render all descriptions as XML and cache the results as files';
    $this->detailedDescription = <<<EOF
Render all descriptions as XML and cache the results as files
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);
    self::exportAll();
  }

  public static function exportAll()
  {
    arXmlExportSingleFileJob::createExportDestinationDirs();
    exportBulkBaseTask::includeXmlExportClassesAndHelpers();

    print "Caching XML representations of information objects...\n";

    foreach(QubitInformationObject::getAll() as $io)
    {
      $published = $io->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID;

      // Only cache if not root and published
      if ($io->id != QubitInformationObject::ROOT_ID && $published)
      {
        // Only cache top-level information object's EAD XML
        if ($io->parentId == QubitInformationObject::ROOT_ID)
        {  
          self::cacheXmlRepresentation($io, 'ead');
          printf("Cached EAD XML for information object %s.\n", $io->id);
        }

        self::cacheXmlRepresentation($io, 'dc');
        printf("Cached DC XML for information object %s.\n", $io->id);
      }
    }

    print "Done.\n";
  }

  public static function cacheXmlRepresentation($resource, $format)
  {
    $tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_'. $format .'_'. $io->id;
    file_put_contents($tempFilePath, self::getXmlRepresentation($resource, $format));
    arXmlExportSingleFileJob::storeExport($tempFilePath, $resource->id, $format);
    unlink($tempFilePath);
  }

  public static function getXmlRepresentation($resource, $format)
  {
    $rawXml = exportBulkBaseTask::captureResourceExportTemplateOutput($resource, $format);
    return Qubit::tidyXml($rawXml);
  }
}
