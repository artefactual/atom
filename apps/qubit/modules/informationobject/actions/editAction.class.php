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
 * Get current state data for information object edit form.
 *
 * @package    AccesstoMemory
 * @subpackage informationobject
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class InformationObjectEditAction extends DefaultEditAction
{
  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = new QubitInformationObject;

    // Edit
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

    // Duplicate
    else if (isset($this->request->source))
    {
      $this->resource = QubitInformationObject::getById($this->request->source);

      // Check that object exists and that it is not the root
      if (!isset($this->resource) || !isset($this->resource->parent))
      {
        $this->forward404();
      }

      // Check user authorization
      if (!QubitAcl::check($this->resource, 'create'))
      {
        QubitAcl::forwardUnauthorized();
      }

      // Store source label
      $this->sourceInformationObjectLabel = new sfIsadPlugin($this->resource);

      // Remove identifier
      unset($this->resource->identifier);

      // Inherit parent level
      $this->form->setDefault('parent', $this->context->routing->generate(null, array($this->resource->parent, 'module' => 'informationobject')));
      $this->form->setValidator('parent', new sfValidatorString);
      $this->form->setWidget('parent', new sfWidgetFormInputHidden);

      // Add id of the information object source
      $this->form->setDefault('sourceId', $this->request->source);
      $this->form->setValidator('sourceId', new sfValidatorInteger);
      $this->form->setWidget('sourceId', new sfWidgetFormInputHidden);

      // Set publication status to "draft"
      $this->resource->setPublicationStatus(sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID));
    }

    // Create
    else
    {
      $this->form->setValidator('parent', new sfValidatorString);
      $this->form->setWidget('parent', new sfWidgetFormInputHidden);

      $getParams = $this->request->getGetParameters();
      if (isset($getParams['parent']))
      {
        $params = $this->context->routing->parse(Qubit::pathInfo($getParams['parent']));
        $this->parent = $params['_sf_route']->resource;
        $this->form->setDefault('parent', $getParams['parent']);
      }
      else
      {
        // Root is default parent
        $this->parent = QubitInformationObject::getById(QubitInformationObject::ROOT_ID);
        $this->form->setDefault('parent', $this->context->routing->generate(null, array($this->parent, 'module' => 'informationobject')));
      }

      // Check authorization
      if (!QubitAcl::check($this->parent, 'create'))
      {
        QubitAcl::forwardUnauthorized();
      }
    }
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'levelOfDescription':
        $this->form->setDefault('levelOfDescription', $this->context->routing->generate(null, array($this->resource->levelOfDescription, 'module' => 'term')));
        $this->form->setValidator('levelOfDescription', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('levelOfDescription', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'publicationStatus':
        $publicationStatus = $this->resource->getStatus(array('typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID));
        if (isset($publicationStatus))
        {
          $this->form->setDefault('publicationStatus', $publicationStatus->statusId);
        }
        else
        {
          $this->form->setDefault('publicationStatus', sfConfig::get('app_defaultPubStatus'));
        }

        $this->form->setValidator('publicationStatus', new sfValidatorString);

        if (isset($this->resource) && QubitAcl::check($this->resource, 'publish') || !isset($this->resurce) && QubitAcl::check($this->parent, 'publish'))
        {
          $choices = array();
          foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::PUBLICATION_STATUS_ID) as $item)
          {
            $choices[$item->id] = $item;
          }

          $this->form->setWidget('publicationStatus', new sfWidgetFormSelect(array('choices' => $choices)));
        }
        else
        {
          $choices = array();
          if (isset($publicationStatus))
          {
            $choices = array($publicationStatus->id => $publicationStatus->status->__toString());
          }
          else
          {
            $status = QubitTerm::getById(sfConfig::get('app_defaultPubStatus'));
            $choices = array($status->id => $status->__toString());
          }

          // Disable widget if user doesn't have "publish" permission
          $this->form->setWidget('publicationStatus', new sfWidgetFormSelect(array('choices' => $choices), array('disabled' => true)));
        }

        break;

      case 'displayStandard':
          $this->form->setDefault('displayStandard', $this->resource->displayStandardId);
          $this->form->setValidator('displayStandard', new sfValidatorString);

          $choices = array();
          $choices[null] = null;
          foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::INFORMATION_OBJECT_TEMPLATE_ID) as $item)
          {
            $choices[$item->id] = $item;
          }

          $this->form->setWidget('displayStandard', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'displayStandardUpdateDescendants':
        $this->form->setValidator('displayStandardUpdateDescendants', new sfValidatorBoolean);
        $this->form->setWidget('displayStandardUpdateDescendants', new sfWidgetFormInputCheckbox);

        break;

      case 'repository':
        $this->form->setDefault('repository', $this->context->routing->generate(null, array($this->resource->repository, 'module' => 'repository')));
        $this->form->setValidator('repository', new sfValidatorString);

        $choices = array();
        if (isset($this->resource->repository))
        {
          $choices[$this->context->routing->generate(null, array($this->resource->repository, 'module' => 'repository'))] = $this->resource->repository;
        }

        $this->form->setWidget('repository', new sfWidgetFormSelect(array('choices' => $choices)));

        if (isset($this->getRoute()->resource))
        {
          $this->repoAcParams = array('module' => 'repository', 'action' => 'autocomplete', 'aclAction' => 'update');
        }
        else
        {
          $this->repoAcParams = array('module' => 'repository', 'action' => 'autocomplete', 'aclAction' => 'create');
        }

        break;

      case 'accessConditions':
      case 'accruals':
      case 'acquisition':
      case 'archivalHistory':
      case 'arrangement':
      case 'extentAndMedium':
      case 'findingAids':
      case 'locationOfCopies':
      case 'locationOfOriginals':
      case 'physicalCharacteristics':
      case 'relatedUnitsOfDescription':
      case 'reproductionConditions':
      case 'revisionHistory':
      case 'rules':
      case 'scopeAndContent':
      case 'sources':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);

        break;

      case 'descriptionIdentifier':
      case 'identifier':
      case 'institutionResponsibleIdentifier':
      case 'title':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

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

      case 'nameAccessPoints':
      case 'relatedMaterialDescriptions':
        $criteria = new Criteria;
        $criteria->add(QubitRelation::SUBJECT_ID, $this->resource->id);

        $value = $choices = array();
        switch ($name)
        {
          case 'nameAccessPoints':
            $criteria->add(QubitRelation::TYPE_ID, QubitTerm::NAME_ACCESS_POINT_ID);

            foreach ($this->nameAccessPoints = QubitRelation::get($criteria) as $item)
            {
              $choices[$value[] = $this->context->routing->generate(null, array($item->object, 'module' => 'actor'))] = $item->object;
            }

            break;

          case 'relatedMaterialDescriptions':
            $criteria->add(QubitRelation::TYPE_ID, QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID);

            foreach ($this->relatedMaterialDescriptions = QubitRelation::get($criteria) as $item)
            {
              $choices[$value[] = $this->context->routing->generate(null, array($item->object, 'module' => 'informationobject'))] = $item->object;
            }

            break;
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
      case 'levelOfDescription':
      case 'parent':
      case 'repository':
        unset($this->resource[$field->getName()]);

        $value = $this->form->getValue($field->getName());
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->resource[$field->getName()] = $params['_sf_route']->resource;
        }

        break;

      case 'subjectAccessPoints':
      case 'placeAccessPoints':
        $value = $filtered = array();
        foreach ($this->form->getValue($field->getName()) as $item)
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item));
          $resource = $params['_sf_route']->resource;
          $value[$resource->id] = $filtered[$resource->id] = $resource;
        }

        foreach ($this[$field->getName()] as $item)
        {
          if (isset($value[$item->term->id]))
          {
            unset($filtered[$item->term->id]);
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

      case 'nameAccessPoints':
      case 'relatedMaterialDescriptions':
        $value = $filtered = array();
        foreach ($this->form->getValue($field->getName()) as $item)
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item));
          $resource = $params['_sf_route']->resource;
          $value[$resource->id] = $filtered[$resource->id] = $resource;
        }

        foreach ($this->{$field->getName()} as $item)
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
          $relation->object = $item;

          switch ($field->getName())
          {
            case 'nameAccessPoints':
              $relation->typeId = QubitTerm::NAME_ACCESS_POINT_ID;

              break;

            case 'relatedMaterialDescriptions':
              $relation->typeId = QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID;

              break;
          }

          $this->resource->relationsRelatedBysubjectId[] = $relation;
        }

        break;

      case 'displayStandard':

        // Use null when the user wants to inherit the global setting
        if (null === $displayStandardId = $this->form->getValue('displayStandard'))
        {
          $this->resource->displayStandardId = null;

          break;
        }

        // If this is a new record, assign the standard to the object
        if (null === $this->resource->id)
        {
          $this->resource->displayStandardId = $displayStandardId;

          break;
        }

        $selectCriteria = new Criteria;
        if (true === $this->form->getValue('displayStandardUpdateDescendants'))
        {
          $selectCriteria->add(QubitInformationObject::LFT, $this->resource->lft, Criteria::GREATER_EQUAL);
          $selectCriteria->add(QubitInformationObject::RGT, $this->resource->rgt, Criteria::LESS_EQUAL);
        }
        else
        {
          $selectCriteria->add(QubitInformationObject::ID, $this->resource->id);
        }

        $updateCriteria = new Criteria;
        $updateCriteria->add(QubitInformationObject::DISPLAY_STANDARD_ID, $displayStandardId);

        BasePeer::doUpdate(
          $selectCriteria,
          $updateCriteria,
          Propel::getConnection(QubitObject::DATABASE_NAME));

        break;

      default:

        return parent::processField($field);
    }
  }

  protected function processForm()
  {
    // If object is being duplicated
    if (isset($this->request->sourceId))
    {
      $sourceInformationObject = QubitInformationObject::getById($this->request->sourceId);

      // Duplicate physical object relations
      foreach ($sourceInformationObject->getPhysicalObjects() as $physicalObject)
      {
        $this->resource->addPhysicalObject($physicalObject);
      }

      // Duplicate notes
      foreach ($sourceInformationObject->notes as $sourceNote)
      {
        if (!isset($this->request->delete_notes[$sourceNote->id]))
        {
          $note = new QubitNote;
          $note->content = $sourceNote->content;
          $note->typeId = $sourceNote->type->id;
          $note->userId = $this->context->user->getAttribute('user_id');

          $this->resource->notes[] = $note;
        }
      }

      foreach (QubitRelation::getRelationsBySubjectId($sourceInformationObject->id, array('typeId' => QubitTerm::RIGHT_ID)) as $item)
      {
        $sourceRight = $item->object;

        if (false === array_search($this->context->routing->generate(null, array($sourceRight, 'module' => 'right')), (array)$this->request->deleteRights))
        {
          $right = new QubitRights;
          $right->act = $sourceRight->act;
          $right->startDate = $sourceRight->startDate;
          $right->endDate = $sourceRight->endDate;
          $right->basis = $sourceRight->basis;
          $right->restriction = $sourceRight->restriction;
          $right->copyrightStatus = $sourceRight->copyrightStatus;
          $right->copyrightStatusDate = $sourceRight->copyrightStatusDate;
          $right->copyrightJurisdiction = $sourceRight->copyrightJurisdiction;
          $right->statuteNote = $sourceRight->statuteNote;

          // Right holder
          if (isset($sourceRight->rightsHolder))
          {
            $right->rightsHolder = $sourceRight->rightsHolder;
          }

          // I18n
          $right->rightsNote = $sourceRight->rightsNote;
          $right->copyrightNote = $sourceRight->copyrightNote;
          $right->licenseIdentifier = $sourceRight->licenseIdentifier;
          $right->licenseTerms = $sourceRight->licenseTerms;
          $right->licenseNote = $sourceRight->licenseNote;
          $right->statuteJurisdiction = $sourceRight->statuteJurisdiction;
          $right->statuteCitation = $sourceRight->statuteCitation;
          $right->statuteDeterminationDate = $sourceRight->statuteDeterminationDate;

          foreach ($sourceRight->rightsI18ns as $sourceRightI18n)
          {
            if ($this->context->user->getCulture() == $sourceRightI18n->culture)
            {
              continue;
            }

            $rightI18n = new QubitRightsI18n;
            $rightI18n->rightNote = $sourceRightI18n->rightNote;
            $rightI18n->copyrightNote = $sourceRightI18n->copyrightNote;
            $rightI18n->licenseIdentifier = $sourceRightI18n->licenseIdentifier;
            $rightI18n->licenseTerms = $sourceRightI18n->licenseTerms;
            $rightI18n->licenseNote = $sourceRightI18n->licenseNote;
            $rightI18n->statuteJurisdiction = $sourceRightI18n->statuteJurisdiction;
            $rightI18n->statuteCitation = $sourceRightI18n->statuteCitation;
            $rightI18n->statuteNote = $sourceRightI18n->statuteNote;
            $rightI18n->culture = $sourceRightI18n->culture;

            $right->rightsI18ns[] = $rightI18n;
          }

          $right->save();

          $relation = new QubitRelation;
          $relation->object = $right;
          $relation->typeId = QubitTerm::RIGHT_ID;

          $this->resource->relationsRelatedBysubjectId[] = $relation;
        }
      }

      if ('sfIsadPlugin' != $this->request->module)
      {
        foreach ($sourceInformationObject->events as $sourceEvent)
        {
          if (false === array_search($this->context->routing->generate(null, array($sourceEvent, 'module' => 'event')), (array)$this->request->deleteEvents))
          {
            $event = new QubitEvent;
            $event->actorId = $sourceEvent->actorId;
            $event->typeId = $sourceEvent->typeId;
            $event->startDate = $sourceEvent->startDate;
            $event->endDate = $sourceEvent->endDate;
            $event->sourceCulture = $sourceEvent->sourceCulture;

            // I18n
            $event->name = $sourceEvent->name;
            $event->description = $sourceEvent->description;
            $event->date = $sourceEvent->date;

            foreach ($sourceEvent->eventI18ns as $sourceEventI18n)
            {
              if ($this->context->user->getCulture() == $sourceEventI18n->culture)
              {
                continue;
              }

              $eventI18n = new QubitEventI18n;
              $eventI18n->name = $sourceEventI18n->name;
              $eventI18n->description = $sourceEventI18n->description;
              $eventI18n->date = $sourceEventI18n->date;
              $eventI18n->culture = $sourceEventI18n->culture;

              $event->eventI18ns[] = $eventI18n;
            }

            // Place
            if (null !== $place = QubitObjectTermRelation::getOneByObjectId($sourceEvent->id))
            {
              $termRelation = new QubitObjectTermRelation;
              $termRelation->term = $place->term;

              $event->objectTermRelationsRelatedByobjectId[] = $termRelation;
            }

            $this->resource->events[] = $event;
          }
        }
      }
    }

    parent::processForm();

    $this->deleteNotes();
    $this->updateChildLevels();
    $this->updateStatus(); // Must come after updateChildLevels()
  }

  public function execute($request)
  {
    parent::execute($request);

    if ($request->hasParameter('csvimport'))
    {
      // if a parent ID is set, use that for parenting
      $parentId = $request->getParameter('parent', null);

      if (!empty($parentId))
      {
        $request->getParameterHolder()->remove('parent');
      }

      // make sure we don't pass the import ID
      $request->getParameterHolder()->remove('id');

      $this->form->bind($request->getParameterHolder()->getAll());
      if ($this->form->isValid())
      {
        $this->processForm();

        if (!empty($parentId))
        {
          $this->resource->parent = QubitInformationObject::getById($parentId);
        }
        else{
          $this->resource->parent = QubitInformationObject::getById(QubitInformationObject::ROOT_ID);
        }

        $this->resource->save();

        return; // don't bother adding assets etc. for output
      }
    }
    elseif ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->processForm();

        $this->resource->save();

        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
    }

    QubitDescription::addAssets($this->response);
  }

  /**
   * Delete related notes marked for deletion.
   *
   * @param sfRequest request object
   */
  protected function deleteNotes()
  {
    if (false == isset($this->request->sourceId) && is_array($deleteNotes = $this->request->delete_notes) && count($deleteNotes))
    {
      foreach ($deleteNotes as $noteId => $doDelete)
      {
        if ($doDelete == 'delete' && !is_null($deleteNote = QubitNote::getById($noteId)))
        {
          $deleteNote->delete();
        }
      }
    }
  }

  protected function updateChildLevels()
  {
    if (is_array($updateChildLevels = $this->request->updateChildLevels) && count($updateChildLevels))
    {
      foreach ($updateChildLevels as $item)
      {
        $childLevel = new QubitInformationObject;
        $childLevel->identifier = $item['identifier'];
        $childLevel->title = $item['title'];

        if (null != ($pubStatus = $this->resource->getPublicationStatus()))
        {
          $childLevel->setPublicationStatus($pubStatus->statusId);
        }

        if (0 < strlen($item['levelOfDescription']) && (null !== QubitTerm::getById($item['levelOfDescription'])))
        {
          $childLevel->levelOfDescriptionId = $item['levelOfDescription'];
        }

        if (true === $this->form->getValue('displayStandardUpdateDescendants')
            && null !== $displayStandardId = $this->form->getValue('displayStandard'))
        {
          $childLevel->displayStandardId = $displayStandardId;
        }

        if (0 < strlen($item['levelOfDescription'])
            || 0 < strlen($item['identifier'])
            || 0 < strlen($item['title']))
        {
          $this->resource->informationObjectsRelatedByparentId[] = $childLevel;
        }
      }
    }
  }

  protected function updateStatus()
  {
    if (!QubitAcl::check($this->resource, 'publish'))
    {
      // if the user does not have 'publish' permission, use default publication
      // status setting
      $pubStatusId = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);
    }
    else
    {
      $pubStatusId = $this->form->getValue('publicationStatus');
    }

    // Only update publicationStatus if its value has changed because it
    // triggers a resource-intensive update of all its descendants
    $oldStatus = $this->resource->getStatus(array('typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID));
    if (!isset($oldStatus) && isset($pubStatusId) || $pubStatusId !== $oldStatus->statusId)
    {
      $this->resource->setPublicationStatus($pubStatusId);

      // Set pub status for child levels
      foreach ($this->resource->informationObjectsRelatedByparentId as $child)
      {
        $child->setPublicationStatus($pubStatusId);
      }

      // Update pub status of descendants
      foreach ($this->resource->descendants as $descendant)
      {
        if (null === $descendantPubStatus = $descendant->getStatus(array('typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID)))
        {
          $descendantPubStatus = new QubitStatus;
          $descendantPubStatus->typeId = QubitTerm::STATUS_TYPE_PUBLICATION_ID;
          $descendantPubStatus->objectId = $descendant->id;
        }

        if ($pubStatusId != $descendantPubStatus->statusId)
        {
          $descendantPubStatus->statusId = $pubStatusId;
          $descendantPubStatus->save();
        }
      }
    }
  }
}
