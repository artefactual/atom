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
 * Job worker for file-based imports initiated from the WebUI.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arFileImportJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  public function runJob($parameters)
  {
    if (isset($parameters['file']))
    {
      $this->info($this->i18n->__('Importing %1 file: %2.', array('%1' => strtoupper($parameters['importType']), '%2' => $parameters['file']['name'])));
    }
    else
    {
      $this->info($this->i18n->__('Importing %1.', array('%1' => strtoupper($parameters['importType']))));
    }

    // Set indexing preference.
    if (isset($parameters['index']) && false === $parameters['index'])
    {
      QubitSearch::disable();
    }

    try
    {
      switch ($parameters['importType'])
      {
        case 'csv':
          $importer = new QubitCsvImport;

          $this->setCsvImportParams($importer, $parameters);

          $importer->import($parameters['file']['tmp_name'], $parameters['objectType'], $parameters['file']['name']);

          break;

        case 'xml':
          $importer = new QubitXmlImport;

          $options = $this->setXmlImportParams($importer, $parameters);

          $importer->import($parameters['file']['tmp_name'], $options, $parameters['file']['name']);

          break;

        case 'skos':
          $importer = new sfSkosPlugin($parameters['taxonomyId'], array('parentId' => $parameters['parentId'], 'logger' => $this->logger));
          $importer->load($parameters['location']);
          $importer->importGraph();

          break;

        default:
          // 'importType' defaults to 'CSV' by design if extension is blank or something unknown.
          // This was to prevent errors if csv file does not have the correct extension. See
          // modules/object/actions/importAction.class.php.  This default case should never be called.
          $this->error($this->i18n->__('Unable to import selected file: unknown format %1%.', array('%1%' => $parameters['importType'])));
          return false;

          break;
      }
    }
    catch (sfException $e)
    {
      $this->error($e->getMessage());
      return false;
    }

    if ($importer->hasErrors())
    {
      foreach ($importer->getErrors() as $error)
      {
        $this->info($error);
      }
    }

    // Try to remove tmp file from uploads/tmp.
    if (isset($parameters['file']) && false === unlink($parameters['file']['tmp_name']))
    {
      // Issue warning if unable to delete but do not show job as failed because of this.
      $this->error($this->i18n->__('Failed to delete temporary file %1 -- please check your folder permissions.', array('%1' => $parameters['file']['tmp_name'])));
    }

    // Mark job as complete.
    $this->info($this->i18n->__('Import complete.'));
    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }

  /**
   * Configure all params for the CSV load.
   *
   * @param  reference to QubitCsvImport object
   *         array()
   *
   * @return null
   */
  private function setCsvImportParams(&$importer, $parameters)
  {
    foreach ($parameters as $key => $value)
    {
      if (empty($value))
      {
        continue;
      }

      switch ($key)
      {
        case 'doCsvTransform':
          $this->info($this->i18n->__('Applying transformation to CSV file.'));
          $importer->doCsvTransform = $parameters['doCsvTransform'];
          break;
        case 'index':
          if ('event' != $parameters['objectType'])
          {
            $this->info($this->i18n->__('Indexing imported records.'));
            $importer->indexDuringImport = $parameters['index'];
          }
          break;
        case 'skip-unmatched':
          $this->info($this->i18n->__('Skipping unmatched records.'));
          $importer->skipUnmatched = $parameters['skip-unmatched'];
          break;
        case 'skip-matched':
          $this->info($this->i18n->__('Skipping matched records.'));
          $importer->skipMatched = $parameters['skip-matched'];
          break;
        case 'update':
          $this->info($this->i18n->__('Update type: %1', array('%1' => $parameters['update'])));
          $importer->updateType = $parameters['update'];
          break;
        case 'repositorySlug':
          $this->info($this->i18n->__('Repository: %1', array('%1' => $parameters['repositorySlug'])));
          $importer->limit = $parameters['repositorySlug'];
          break;
        case 'collectionSlug':
          // collectionSlug, if specified, should take precedence over repositorySlug.
          $this->info($this->i18n->__('Collection: %1', array('%1' => $parameters['collectionSlug'])));
          $importer->limit = $parameters['collectionSlug'];
          break;
        case 'parentId':
          $importer->setParent($parameters['parentId']);
          break;
      }
    }
  }

  /**
   * Configure all params for the XML load.
   *
   * @param  reference to QubitXmlImport object
   *         array() reference
   *
   * @return array()
   */
  private function setXmlImportParams(&$importer, &$parameters)
  {
    $options = array();

    $options['strictXmlParsing'] = false;

    foreach ($parameters as $key => $value)
    {
      if (empty($value))
      {
        continue;
      }

      switch ($key)
      {
        case 'index':
          $this->info($this->i18n->__('Indexing imported records.'));
          $options['index'] = $parameters['index'];
          break;
        case 'skip-unmatched':
          $this->info($this->i18n->__('Skipping unmatched records.'));
          $options['skip-unmatched'] = $parameters['skip-unmatched'];
          break;
        case 'skip-matched':
          $this->info($this->i18n->__('Skipping matched records.'));
          $options['skip-matched'] = $parameters['skip-matched'];
          break;
        case 'update':
          $this->info($this->i18n->__('Update type: %1', array('%1' => $parameters['update'])));
          if ('import-as-new' != $parameters['update'])
          {
            $options['update'] = $parameters['update'];
          }
          break;
        case 'repositorySlug':
          $this->info($this->i18n->__('Repository: %1', array('%1' => $parameters['repositorySlug'])));
          $options['limit'] = $parameters['repositorySlug'];
          break;
        case 'collectionSlug':
          $this->info($this->i18n->__('Collection: %1', array('%1' => $parameters['collectionSlug'])));
          $options['limit'] = $parameters['collectionSlug'];
          break;
        case 'parentId':
          $importer->setParent($parameters['parentId']);
          break;
      }
    }
    return $options;
  }

}
