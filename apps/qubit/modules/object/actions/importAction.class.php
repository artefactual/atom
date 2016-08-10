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

    $options = array('noIndex' => ($request->getParameter('noindex') == 'on') ? false : true,
                     'doCsvTransform' => ($request->getParameter('doCsvTransform') == 'on') ? true : false,
                     'parent' => (isset($this->getRoute()->resource) ? $this->getRoute()->resource : null),
                     'csvObjectType' => $request->csvObjectType,
                     // Choose import type based on importType parameter
                     // This decision used to be based in the file extension but some users
                     // experienced problems when the extension was omitted
                     'importType' => $importType,
                     'file' => $request->getFiles('file'));

    try
    {
      QubitJob::runJob('arFileImportJob', $options);

      // Let user know import has started
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
      $jobManageUrl = url_for(array('module' => 'jobs', 'action' => 'browse'));
      $message = '<strong>Import of ' . strtoupper($importType) . ' file initiated.</strong> Check <a href="'. $jobManageUrl . '">job management</a> page to view the status of the import.';
      $this->context->user->setFlash('notice', $this->context->i18n->__($message));
    }
    catch (sfException $e)
    {
      $this->context->user->setFlash('error', $e->getMessage());
      $this->redirect($importSelectRoute);
    }
  }
}
