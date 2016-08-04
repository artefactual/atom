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
 * Ingest an uploaded file and import it as an object w/relations
 *
 * @package    AccesstoMemory
 * @subpackage import/export
 * @author     David Juhasz <david@artefactual.com>
 * @author     MJ Suhonos <mj@suhonos.ca>
 */
class ObjectImportAction extends sfAction
{
  public function execute($request)
  {
    $this->timer = new QubitTimer;
    $file = $request->getFiles('file');

    // Import type, CSV or XML?
    $importType = $request->getParameter('importType', 'xml');

    // We will use this later to redirect users back to the importSelect page
    if (isset($this->getRoute()->resource))
    {
      $importSelectRoute = array($this->getRoute()->resource, 'module' => 'object', 'action' => 'importSelect', 'type' => $importType);
    }
    else
    {
      $importSelectRoute = array('module' => 'object', 'action' => 'importSelect', 'type' => $importType);
    }

    // if we got here without a file upload, go to file selection
    if (0 == count($file))
    {
      $this->redirect($importSelectRoute);
    }

    // set indexing preference
    if (isset($request->noindex))
    {
      QubitSearch::disable();
    }

    // Zip file
    if ('zip' == strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)))
    {
      // Check whether PHP Zip extension is installed
      if (!class_exists('ZipArchive'))
      {
        $this->context->user->setFlash('error', $this->context->i18n->__('PHP Zip extension could not be found.'));
        $this->redirect($importSelectRoute);
      }

      // Create temporary directory
      $zipDirectory = $file['tmp_name'].'-zip';
      if (!file_exists($zipDirectory))
      {
        mkdir($zipDirectory, 0755);
      }

      // Extract the zip archive into the temporary folder
      // TODO: need some error handling here
      $zip = new ZipArchive();
      $zip->open($file['tmp_name']);
      $zip->extractTo($zipDirectory);
      $zip->close();

      $files = Qubit::dirTree($zipDirectory);

      foreach ($files as $importFile)
      {
        // Try to free up memory
        unset($importer);

        // Choose import type based on file extension, eg. csv, xml
        switch (strtolower(pathinfo($importFile, PATHINFO_EXTENSION)))
        {
          case 'csv':
            $importer = new QubitCsvImport;
            if (isset($this->getRoute()->resource)) $importer->setParent($this->getRoute()->resource);
            $importer->import($importFile);

            break;

          case 'xml':
            $importer = new QubitXmlImport;
            if (isset($this->getRoute()->resource)) $importer->setParent($this->getRoute()->resource);
            $importer->import($importFile, array('strictXmlParsing' => false));

            break;
        }
      }
    }
    else
    {
      try
      {
        // Choose import type based on importType parameter
        // This decision used to be based in the file extension but some users
        // experienced problems when the extension was omitted
        switch ($importType)
        {
          case 'csv':
            $importer = new QubitCsvImport;
            $importer->indexDuringImport = ($request->getParameter('noindex') == 'on') ? false : true;
            $importer->doCsvTransform = ($request->getParameter('doCsvTransform') == 'on') ? true : false;
            if (isset($this->getRoute()->resource)) $importer->setParent($this->getRoute()->resource);
            $importer->import($file['tmp_name'], $request->csvObjectType, $file['name']);

            break;

          case 'xml':
            $importer = new QubitXmlImport;
            if (isset($this->getRoute()->resource)) $importer->setParent($this->getRoute()->resource);
            $importer->import($file['tmp_name'], array('strictXmlParsing' => false), $file['name']);

            break;

          default:
            $this->context->user->setFlash('error', $this->context->i18n->__('Unable to import selected file: unknown file extension.'));
            $this->redirect($importSelectRoute);

            break;
        }
      }
      catch (sfException $e)
      {
        $this->context->user->setFlash('error', $e->getMessage());
        $this->redirect($importSelectRoute);
      }

      $this->errors = $importer->getErrors();
      $this->rootObject = $importer->getRootObject();
      $this->objectType = strtr(get_class($this->rootObject), array('Qubit' => ''));
    }
  }
}
