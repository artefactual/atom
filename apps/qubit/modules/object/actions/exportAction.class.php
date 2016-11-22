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
    // Create array of selections to pass to background job where Term ID will
    // be key, and Term description is value.
    foreach ($this->levels as $value)
    {
      $levelsOfDescription[$value] = $this->choices[$value];
    }

    $options = array('params' => array('fromClipboard' => true,
                                       'slugs' => $this->context->user->getClipboard()->getAll()),
                     'current-level-only' => ('on' == $request->getParameter('includeDescendants')) ? false : true,
                     'public' => ($request->getParameter('includeDrafts') == 'on') ? false : true,
                     'objectType' => $request->getParameter('objectType'),
                     'levels' => $levelsOfDescription);

    try
    {
      if ('CSV' == strtoupper($this->type))
      {
        QubitJob::runJob('arInformationObjectCsvExportJob', $options);
      }
      else
      {
        QubitJob::runJob('arXmlExportJob', $options);
      }

      // Let user know export has started
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

      $message = $this->context->i18n->__('%1%Export of descriptions initiated.%2% Check %3%job management%4% page to download the results when it has completed.', array(
        '%1%' => '<strong>',
        '%2%' => '</strong>',
        '%3%' => sprintf('<a href="%s">', url_for(array('module' => 'jobs', 'action' => 'browse'))),
        '%4%' => '</a>'));

      $this->context->user->setFlash('notice', $message);
    }
    catch (sfException $e)
    {
      $this->context->user->setFlash('error', $e->getMessage());
      return sfView::SUCCESS;
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    $this->response->addJavaScript('exportOptions', 'last');

    // Export type, CSV or XML?
    $this->type = $request->getParameter('type', 'csv');

    $this->redirectUrl = array('module' => 'object', 'action' => 'export');
    if (null !== $referrer = $request->getReferer())
    {
      $this->redirectUrl = $referrer;
    }

    $this->title = $this->context->i18n->__('Export');

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->processForm();

        $this->doBackgroundExport($request);

        $this->redirect($this->redirectUrl);
      }
    }
  }
}
