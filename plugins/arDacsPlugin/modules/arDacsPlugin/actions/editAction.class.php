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

class arDacsPluginEditAction extends InformationObjectEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'accessConditions',
      'accruals',
      'acquisition',
      'appraisal',
      'archivalHistory',
      'arrangement',
      'creators',
      'extentAndMedium',
      'findingAids',
      'identifier',
      'language',
      'languageNotes',
      'levelOfDescription',
      'locationOfCopies',
      'locationOfOriginals',
      'nameAccessPoints',
      'genreAccessPoints',
      'physicalCharacteristics',
      'placeAccessPoints',
      'relatedMaterialDescriptions',
      'relatedUnitsOfDescription',
      'repository',
      'reproductionConditions',
      'rules',
      'scopeAndContent',
      'script',
      'sources',
      'subjectAccessPoints',
      'displayStandard',
      'displayStandardUpdateDescendants',
      'title',

      // DACS, see arDacsPlugin
      'technicalAccess');

  protected function earlyExecute()
  {
    parent::earlyExecute();

    $this->dacs = new arDacsPlugin($this->resource);

    $title = $this->context->i18n->__('Add new archival description');
    if (isset($this->getRoute()->resource))
    {
      if (1 > strlen($title = $this->resource->__toString()))
      {
        $title = $this->context->i18n->__('Untitled');
      }

      $title = $this->context->i18n->__('Edit %1%', array('%1%' => $title));
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    $this->alternativeIdentifiersComponent = new InformationObjectAlternativeIdentifiersComponent($this->context, 'informationobject', 'alternativeIdentifiers');
    $this->alternativeIdentifiersComponent->resource = $this->resource;
    $this->alternativeIdentifiersComponent->execute($this->request);

    $this->eventComponent = new sfIsadPluginEventComponent($this->context, 'sfIsadPlugin', 'event');
    $this->eventComponent->resource = $this->resource;
    $this->eventComponent->execute($this->request);

    $this->publicationNotesComponent = new InformationObjectNotesComponent($this->context, 'informationobject', 'notes');
    $this->publicationNotesComponent->resource = $this->resource;
    $this->publicationNotesComponent->execute($this->request, $options = array('type' => 'dacsPublicationNotes'));

    $this->notesComponent = new InformationObjectNotesComponent($this->context, 'informationobject', 'notes');
    $this->notesComponent->resource = $this->resource;
    $this->notesComponent->execute($this->request, $options = array('type' => 'dacsNotes'));

    $this->specializedNotesComponent = new InformationObjectNotesComponent($this->context, 'informationobject', 'notes');
    $this->specializedNotesComponent->resource = $this->resource;
    $this->specializedNotesComponent->execute($this->request, $options = array('type' => 'dacsSpecializedNotes'));

    $this->archivistsNotesComponent = new InformationObjectNotesComponent($this->context, 'informationobject', 'notes');
    $this->archivistsNotesComponent->resource = $this->resource;
    $this->archivistsNotesComponent->execute($this->request, $options = array('type' => 'dacsArchivistsNotes'));
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'creators':
        $criteria = new Criteria;
        $criteria->add(QubitEvent::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitEvent::ACTOR_ID, null, Criteria::ISNOTNULL);
        $criteria->add(QubitEvent::TYPE_ID, QubitTerm::CREATION_ID);

        $value = $choices = array();
        foreach ($this->events = QubitEvent::get($criteria) as $item)
        {
          $choices[$value[] = $this->context->routing->generate(null, array($item->actor, 'module' => 'actor'))] = $item->actor;
        }

        $this->form->setDefault('creators', $value);
        $this->form->setValidator('creators', new sfValidatorPass);
        $this->form->setWidget('creators', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

        break;

      case 'appraisal':
        $this->form->setDefault('appraisal', $this->resource['appraisal']);
        $this->form->setValidator('appraisal', new sfValidatorString);
        $this->form->setWidget('appraisal', new sfWidgetFormTextarea);

        break;

      case 'languageNotes':
      case 'technicalAccess':
        $this->form->setDefault($name, $this->dacs[$name]);
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
      case 'creators':
        $value = $filtered = array();
        foreach ($this->form->getValue('creators') as $item)
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item));
          $resource = $params['_sf_route']->resource;
          $value[$resource->id] = $filtered[$resource->id] = $resource;
        }

        foreach ($this->events as $item)
        {
          if (isset($value[$item->actor->id]))
          {
            unset($filtered[$item->actor->id]);
          }
          else if (!isset($this->request->sourceId))
          {
            $item->delete();
          }
        }

        foreach ($filtered as $item)
        {
          $event = new QubitEvent;
          $event->actor = $item;
          $event->typeId = QubitTerm::CREATION_ID;

          $this->resource->eventsRelatedByobjectId[] = $event;
        }

        break;

      case 'languageNotes':
      case 'technicalAccess':
        $this->dacs[$field->getName()] = $this->form->getValue($field->getName());

        break;

      default:

        return parent::processField($field);
    }
  }

  protected function processForm()
  {
    $this->resource->sourceStandard = 'DACS 2nd edition';

    $this->alternativeIdentifiersComponent->processForm();

    $this->eventComponent->processForm();

    $this->publicationNotesComponent->processForm();

    $this->notesComponent->processForm();

    $this->archivistsNotesComponent->processForm();

    $this->specializedNotesComponent->processForm();

    return parent::processForm();
  }
}
