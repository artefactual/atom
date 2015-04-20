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

class AccessionRelatedDonorComponent extends RelationEditComponent
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'authorizedFormOfName',
      'type',

      // Must be processed before the contact information fields
      // because sets $this->contactInformation property
      'resource',

      // Contact information
      'city',
      'contactPerson',
      'countryCode',
      'email',
      'postalCode',
      'region',
      'streetAddress',
      'telephone',
      'contactType',
      'website',
      'fax',
      'latitude',
      'longitude',
      'note');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'authorizedFormOfName':
        $this->form->setValidator('authorizedFormOfName', new sfValidatorString);
        $this->form->setWidget('authorizedFormOfName', new sfWidgetFormSelect(array('choices' => array())));

        break;

      case 'type':
        $this->form->setDefault('type', $this->context->routing->generate(null, array(QubitTerm::getById(QubitTerm::DONOR_ID), 'module' => 'term')));
        $this->form->setValidator('type', new sfValidatorString);
        $this->form->setWidget('type', new sfWidgetFormInputHidden);

        break;

      case 'latitude':
      case 'longitude':
        $this->form->setValidator($name, new sfValidatorNumber);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'streetAddress':
      case 'note':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextArea(array(), array('rows' => 2)));

        break;

      case 'countryCode':
        $this->form->setValidator('countryCode', new sfValidatorI18nChoiceCountry);
        $this->form->setWidget('countryCode', new sfWidgetFormI18nChoiceCountry(array('add_empty' => true, 'culture' => $this->context->user->getCulture())));

        break;

      case 'city':
      case 'contactPerson':
      case 'email':
      case 'postalCode':
      case 'region':
      case 'telephone':
      case 'contactType':
      case 'fax':
      case 'website':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

      default:

        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      // Override RelationEditComponent
      case 'resource':
        unset($this->relation->object);

        $value = $this->form->getValue('resource');
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->relation->object = $params['_sf_route']->resource;

          if (null === $this->contactInformation = $this->relation->object->getPrimaryContact())
          {
            $this->contactInformation = new QubitContactInformation;
          }
        }

        break;

      case 'city':
      case 'contactPerson':
      case 'countryCode':
      case 'email':
      case 'postalCode':
      case 'region':
      case 'streetAddress':
      case 'telephone':
      case 'contactType':
      case 'latitude':
      case 'longitude':
      case 'note':
      case 'fax':
      case 'website':
        $this->contactInformation[$field->getName()] = $this->form->getValue($field->getName());

        break;

      default:
        parent::processField($field);
    }
  }

  public function processForm()
  {
    parent::processForm();

    if (isset($this->contactInformation->city) ||
        isset($this->contactInformation->contactPerson) ||
        isset($this->contactInformation->email) ||
        isset($this->contactInformation->postalCode) ||
        isset($this->contactInformation->region) ||
        isset($this->contactInformation->streetAddress) ||
        isset($this->contactInformation->telephone) ||
        isset($this->contactInformation->countryCode) ||
        isset($this->contactInformation->contactType) ||
        isset($this->contactInformation->latitude) ||
        isset($this->contactInformation->longitude) ||
        isset($this->contactInformation->note) ||
        isset($this->contactInformation->fax) ||
        isset($this->contactInformation->website))
    {
      $this->contactInformation->actor = $this->relation->object;
      $this->contactInformation->save();
      $this->contactInformation->makePrimaryContact();
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    $this->form->getWidgetSchema()->setNameFormat('relatedDonor[%s]');

    $this->relatedDonorRecord = QubitRelation::getRelationsBySubjectId($this->resource->id, array('typeId' => QubitTerm::DONOR_ID));
  }
}
