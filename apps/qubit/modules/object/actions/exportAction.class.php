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

  public function execute($request)
  {
    parent::execute($request);

    $this->response->addJavaScript('exportOptions', 'last');

    // Export type, CSV or XML?
    $this->type = $request->getParameter('format', 'csv');
    $this->objectType = $request->getParameter('objectType');

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

    $options = array(
      'params' => array('fromClipboard' => true, 'slugs' => $this->context->user->getClipboard()->getAll()),
      'current-level-only' => 'on' !== $request->getParameter('includeDescendants'),
      'public' => 'on' !== $request->getParameter('includeDrafts'),
      'objectType' => $this->objectType,
      'levels' => $levelsOfDescription,
      'name' => $this->context->i18n->__('CSV export')
    );

    // When exporting actors, ensure aliases and relations are also exported.
    if ('actor' === $this->objectType && 'CSV' === strtoupper($this->type))
    {
      $options['aliases'] = true;
      $options['relations'] = true;
    }

    try
    {
      $job = QubitJob::runJob($this->getJobNameString(), $options);

      // If anonymous user, store job ID in session
      if (!$this->context->user->isAuthenticated())
      {
        $manager = new QubitUnauthenticatedUserJobManager($this->context->user);
        $manager->addJobAssociation($job);
      }

      if ($this->context->user->isAuthenticated())
      {
        $message = $this->context->i18n->__('%1%Export initiated.%2% Check %3%job management%4% page to download the results when it has completed.', array(
          '%1%' => '<strong>',
          '%2%' => '</strong>',
          '%3%' => sprintf('<a href="%s">', $this->context->routing->generate(null, array('module' => 'jobs', 'action' => 'browse'))),
          '%4%' => '</a>'));
      }
      else
      {
        $message = $this->context->i18n->__('Export initiated. Progress will be reported, and a download link provided, in a subsequent notification &mdash; <a href="javascript:location.reload();">refresh the page</a> for updates.');
      }

      $this->context->user->setFlash('notice', $message);
    }
    catch (sfException $e)
    {
      $this->context->user->setFlash('error', $e->getMessage());
      return sfView::SUCCESS;
    }
  }

  private function getJobNameString()
  {
    switch ($this->objectType)
    {
      case 'informationObject':
        if ('CSV' == strtoupper($this->type))
        {
          return 'arInformationObjectCsvExportJob';
        }
        else
        {
          return 'arInformationObjectXmlExportJob';
        }

      case 'actor':
        if ('CSV' == strtoupper($this->type))
        {
          return 'arActorCsvExportJob';
        }
        else
        {
          return 'arActorXmlExportJob';
        }

      case 'repository':
        return 'arRepositoryCsvExportJob';

      default:
        throw new sfException("Invalid object type specified: {$this->objectType}");
    }
  }
}
