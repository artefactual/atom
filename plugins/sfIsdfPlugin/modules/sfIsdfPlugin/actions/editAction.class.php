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

class sfIsdfPluginEditAction extends FunctionEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'type',
      'authorizedFormOfName',
      'parallelName',
      'otherName',
      'classification',
      'dates',
      'description',
      'history',
      'legislation',
      'descriptionIdentifier',
      'institutionIdentifier',
      'rules',
      'descriptionStatus',
      'descriptionDetail',
      'revisionHistory',
      'language',
      'script',
      'sources',
      'maintenanceNotes');

  protected function earlyExecute()
  {
    parent::earlyExecute();

    $this->isdf = new sfIsdfPlugin($this->resource);

    $title = $this->context->i18n->__('Add new function');
    if (isset($this->getRoute()->resource))
    {
      if (1 > strlen($title = $this->resource->__toString()))
      {
        $title = $this->context->i18n->__('Untitled');
      }

      $title = $this->context->i18n->__('Edit %1%', array('%1%' => $title));
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    $this->relatedAuthorityRecordComponent = new sfIsdfPluginRelatedAuthorityRecordComponent($this->context, 'sfIsdfPlugin', 'relatedAuthorityRecord');
    $this->relatedAuthorityRecordComponent->resource = $this->resource;
    $this->relatedAuthorityRecordComponent->isdf = $this->isdf;
    $this->relatedAuthorityRecordComponent->execute($this->request);

    $this->relatedFunctionComponent = new sfIsdfPluginRelatedFunctionComponent($this->context, 'sfIsdfPlugin', 'relatedFunction');
    $this->relatedFunctionComponent->resource = $this->resource;
    $this->relatedFunctionComponent->isdf = $this->isdf;
    $this->relatedFunctionComponent->execute($this->request);

    $this->relatedResourceComponent = new sfIsdfPluginRelatedResourceComponent($this->context, 'sfIsdfPlugin', 'relatedResource');
    $this->relatedResourceComponent->resource = $this->resource;
    $this->relatedResourceComponent->isdf = $this->isdf;
    $this->relatedResourceComponent->execute($this->request);
  }

  /**
   * Add fields to form
   *
   * @param $name string
   * @return void
   */
  protected function addField($name)
  {
    switch ($name)
    {
      case 'type':
        $this->form->setDefault('type', $this->context->routing->generate(null, array($this->resource->type, 'module' => 'term')));
        $this->form->setValidator('type', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::FUNCTION_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('type', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'maintenanceNotes':
        $this->form->setDefault('maintenanceNotes', $this->isdf->maintenanceNotes);
        $this->form->setValidator('maintenanceNotes', new sfValidatorString);
        $this->form->setWidget('maintenanceNotes', new sfWidgetFormTextarea);

        break;

      case 'authorizedFormOfName':
      case 'classification':
      case 'dates':
      case 'descriptionIdentifier':
      case 'institutionIdentifier':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'history':
      case 'description':
      case 'legislation':
      case 'rules':
      case 'revisionHistory':
      case 'sources':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);

        break;

      default:

        return parent::addField($name);
    }
  }

  /**
   * Process form fields
   *
   * @param $field mixed symfony form widget
   * @return void
   */
  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'type':
        unset($this->resource->type);

        $value = $this->form->getValue($field->getName());
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->resource->type = $params['_sf_route']->resource;
        }

        break;

      case 'maintenanceNotes':
        $this->isdf->maintenanceNotes = $this->form->getValue('maintenanceNotes');

        break;

      default:

        return parent::processField($field);
    }
  }

  protected function processForm()
  {
    $this->relatedAuthorityRecordComponent->processForm();
    $this->relatedFunctionComponent->processForm();
    $this->relatedResourceComponent->processForm();

    if (isset($this->request->deleteRelations))
    {
      foreach ($this->request->deleteRelations as $item)
      {
        $params = $this->context->routing->parse(Qubit::pathInfo($item));
        $params['_sf_route']->resource->delete();
      }
    }

    return parent::processForm();
  }
}
