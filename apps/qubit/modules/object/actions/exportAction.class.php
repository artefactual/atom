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

class ObjectExportAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'levels');

  private $choices = array();

  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'levels':
        $this->form->setValidator('levels', new sfValidatorPass);

        foreach (QubitTerm::getLevelsOfDescription() as $item)
        {
          $this->choices[$item->id] = $item->__toString();
        }

        $size = count($this->choices);
        if ($size === 0)
        {
          $size = 4;
        }

        $this->form->setWidget('levels', new sfWidgetFormSelect(array('choices' => $this->choices, 'multiple' => true), array('size' => $size)));

        break;

      default:
        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'levels':
        $this->levels = $this->form->getValue('levels');
        if (empty($this->levels))
        {
          $this->levels = array();
        }

        break;
    }
  }

  protected function doBackgroundExport($request)
  {
    // Export type, CSV or XML?
    $exportType = $request->getParameter('format', 'xml');

    // We will use this later to redirect users back to the importSelect page
    if (isset($this->getRoute()->resource))
    {
      $exportRoute = array($this->getRoute()->resource, 'module' => 'object', 'action' => 'importSelect', 'type' => $exportType);
    }
    else
    {
      $exportRoute = array('module' => 'object', 'action' => 'export', 'type' => $exportType);
    }

    // Create array of selections to pass to background job where Term ID will
    // be key, and Term description is value.
    foreach($this->levels as $key => $value)
    {
      $levelsOfDescription[$value] = $this->choices[$value];
    }

    $options = array('params' => array('fromClipboard' => true,
                                       'slugs' => $this->context->user->getClipboard()->getAll()),
                     'include-all-levels' => ('on' == $request->getParameter('includeAllLevels')) ? true : false,
                     'current-level-only' => ('on' == $request->getParameter('includeDescendants')) ? false : true,
                     'public' => ($request->getParameter('includeDrafts') == 'on') ? false : true,
                     'objectType' => $request->getParameter('objectType'),
                     'levels' => $levelsOfDescription);

    try
    {
      if ('CSV' == strtoupper($exportType))
      {
        QubitJob::runJob('arInformationObjectCsvExportJob', $options);
      }
      else
      {
        QubitJob::runJob('arXmlExportJob', $options);
      }

      // Let user know import has started
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
      $jobManageUrl = url_for(array('module' => 'jobs', 'action' => 'browse'));
      $message = '<strong>Export of descriptions initiated.</strong> Check <a href="'. $jobManageUrl . '">job management</a> page to download the results when it has completed.';
      $this->context->user->setFlash('notice', $this->context->i18n->__($message));
    }
    catch (sfException $e)
    {
      $this->context->user->setFlash('error', $e->getMessage());
      $this->redirect($exportRoute);
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->processForm();

        $this->doBackgroundExport($request);

        $this->setTemplate('exportResults');
      }
    }
    else
    {
      $this->response->addJavaScript('exportOptions', 'last');

      if (isset($request->type))
      {
        $this->type = $request->type;
      }

      $this->title = $this->context->i18n->__('Export');
    }
  }
}
