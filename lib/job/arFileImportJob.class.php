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
 * Job worker for XML and CSV imports initiated from the WebUI.
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
    $this->info($this->i18n->__('Importing %1 file: %2.', array('%1' => strtoupper($parameters['importType']), '%2' => $parameters['file']['name'])));

    // set indexing preference
    if (true == $parameters['noIndex'])
    {
      QubitSearch::disable();
    }

    try
    {
      switch ($parameters['importType'])
      {
        case 'csv':
          $importer = new QubitCsvImport;
          if ($parameters['doCsvTransform']) { $this->info($this->i18n->__('Applying transformation to CSV file.')); }
          $importer->doCsvTransform = $parameters['doCsvTransform'];

          if ($parameters['noIndex']) { $this->info($this->i18n->__('Indexing imported records.')); }
          $importer->indexDuringImport = $parameters['noIndex'];

          if (null != $parameters['parent']) { $importer->setParent($parameters['parent']); }

          $importer->import($parameters['file']['tmp_name'], $request->csvObjectType, $parameters['file']['name']);

          break;

        case 'xml':
          $importer = new QubitXmlImport;
          if (null != $parameters['parent']) { $importer->setParent($parameters['parent']); }
          $importer->import($parameters['file']['tmp_name'], array('strictXmlParsing' => false), $parameters['file']['name']);

          break;

        default:
          // 'importType' defaults to 'CSV' by design if extension is blank or something unknown.
          // This was to prevent errors if csv file does not have the correct extension. See
          // modules/object/actions/importAction.class.php.  This default case should never be called.
          $this->error($this->i18n->__('Unable to import selected file: unknown file extension.'));
          return false;

          break;
      }
    }
    catch (sfException $e)
    {
      $this->error($e->getMessage());
      return false;
    }

    // Mark job as complete
    $this->info($this->i18n->__('Import complete.'));
    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }

}
