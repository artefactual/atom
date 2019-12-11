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

class AccessionEditAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'acquisitionType',
      'appraisal',
      'archivalHistory',
      'creators',
      'date',
      'identifier',
      'identifierAvailableCheckUrl',
      'informationObjects',
      'locationInformation',
      'resourceType',
      'physicalCharacteristics',
      'processingNotes',
      'processingPriority',
      'processingStatus',
      'receivedExtentUnits',
      'scopeAndContent',
      'sourceOfAcquisition',
      'title');

  public function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = new QubitAccession;

    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;

      // Check user authorization
      if (!QubitAcl::check($this->resource, 'update'))
      {
        QubitAcl::forwardUnauthorized();
      }
    }
    else
    {
      // Check user authorization
      if (!QubitAcl::check($this->resource, 'create'))
      {
        QubitAcl::forwardUnauthorized();
      }
    }

    $title = $this->context->i18n->__('Add new accession record');
    if (isset($this->getRoute()->resource))
    {
      if (1 > strlen($title = $this->resource->__toString()))
      {
        $title = $this->context->i18n->__('Untitled');
      }

      $title = $this->context->i18n->__('Edit %1%', array('%1%' => $title));
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    $this->relatedDonorComponent = new AccessionRelatedDonorComponent($this->context, 'accession', 'relatedDonor');
    $this->relatedDonorComponent->resource = $this->resource;
    $this->relatedDonorComponent->execute($this->request);

    $this->eventComponent = new sfIsadPluginEventComponent($this->context, 'sfIsadPlugin', 'event');
    $this->eventComponent->resource = $this->resource;
    $this->eventComponent->execute($this->request);
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'acquisitionType':
        $this->form->setDefault('acquisitionType', $this->context->routing->generate(null, array($this->resource->acquisitionType, 'module' => 'term')));
        $this->form->setValidator('acquisitionType', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::ACCESSION_ACQUISITION_TYPE_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('acquisitionType', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'processingPriority':
        $this->form->setDefault('processingPriority', $this->context->routing->generate(null, array($this->resource->processingPriority, 'module' => 'term')));
        $this->form->setValidator('processingPriority', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::ACCESSION_PROCESSING_PRIORITY_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('processingPriority', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'processingStatus':
        $this->form->setDefault('processingStatus', $this->context->routing->generate(null, array($this->resource->processingStatus, 'module' => 'term')));
        $this->form->setValidator('processingStatus', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::ACCESSION_PROCESSING_STATUS_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('processingStatus', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'resourceType':
        $this->form->setDefault('resourceType', $this->context->routing->generate(null, array($this->resource->resourceType, 'module' => 'term')));
        $this->form->setValidator('resourceType', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::ACCESSION_RESOURCE_TYPE_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('resourceType', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'creators':
        $value = $choices = array();
        foreach ($this->creators = QubitRelation::getRelationsByObjectId($this->resource->id, array('typeId' => QubitTerm::CREATION_ID)) as $item)
        {
          $choices[$value[] = $this->context->routing->generate(null, array($item->subject, 'module' => 'actor'))] = $item->subject;
        }

        $this->form->setDefault('creators', $value);
        $this->form->setValidator('creators', new sfValidatorPass);
        $this->form->setWidget('creators', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

        break;

      case 'date':
        $this->form->setDefault('date', Qubit::renderDate($this->resource['date']));

        if (!isset($this->resource->id))
        {
          $dt = new DateTime;
          $this->form->setDefault('date', $dt->format('Y-m-d'));
        }

        $this->form->setValidator('date', new sfValidatorString);
        $this->form->setWidget('date', new sfWidgetFormInput);

        break;

      case 'identifier':
        $this->form->setDefault('identifier', $this->resource['identifier']);

        // If accession mask enable setting isn't set or is set to on, then populate default with mask value
        if (!isset($this->resource->id) && QubitAccession::maskEnabled())
        {
          $dt = new DateTime;
          $this->form->setDefault('identifier', QubitAccession::nextAvailableIdentifier());
        }

        $this->form->setValidator('identifier', new QubitValidatorAccessionIdentifier(array('required' => true, 'resource' => $this->resource)));
        $this->form->setWidget('identifier', new sfWidgetFormInput());

        break;

      case 'identifierAvailableCheckUrl':
        // Store URL for checking identifiers as a hidden field so we can relay it to JavaScript validation
        $routingParams = array('module' => 'accession', 'action' => 'checkIdentifierAvailable', 'accession_id' => $this->resource->id);
        $this->form->setDefault($name, $this->context->getRouting()->generate(null, $routingParams));
        $this->form->setWidget($name, new sfWidgetFormInputHidden);

        break;

      case 'title':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'appraisal':
      case 'archivalHistory':
      case 'locationInformation':
      case 'physicalCharacteristics':
      case 'processingNotes':
      case 'receivedExtentUnits':
      case 'scopeAndContent':
      case 'sourceOfAcquisition':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);

        break;

      case 'informationObjects':
        $criteria = new Criteria;
        $criteria->add(QubitRelation::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitRelation::TYPE_ID, QubitTerm::ACCESSION_ID);

        ProjectConfiguration::getActive()->loadHelpers('Qubit');

        $value = $choices = array();
        foreach ($this->informationObjects = QubitRelation::get($criteria) as $item)
        {
          $choices[$value[] = $this->context->routing->generate(null, array($item->subject, 'module' => 'informationobject'))] = render_title($item->subject, false);
        }

        $this->form->setDefault($name, $value);
        $this->form->setValidator($name, new sfValidatorPass);
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

        break;

      default:

        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'creators':
        $value = $filtered = array();

        if (is_array($formCreators = $this->form->getValue('creators')))
        {
          foreach ($formCreators as $item)
          {
            $params = $this->context->routing->parse(Qubit::pathInfo($item));
            $resource = $params['_sf_route']->resource;
            $value[$resource->id] = $filtered[$resource->id] = $resource;
          }
        }

        foreach ($this->creators as $item)
        {
          if (isset($value[$item->objectId]))
          {
            unset($filtered[$item->objectId]);
          }
          else
          {
            $item->delete();
          }
        }

        foreach ($filtered as $item)
        {
          $relation = new QubitRelation;
          $relation->subject = $item;
          $relation->typeId = QubitTerm::CREATION_ID;

          $this->resource->relationsRelatedByobjectId[] = $relation;
        }

        break;

      case 'acquisitionType':
      case 'processingPriority':
      case 'processingStatus':
      case 'resourceType':
        unset($this->resource[$field->getName()]);

        $value = $this->form->getValue($field->getName());
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->resource[$field->getName()] = $params['_sf_route']->resource;
        }

        break;

      case 'identifier':
        $value = $this->form->getValue($field->getName());
        $this->resource['identifier'] = $value;
        break;

      case 'informationObjects':
        $value = $filtered = array();

        if (is_array($formInformationObjects = $this->form->getValue('informationObjects')))
        {
          foreach ($formInformationObjects as $item)
          {
            $params = $this->context->routing->parse(Qubit::pathInfo($item));
            $resource = $params['_sf_route']->resource;
            $value[$resource->id] = $filtered[$resource->id] = $resource;
          }
	}

        foreach ($this->informationObjects as $item)
        {
          if (isset($value[$item->subjectId]))
          {
            unset($filtered[$item->subjectId]);
          }
          else
          {
            $item->delete();
          }
        }

        foreach ($filtered as $item)
        {
          $relation = new QubitRelation;
          $relation->subject = $item;
          $relation->typeId = QubitTerm::ACCESSION_ID;
          $relation->indexOnSave = false;

          $this->resource->relationsRelatedByobjectId[] = $relation;
        }

        break;

      default:
        return parent::processField($field);
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    // Parameter "accession" is sent when creating an accrual
    if (isset($request->accession))
    {
      $params = $this->context->routing->parse(Qubit::pathInfo($request->accession));

      if (isset($params['_sf_route']))
      {
        $this->accession = $params['_sf_route']->resource;

        if ($this->accession->isAccrual())
        {
          throw new sfException('This accession can\'t be created.');
        }

        // Add id of the information object source
        $this->form->setDefault('accession', $request->accession);
        $this->form->setValidator('accession', new sfValidatorString);
        $this->form->setWidget('accession', new sfWidgetFormInputHidden);
      }
    }

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->relatedDonorComponent->processForm();

        $this->eventComponent->processForm();

        if (isset($this->request->deleteRelations))
        {
          foreach ($this->request->deleteRelations as $item)
          {
            $params = $this->context->routing->parse(Qubit::pathInfo($item));
            $params['_sf_route']->resource->delete();
          }
        }

        // Relation between accesion will only be accepted if the object is new
        if (!isset($this->resource->id) && isset($this->accession))
        {
          $relation = new QubitRelation;
          $relation->typeId = QubitTerm::ACCRUAL_ID;
          $relation->object = $this->accession;

          $this->resource->relationsRelatedBysubjectId[] = $relation;
        }

        $this->processForm();

        $this->resource->save();

        $this->redirect(array($this->resource, 'module' => 'accession'));
      }
    }

    QubitDescription::addAssets($this->response);
  }
}
