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
 * Bulk export data to XML
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Gale <mikeg@artefactual.com>
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class eacExportTask extends exportBulkBaseTask
{
  protected $namespace        = 'export';
  protected $name             = 'auth-recs';
  protected $briefDescription = 'Bulk export multiple EAC XML files at once for authority records.';

  /**
   * @see exportBulkBaseTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->checkPathIsWritable($arguments['path']);

    sfContext::createInstance($this->configuration);

    $itemsExported = 0;

    $conn = $this->getDatabaseConnection();

    $sql = "SELECT id FROM actor WHERE entity_type_id IN (" .
            QubitTerm::CORPORATE_BODY_ID . ", " . QubitTerm::PERSON_ID . 
            ", " . QubitTerm::FAMILY_ID . ")";

    $actors = $conn->query($sql, PDO::FETCH_ASSOC);

    foreach ($actors as $row)
    {
      $resource = QubitActor::getById($row['id']);

      $eac = new sfEacPlugin($resource);

      $rawXml = $this->captureResourceExportTemplateOutput($resource, 'eac');
      $xml = $this->tidyXml($rawXml);

      $filename = $this->generateSortableFilename($row['id'], 'eac');
      $filePath = sprintf('%s/%s', $arguments['path'], $filename);

      file_put_contents($filePath, $xml);

      $this->indicateProgress($options['items-until-update']);

      $itemsExported++;
    }

    print "\nExport complete (". $itemsExported ." actors exported).\n";
  }
}
