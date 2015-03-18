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
 * Repository - editIsdiah
 *
 * @package    AccesstoMemory
 * @subpackage Actor - initialize an editIDIAH template for updating a repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 */

class sfIsdiahPluginEditAction extends RepositoryEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'identifier',
      'authorizedFormOfName',
      'parallelName',
      'otherName',
      'type',
      'history',
      'geoculturalContext',
      'mandates',
      'internalStructures',
      'collectingPolicies',
      'buildings',
      'holdings',
      'findingAids',
      'openingTimes',
      'accessConditions',
      'disabledAccess',
      'researchServices',
      'reproductionServices',
      'publicFacilities',
      'descIdentifier',
      'descInstitutionIdentifier',
      'descRules',
      'descStatus',
      'descDetail',
      'descRevisionHistory',
      'language',
      'script',
      'descSources',
      'maintenanceNotes',
      'geographicSubregion',
      'thematicArea');

  protected function earlyExecute()
  {
    parent::earlyExecute();

    $this->isdiah = new sfIsdiahPlugin($this->resource);

    $title = $this->context->i18n->__('Add new archival institution');
    if (isset($this->getRoute()->resource))
    {
      if (1 > strlen($title = $this->resource->__toString()))
      {
        $title = $this->context->i18n->__('Untitled');
      }

      $title = $this->context->i18n->__('Edit %1%', array('%1%' => $title));
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    $this->contactInformationEditComponent->form->getWidgetSchema()->contactPerson->setHelp($this->context->i18n->__('See ISDIAH 5.2 for contact area information and examples.'));
    $this->contactInformationEditComponent->form->getWidgetSchema()->latitude->setHelp($this->context->i18n->__('Enter the digital latitude of the institution\'s physical location (rather than degrees, minutes and seconds), e.g. 49.459044'));
    $this->contactInformationEditComponent->form->getWidgetSchema()->longitude->setHelp($this->context->i18n->__('Enter the digital longitude of the institution\'s physical location (rather than degrees, minutes and seconds), e.g. -104.296984'));
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'maintenanceNotes':
        $this->form->setDefault('maintenanceNotes', $this->isdiah->maintenanceNotes);
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
        $this->isdiah->maintenanceNotes = $this->form->getValue('maintenanceNotes');

        break;

      default:

        return parent::processField($field);
    }
  }
}
