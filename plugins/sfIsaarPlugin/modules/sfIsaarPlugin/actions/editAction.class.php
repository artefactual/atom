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
 * Actor - editIsaar
 *
 * @package    AccesstoMemory
 * @subpackage Actor - initialize an editISAAR template for updating an actor
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class sfIsaarPluginEditAction extends ActorEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'authorizedFormOfName',
      'corporateBodyIdentifiers',
      'datesOfExistence',
      'descriptionDetail',
      'descriptionIdentifier',
      'descriptionStatus',
      'entityType',
      'functions',
      'generalContext',
      'history',
      'institutionResponsibleIdentifier',
      'internalStructures',
      'language',
      'legalStatus',
      'maintainingRepository',
      'maintenanceNotes',
      'mandates',
      'otherName',
      'parallelName',
      'places',
      'revisionHistory',
      'rules',
      'script',
      'sources',
      'standardizedName');

  protected function earlyExecute()
  {
    parent::earlyExecute();

    $this->isaar = new sfIsaarPlugin($this->resource);

    $title = $this->context->i18n->__('Add new authority record');
    if (isset($this->getRoute()->resource))
    {
      if (1 > strlen($title = $this->resource))
      {
        $title = $this->context->i18n->__('Untitled');
      }

      $title = $this->context->i18n->__('Edit %1%', array('%1%' => $title));
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    $this->eventComponent = new sfIsaarPluginEventComponent($this->context, 'sfIsaarPlugin', 'event');
    $this->eventComponent->resource = $this->resource;
    $this->eventComponent->execute($this->request);

    $this->relatedAuthorityRecordComponent = new sfIsaarPluginRelatedAuthorityRecordComponent($this->context, 'sfIsaarPlugin', 'relatedAuthorityRecord');
    $this->relatedAuthorityRecordComponent->resource = $this->resource;
    $this->relatedAuthorityRecordComponent->execute($this->request);

    $this->occupationsComponent = new ActorOccupationsComponent($this->context, 'actor', 'occupations');
    $this->occupationsComponent->resource = $this->resource;
    $this->occupationsComponent->execute($this->request);
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'maintenanceNotes':
        $this->form->setDefault('maintenanceNotes', $this->isaar->maintenanceNotes);
        $this->form->setValidator('maintenanceNotes', new sfValidatorString);
        $this->form->setWidget('maintenanceNotes', new sfWidgetFormTextarea);

        break;

      default:

        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'maintenanceNotes':
        $this->isaar->maintenanceNotes = $this->form->getValue('maintenanceNotes');

        break;

      default:

        return parent::processField($field);
    }
  }

  protected function processForm()
  {
    $this->eventComponent->processForm();
    $this->relatedAuthorityRecordComponent->processForm();
    $this->occupationsComponent->processForm();

    return parent::processForm();
  }
}
