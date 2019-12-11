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
 * Information Object - editIsad
 *
 * @package    AccesstoMemory
 * @subpackage informationObject - initialize an editIsad template for updating an information object
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jesús García Crespo <correo@sevein.com>
 */
class sfIsadPluginEditAction extends InformationObjectEditAction
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
      'descriptionDetail',
      'descriptionIdentifier',
      'extentAndMedium',
      'findingAids',
      'identifier',
      'institutionResponsibleIdentifier',
      'language',
      'languageNotes',
      'languageOfDescription',
      'levelOfDescription',
      'locationOfCopies',
      'locationOfOriginals',
      'nameAccessPoints',
      'genreAccessPoints',
      'physicalCharacteristics',
      'placeAccessPoints',
      'relatedUnitsOfDescription',
      'relatedMaterialDescriptions',
      'repository',
      'reproductionConditions',
      'revisionHistory',
      'rules',
      'scopeAndContent',
      'scriptOfDescription',
      'script',
      'sources',
      'subjectAccessPoints',
      'descriptionStatus',
      'displayStandard',
      'displayStandardUpdateDescendants',
      'title');

  protected function earlyExecute()
  {
    parent::earlyExecute();

    $this->isad = new sfIsadPlugin($this->resource);

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
    $this->publicationNotesComponent->execute($this->request, $options = array('type' => 'isadPublicationNotes'));

    $this->notesComponent = new InformationObjectNotesComponent($this->context, 'informationobject', 'notes');
    $this->notesComponent->resource = $this->resource;
    $this->notesComponent->execute($this->request, $options = array('type' => 'isadNotes'));

    $this->archivistsNotesComponent = new InformationObjectNotesComponent($this->context, 'informationobject', 'notes');
    $this->archivistsNotesComponent->resource = $this->resource;
    $this->archivistsNotesComponent->execute($this->request, $options = array('type' => 'isadArchivistsNotes'));
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

        $this->form->setDefault('languageNotes', $this->isad['languageNotes']);
        $this->form->setValidator('languageNotes', new sfValidatorString);
        $this->form->setWidget('languageNotes', new sfWidgetFormTextarea);

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

        foreach ($this->events as $item)
        {
          if (isset($value[$item->actor->id]))
          {
            unset($filtered[$item->actor->id]);
          }
          else if (!isset($this->request->sourceId))
          {
            // Will be indexed when description is saved
            $item->indexOnSave = false;

            // Only delete event if it has no associated date and start/end date
            if (null === $item->date && null === $item->startDate && null === $item->endDate)
            {
              $item->delete();
            }
            else
            {
              // Handle specially as data wasn't created using ISAD template
              $item->actor = null;
              $item->save();
            }
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

        $this->isad['languageNotes'] = $this->form->getValue('languageNotes');

        break;

      default:

        return parent::processField($field);
    }
  }

  protected function processForm()
  {
    $this->resource->sourceStandard = 'ISAD(G) 2nd edition';

    $this->alternativeIdentifiersComponent->processForm();

    $this->eventComponent->processForm();

    $this->publicationNotesComponent->processForm();

    $this->notesComponent->processForm();

    $this->archivistsNotesComponent->processForm();

    return parent::processForm();
  }
}
