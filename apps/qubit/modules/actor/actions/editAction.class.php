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
 * Controller for editing actor information.
 *
 * @package    AccesstoMemory
 * @subpackage actor
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class ActorEditAction extends DefaultEditAction
{
  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = new QubitActor;

    // Make root actor the parent of new actors
    $this->resource->parentId = QubitActor::ROOT_ID;

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
      // Check user authorization against Actor ROOT
      if (!QubitAcl::check(QubitActor::getById(QubitActor::ROOT_ID), 'create'))
      {
        QubitAcl::forwardUnauthorized();
      }
    }

    $this->form->setDefault('next', $this->request->getReferer());
    $this->form->setValidator('next', new sfValidatorString);
    $this->form->setWidget('next', new sfWidgetFormInputHidden);
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'entityType':
        $this->form->setDefault('entityType', $this->context->routing->generate(null, array($this->resource->entityType, 'module' => 'term')));
        $this->form->setValidator('entityType', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::ACTOR_ENTITY_TYPE_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('entityType', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'authorizedFormOfName':
      case 'corporateBodyIdentifiers':
      case 'datesOfExistence':
      case 'descriptionIdentifier':
      case 'institutionResponsibleIdentifier':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'functions':
      case 'generalContext':
      case 'history':
      case 'internalStructures':
      case 'legalStatus':
      case 'mandates':
      case 'places':
      case 'revisionHistory':
      case 'rules':
      case 'sources':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);

        break;

      case 'maintainingRepository':
        $choices = array();
        if (null !== $repo = $this->resource->getMaintainingRepository())
        {
          $repoRoute = $this->context->routing->generate(null, array($repo, 'module' => 'repository'));
          $choices[$repoRoute] = $repo;
          $this->form->setDefault('maintainingRepository', $repoRoute);
        }

        $this->form->setValidator('maintainingRepository', new sfValidatorString);
        $this->form->setWidget('maintainingRepository', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

        case 'subjectAccessPoints':
        case 'placeAccessPoints':
          $criteria = new Criteria;
          $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->resource->id);
          $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
          switch ($name)
          {
            case 'subjectAccessPoints':
              $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::SUBJECT_ID);

              break;

            case 'placeAccessPoints':
              $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::PLACE_ID);

              break;
          }

          $value = $choices = array();
          foreach ($this[$name] = QubitObjectTermRelation::get($criteria) as $item)
          {
            $choices[$value[] = $this->context->routing->generate(null, array($item->term, 'module' => 'term'))] = $item->term;
          }

          $this->form->setDefault($name, $value);
          $this->form->setValidator($name, new sfValidatorPass);
          $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

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
      case 'authorizedFormOfName':
        // Avoid duplicates (used in autocomplete.js)
        if (filter_var($this->request->getPostParameter('linkExisting'), FILTER_VALIDATE_BOOLEAN))
        {
          $criteria = new Criteria;
          $criteria->addJoin(QubitObject::ID, QubitActorI18n::ID);
          $criteria->add(QubitObject::CLASS_NAME, get_class($this->request));
          $criteria->add(QubitActorI18n::CULTURE, $this->context->user->getCulture());
          $criteria->add(QubitActorI18n::AUTHORIZED_FORM_OF_NAME, $this->form->getValue('authorizedFormOfName'));
          if (null !== $actor = QubitActor::getOne($criteria))
          {
            $this->redirect(array($actor));

            return;
          }
        }

        return parent::processField($field);

      case 'entityType':
        unset($this->resource->entityType);

        $value = $this->form->getValue('entityType');
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->resource->entityType = $params['_sf_route']->resource;
        }

        break;

      case 'maintainingRepository':
        $value = $this->form->getValue('maintainingRepository');
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->resource->setOrDeleteMaintainingRepository($params['_sf_route']->resource);
        }
        else
        {
          $this->resource->setOrDeleteMaintainingRepository();
        }

        break;

      case 'subjectAccessPoints':
      case 'placeAccessPoints':
        $value = $filtered = array();

        if (is_array($formItems = $this->form->getValue($field->getName())))
        {
          foreach ($formItems as $item)
          {
            $params = $this->context->routing->parse(Qubit::pathInfo($item));
            $resource = $params['_sf_route']->resource;
            $value[$resource->id] = $filtered[$resource->id] = $resource;
          }
        }

        if (is_array($this[$field->getName()]))
        {
          foreach ($this[$field->getName()] as $item)
          {
            if (isset($value[$item->term->id]))
            {
              unset($filtered[$item->term->id]);
            }
            else
            {
              $item->indexObjectOnDelete = false;
              $item->delete();
            }
          }
        }

        if (is_array($filtered))
        {
          foreach ($filtered as $item)
          {
            $relation = new QubitObjectTermRelation;
            $relation->term = $item;

            $this->resource->objectTermRelationsRelatedByobjectId[] = $relation;
          }
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
        $this->processForm();

        $this->resource->save();

        if (isset($request->id) && (0 < strlen($next = $this->form->getValue('next'))))
        {
          $this->redirect($next);
        }

        $this->redirect(array($this->resource, 'module' => 'actor'));
      }
    }

    QubitDescription::addAssets($this->response);
  }
}
