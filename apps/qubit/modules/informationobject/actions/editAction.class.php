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

    // Publication status defaults to draft
    $this->publicationStatusId = QubitTerm::PUBLICATION_STATUS_DRAFT_ID;
    $this->unpublishing = false;

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

      $publicationStatus = $this->resource->getStatus(array('typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID));
      if (QubitAcl::check($this->resource, 'publish') && !isset($publicationStatus))
      {
        // Use app. default pub. status if the user can publish and
        // the current pub. status is missing (rare case)
        $this->publicationStatusId = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);
      }
      else if (QubitAcl::check($this->resource, 'publish'))
      {
        // Use current pub. status if the user can publish
        $this->publicationStatusId = $publicationStatus->statusId;
      }
      else if (isset($publicationStatus) && $publicationStatus->statusId == QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)
      {
        // Take note to show a warning if the user can't publish but the current pub. status
        // is published while editing, as it will change the pub. status to draft
        $this->unpublishing = true;
      }
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

      // Use app. default pub. status if the user can publish
      if (QubitAcl::check($this->resource, 'publish'))
      {
        $this->publicationStatusId = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);
      }
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

      if (isset($getParams['repository']))
      {
        $this->resource->repository = QubitRepository::getById($this->request->repository);
        $this->form->setDefault('repository', $this->context->routing->generate(null, array($this->resource->repository, 'module' => 'repository')));
      }

      // Check authorization
      if (!QubitAcl::check($this->parent, 'create'))
      {
        QubitAcl::forwardUnauthorized();
      }

      // Use app. default pub. status if the user can publish
      if (QubitAcl::check($this->parent, 'publish'))
      {
        $this->publicationStatusId = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);
      }

      // If creating new description and identifier mask is set to on, auto-generate next identifier.
      $this->handleIdentifierFromMask();
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

      case 'genreAccessPoints':
      case 'subjectAccessPoints':
      case 'placeAccessPoints':
        $criteria = new Criteria;
        $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->resource->id);
        $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
        switch ($name)
        {
          case 'genreAccessPoints':
            $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::GENRE_ID);

            break;

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

            // Add also relations where it's the object
            $criteria = new Criteria;
            $criteria->add(QubitRelation::OBJECT_ID, $this->resource->id);
            $criteria->add(QubitRelation::TYPE_ID, QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID);

            foreach (QubitRelation::get($criteria) as $item)
            {
              $this->relatedMaterialDescriptions[] = $item;
              $choices[$value[] = $this->context->routing->generate(null, array($item->subject, 'module' => 'informationobject'))] = $item->subject;
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
      case 'title':
        // Avoid duplicates (used in autocomplete.js)
        if (filter_var($this->request->getPostParameter('linkExisting'), FILTER_VALIDATE_BOOLEAN))
        {
          $criteria = new Criteria;
          $criteria->addJoin(QubitInformationObject::ID, QubitInformationObjectI18n::ID);
          $criteria->add(QubitInformationObjectI18n::CULTURE, $this->context->user->getCulture());
          $criteria->add(QubitInformationObjectI18n::TITLE, $this->form->getValue('title'));
          if (null !== $io = QubitInformationObject::getOne($criteria))
          {
            $this->redirect(array($io, 'module' => 'informationobject'));

            return;
          }
        }

        return parent::processField($field);

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

      case 'genreAccessPoints':
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

        if (is_array($formItems = $this->form->getValue($field->getName())))
        {
          foreach ($formItems as $item)
          {
            $params = $this->context->routing->parse(Qubit::pathInfo($item));
            $resource = $params['_sf_route']->resource;
            $value[$resource->id] = $filtered[$resource->id] = $resource;
          }
        }

        foreach ($this->{$field->getName()} as $item)
        {
          if (isset($value[$item->objectId]))
          {
            unset($filtered[$item->objectId]);
          }
          else
          {
            $item->indexSubjectOnDelete = false;
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
              // Don't allow self-relations
              if (!isset($this->resource->id) || ($this->resource->id != $item->id))
              {
                $relation->typeId = QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID;
              }
              else
              {
                $message = $this->context->i18n->__('Self-relation ignored.');
                $this->getUser()->setFlash('notice', $message);
              }

              break;
          }

          // Don't create if no relation type has been set
          if (isset($relation->typeId))
          {
            $this->resource->relationsRelatedBysubjectId[] = $relation;
          }
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

      $this->duplicateNotes($sourceInformationObject);
      $this->duplicateAlternativeIdentifiers($sourceInformationObject);

      foreach (QubitRelation::getRelationsBySubjectId($sourceInformationObject->id, array('typeId' => QubitTerm::RIGHT_ID)) as $item)
      {
        $sourceRights = $item->object;

        $newRights = $sourceRights->copy();

        $relation = new QubitRelation;
        $relation->object = $newRights;
        $relation->typeId = QubitTerm::RIGHT_ID;

        $this->resource->relationsRelatedBysubjectId[] = $relation;
      }

      if ('sfIsadPlugin' != $this->request->module)
      {
        foreach ($sourceInformationObject->eventsRelatedByobjectId as $sourceEvent)
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

            $this->resource->eventsRelatedByobjectId[] = $event;
          }
        }
      }
    }

    parent::processForm();

    // Set publication status
    $this->resource->setPublicationStatus($this->publicationStatusId);

    // Let user know if the description has been unpublished
    if ($this->unpublishing)
    {
      $i18n = $this->context->i18n;
      $message = $i18n->__('Your edits to this description have been saved and the description has reverted to Draft status. Please ask a user with sufficient permissions to publish the description again to make it publicly visible.');
      $this->getUser()->setFlash('notice', $message);
    }

    $this->deleteNotes();
    $this->updateChildLevels();
    $this->removeDuplicateRepositoryAssociations();
    $this->incrementMaskCounter();
  }

  public function execute($request)
  {
    // Force subclassing
    if ('informationobject' == $this->context->getModuleName() && 'edit' == $this->context->getActionName())
    {
      $this->forward404();
    }

    parent::execute($request);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->processForm();

        $this->resource->save();
        $this->resource->updateXmlExports();

        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
    }

    QubitDescription::addAssets($this->response);
  }

  /**
   * Copy over a source information object's alternative identifiers when duplicating.
   *
   * @param QubitInformationObject $sourceIo  The source information object we're duplicating from.
   */
  private function duplicateAlternativeIdentifiers($sourceIo)
  {
    foreach ($sourceIo->getProperties(null, 'alternativeIdentifiers') as $sourceAltId)
    {
      $altId = new QubitProperty;
      $altId->scope = 'alternativeIdentifiers';
      $altId->name = $sourceAltId->name;
      $altId->sourceCulture = $sourceAltId->sourceCulture;
      $altId->value = $sourceAltId->value;

      $this->resource->propertys[] = $altId;
    }
  }

  /**
   * Copy over a source information object's notes when duplicating.
   *
   * @param QubitInformationObject $sourceIo  The source information object we're duplicating from.
   */
  private function duplicateNotes($sourceIo)
  {
    // Duplicate notes
    foreach ($sourceIo->notes as $sourceNote)
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
  }

  /**
   * If the user selected an existing repository that this record
   * would inherit an association to anyway, don't bother duplicating
   * the association.
   */
  private function removeDuplicateRepositoryAssociations()
  {
    if ($this->resource->canInheritRepository($this->resource->repositoryId))
    {
      $this->resource->repositoryId = null;
    }
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
    $updateChildLevels = $this->request->updateChildLevels;
    if (!is_array($updateChildLevels) || 0 == count($updateChildLevels))
    {
      return;
    }

    $dsUpdateDescendants = $this->form->getValue('displayStandardUpdateDescendants');
    $dsId = $this->form->getValue('displayStandard');
    if (true === $dsUpdateDescendants && null !== $dsId)
    {
      $displayStandardId = $dsId;
    }
    else if (isset($this->resource->id) && isset($this->resource->displayStandardId))
    {
      $displayStandardId = $this->resource->displayStandardId;
    }

    // Use app. default pub. status for the children if the user can publish
    $childrenPublicationStatusId = $this->publicationStatusId;
    if (QubitAcl::check($this->resource, 'publish'))
    {
      $childrenPublicationStatusId = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);
    }

    foreach ($updateChildLevels as $item)
    {
      $childLevel = new QubitInformationObject;
      $childLevel->identifier = $item['identifier'];
      $childLevel->title = $item['title'];
      $childLevel->setPublicationStatus($childrenPublicationStatusId);

      if (0 < strlen($item['levelOfDescription']) && (null !== QubitTerm::getById($item['levelOfDescription'])))
      {
        $childLevel->levelOfDescriptionId = $item['levelOfDescription'];
      }

      if (!empty($displayStandardId))
      {
        $childLevel->displayStandardId = $displayStandardId;
      }

      if (0 < strlen($item['date']))
      {
        $creationEvent = new QubitEvent;
        $creationEvent->typeId = QubitTerm::CREATION_ID;
        $creationEvent->date = $item['date'];

        if (0 < strlen($item['startDate']))
        {
          if (preg_match('/^\d{8}\z/', trim($item['startDate']), $matches))
          {
            $creationEvent->startDate = substr($matches[0], 0, 4).'-'.substr($matches[0], 4, 2).'-'.substr($matches[0], 6, 2);
          }
          else
          {
            $creationEvent->startDate = $item['startDate'];
          }
        }

        if (0 < strlen($item['endDate']))
        {
          if (preg_match('/^\d{8}\z/', trim($item['endDate']), $matches))
          {
            $creationEvent->endDate = substr($matches[0], 0, 4).'-'.substr($matches[0], 4, 2).'-'.substr($matches[0], 6, 2);
          }
          else
          {
            $creationEvent->endDate = $item['endDate'];
          }
        }

        $childLevel->eventsRelatedByobjectId[] = $creationEvent;
      }

      if (0 < strlen($item['levelOfDescription'])
          || 0 < strlen($item['identifier'])
          || 0 < strlen($item['title']))
      {
        $this->resource->informationObjectsRelatedByparentId[] = $childLevel;
      }
    }
  }

  /**
   * If identifier mask is enabled, set our new info obj to an identifier generated
   * from the mask.
   */
  private function handleIdentifierFromMask()
  {
    // Pass if we're using mask or not to template, fill in identifier with generated
    // identifier if so.
    if ($this->mask = sfConfig::get('app_identifier_mask_enabled', 0))
    {
      $this->resource->identifier = QubitInformationObject::generateIdentiferFromMask();
    }
  }

  /**
   * If the user is using an identifier generated from the mask, increment the mask counter.
   */
  private function incrementMaskCounter()
  {
    if (!filter_var($this->request->getPostParameter('usingMask'), FILTER_VALIDATE_BOOLEAN))
    {
      return;
    }

    $counter = QubitInformationObject::getIdentifierCounter();
    $counterValue = $counter->getValue(array('sourceCulture' => true));
    $counter->setValue($counterValue + 1, array('sourceCulture' => true));
    $counter->save();
  }
}
