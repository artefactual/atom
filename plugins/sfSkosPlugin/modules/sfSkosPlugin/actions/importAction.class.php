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

class sfSkosPluginImportAction extends DefaultEditAction
{
  public static
    $NAMES = array(
      'file',
      'taxonomy',
      'url');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'file':
        $this->form->setWidget('file', new sfWidgetFormInputFile);
        $this->form->setValidator('file', new sfValidatorFile);

        break;

      case 'taxonomy':
        $id = $this->context->routing->generate(null, array($this->taxonomy, 'module' => 'taxonomy'));
        $this->form->setValidator('taxonomy', new sfValidatorString(array('required' => true)));

        if (isset($this->resource))
        {
          $this->form->setDefault('taxonomy', $id);
          $this->form->setWidget('taxonomy', new sfWidgetFormInputHidden);
        }
        else
        {
          $choices = array($id => $this->taxonomy); reset($choices);
          $this->form->setDefault('taxonomy', key($choices));
          $this->form->setWidget('taxonomy', new sfWidgetFormSelect(array('choices' => $choices), array('class' => 'form-autocomplete')));
        }

        break;

      case 'url':
        $this->form->setValidator('url', new QubitValidatorUrl);
        $this->form->setWidget('url', new sfWidgetFormInput(array(), array('placeholder' => 'https://')));

        break;
    }
  }

  protected function earlyExecute()
  {
    // Use 'Places' as default taxonomy
    $this->taxonomy = QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID);
    $this->parent = QubitTerm::getById(QubitTerm::ROOT_ID);

    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;

      if ('QubitTaxonomy' == $this->resource->className)
      {
        $this->taxonomy = QubitTaxonomy::getById($this->resource->id);
      }
      else
      {
        $this->parent = QubitTerm::getById($this->resource->id);
        $this->taxonomy = $this->parent->taxonomy;
      }
    }

    // Check user authorization
    if (!QubitAcl::check($this->parent, 'create'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // Setup custom form validator
    $this->form->getValidatorSchema()->setPostValidator(new sfValidatorCallback(array('callback' => function ($validator, $values) {
      if ($this->parent->id != QubitTerm::ROOT_ID && $this->parent->taxonomyId != $this->taxonomy->id)
      {
        throw new sfValidatorError($validator, $this->context->i18n->__('The current term does not belong to the taxonomy chosen.'));
      }

      if (is_null($values['file']) && is_null($values['url']))
      {
        throw new sfValidatorError($validator, $this->context->i18n->__('You must select a source to continue.'));
      }

      return $values;
    })));
  }

  public function execute($request)
  {
    parent::execute($request);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters(), $request->getFiles());

      if ($this->form->isValid())
      {
        $params = $this->context->routing->parse(Qubit::pathInfo($this->form->getValue('taxonomy')));
        $taxonomyId = $params['_sf_route']->resource->id;
        $parentId = is_null($this->parent) ? null : $this->parent->id;

        try
        {
          $job = $this->doBackgroundImport($taxonomyId, $parentId);
        }
        catch (Exception $e)
        {
          $this->getUser()->setFlash('error', $this->context->i18n->__('Something wrong happened! Please check out the logs. Error: %1%', array('%1%' => $e->getMessage()), array('persist' => false)));

          return;
        }

        $this->getUser()->setFlash('notice', $this->context->i18n->__('Import file initiated. Check %1%job %2%%3% to view the status of the import.', array(
          '%1%' => sprintf('<a href="%s">', $this->context->routing->generate(null, array('module' => 'jobs', 'action' => 'report', 'id' => $job->id))),
          '%2%' => $job->id,
          '%3%' => '</a>'
        )));

        $this->redirect(array('module' => 'sfSkosPlugin', 'action' => 'import'));
      }
    }
  }

  protected function doBackgroundImport($taxonomyId, $parentId)
  {
    $payload = array(
      'importType' => 'skos',
      'taxonomyId' => $taxonomyId,
      'parentId' => $parentId);

    // We know at this point that we have either a file or a remote resource
    if (null !== $this->form->getValue('file'))
    {
      // TODO: moveUploadFile only works with request data, we should rely on
      // the cleaned up version that the framework provides us. This seems to
      // be a pattern in AtoM.
      $file = Qubit::moveUploadFile($this->request->getFiles('file'));
      $payload['location'] = 'file://'.$file['tmp_name'];
      $payload['file'] = $file;
    }
    else
    {
      $payload['location'] = $this->form->getValue('url');
    }

    return QubitJob::runJob('arFileImportJob', $payload);
  }
}
