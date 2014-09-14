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
 * Controller for editing repository information.
 *
 * @package    AccesstoMemory
 * @subpackage repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class RepositoryEditAction extends DefaultEditAction
{
  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = new QubitRepository;

    // Make root repository the parent of new repositories
    $this->resource->parentId = QubitRepository::ROOT_ID;

    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;

      // Check that this isn't the root
      if (!isset($this->resource->parent))
      {
        $this->forward404();
      }

      // Check user authorization
      if (!QubitAcl::check($this->resource, 'update') && !QubitAcl::check($this->resource, 'translate'))
      {
        QubitAcl::forwardUnauthorized();
      }

      // Add optimistic lock
      $this->form->setDefault('serialNumber', $this->resource->serialNumber);
      $this->form->setValidator('serialNumber', new sfValidatorInteger);
      $this->form->setWidget('serialNumber', new sfWidgetFormInputHidden);
    }
    else
    {
      // Check user authorization against ROOT repository
      if (!QubitAcl::check(QubitRepository::getById(QubitRepository::ROOT_ID), 'create'))
      {
        QubitAcl::forwardUnauthorized();
      }
    }

    $this->contactInformationEditComponent = new ContactInformationEditComponent($this->context, 'contactinformation', 'editContactInformation');
    $this->contactInformationEditComponent->resource = $this->resource;
    $this->contactInformationEditComponent->execute($this->request);
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'type':
        $criteria = new Criteria;
        $criteria = $this->resource->addObjectTermRelationsRelatedByObjectIdCriteria($criteria);
        $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::REPOSITORY_TYPE_ID);

        $value = array();
        foreach ($this->relations = QubitObjectTermRelation::get($criteria) as $item)
        {
          $value[] = $this->context->routing->generate(null, array($item->term, 'module' => 'term'));
        }

        $this->form->setDefault('type', $value);
        $this->form->setValidator('type', new sfValidatorPass);

        $choices = array();
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::REPOSITORY_TYPE_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item->__toString();
        }

        $this->form->setWidget('type', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

        break;

      case 'thematicArea':
        $criteria = new Criteria;
        $criteria = $this->resource->addObjectTermRelationsRelatedByObjectIdCriteria($criteria);
        $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::THEMATIC_AREA_ID);

        $value = array();
        foreach ($this->thematicAreaRelations = QubitObjectTermRelation::get($criteria) as $item)
        {
          $value[] = $this->context->routing->generate(null, array($item->term, 'module' => 'term'));
        }

        $this->form->setDefault('thematicArea', $value);
        $this->form->setValidator('thematicArea', new sfValidatorPass);

        $choices = array();
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::THEMATIC_AREA_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item->__toString();
        }

        $choice[] = asort($choices);

        $this->form->setWidget('thematicArea', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

        break;

      case 'geographicSubregion':
        $criteria = new Criteria;
        $criteria = $this->resource->addObjectTermRelationsRelatedByObjectIdCriteria($criteria);
        $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID);

        $value = array();
        foreach ($this->geographicSubregionRelations = QubitObjectTermRelation::get($criteria) as $item)
        {
          $value[] = $this->context->routing->generate(null, array($item->term, 'module' => 'term'));
        }

        $this->form->setDefault('geographicSubregion', $value);
        $this->form->setValidator('geographicSubregion', new sfValidatorPass);

        $choices = array();
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item->__toString();
        }

        $choice[] = asort($choices);

        $this->form->setWidget('geographicSubregion', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

        break;

      case 'descDetail':
      case 'descStatus':
        $this->form->setDefault($name, $this->context->routing->generate(null, array($this->resource[$name], 'module' => 'term')));
        $this->form->setValidator($name, new sfValidatorString);

        switch ($name)
        {
          case 'descDetail':
            $id = QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID;

            break;

          case 'descStatus':
            $id = QubitTaxonomy::DESCRIPTION_STATUS_ID;

            break;
        }

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById($id) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'identifier':
      case 'authorizedFormOfName':
      case 'descIdentifier':
      case 'descInstitutionIdentifier':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'history':
      case 'geoculturalContext':
      case 'mandates':
      case 'internalStructures':
      case 'collectingPolicies':
      case 'buildings':
      case 'holdings':
      case 'findingAids':
      case 'openingTimes':
      case 'accessConditions':
      case 'disabledAccess':
      case 'researchServices':
      case 'reproductionServices':
      case 'publicFacilities':
      case 'descRules':
      case 'descRevisionHistory':
      case 'descSources':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);

        break;

      default:

        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'type':
        $value = $filtered = array();
        foreach ($this->form->getValue('type') as $item)
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item));
          $resource = $params['_sf_route']->resource;
          $value[$resource->id] = $filtered[$resource->id] = $resource;
        }

        foreach ($this->relations as $item)
        {
          if (isset($value[$item->termId]))
          {
            unset($filtered[$item->termId]);
          }
          else
          {
            $item->delete();
          }
        }

        foreach ($filtered as $item)
        {
          $relation = new QubitObjectTermRelation;
          $relation->term = $item;

          $this->resource->objectTermRelationsRelatedByobjectId[] = $relation;
        }

        break;

      case 'geographicSubregion':
        $value = $filtered = array();
        foreach ($this->form->getValue('geographicSubregion') as $item)
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item));
          $resource = $params['_sf_route']->resource;
          $value[$resource->id] = $filtered[$resource->id] = $resource;
        }

        foreach ($this->geographicSubregionRelations as $item)
        {
          if (isset($value[$item->termId]))
          {
            unset($filtered[$item->termId]);
          }
          else
          {
            $item->delete();
          }
        }

        foreach ($filtered as $item)
        {
          $relation = new QubitObjectTermRelation;
          $relation->term = $item;

          $this->resource->objectTermRelationsRelatedByobjectId[] = $relation;
        }

        break;

      case 'thematicArea':
        $value = $filtered = array();
        foreach ($this->form->getValue('thematicArea') as $item)
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item));
          $resource = $params['_sf_route']->resource;
          $value[$resource->id] = $filtered[$resource->id] = $resource;
        }

        foreach ($this->thematicAreaRelations as $item)
        {
          if (isset($value[$item->termId]))
          {
            unset($filtered[$item->termId]);
          }
          else
          {
            $item->delete();
          }
        }

        foreach ($filtered as $item)
        {
          $relation = new QubitObjectTermRelation;
          $relation->term = $item;

          $this->resource->objectTermRelationsRelatedByobjectId[] = $relation;
        }

        break;

      case 'descStatus':
      case 'descDetail':
        unset($this->resource[$field->getName()]);

        $value = $this->form->getValue($field->getName());
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->resource[$field->getName()] = $params['_sf_route']->resource;
        }

        break;

      default:

        return parent::processField($field);
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
        $this->contactInformationEditComponent->processForm();

        $this->processForm();

        $this->resource->save();

        $this->redirect(array($this->resource, 'module' => 'repository'));
      }
    }

    QubitDescription::addAssets($this->response);
  }
}
