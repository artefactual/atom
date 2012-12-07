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

class ContactInformationEditComponent extends sfComponent
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'city',
      'contactPerson',
      'contactType',
      'countryCode',
      'email',
      'fax',
      'latitude',
      'longitude',
      'note',
      'region',
      'postalCode',
      'primaryContact',
      'telephone',
      'streetAddress',
      'website');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'countryCode':
        $this->form->setValidator('countryCode', new sfValidatorI18nChoiceCountry);
        $this->form->setWidget('countryCode', new sfWidgetFormI18nChoiceCountry(array('add_empty' => true, 'culture' => $this->context->user->getCulture())));

        break;

      case 'primaryContact':
        $this->form->setDefault('primaryContact', false);
        $this->form->setValidator('primaryContact', new sfValidatorBoolean);
        $this->form->setWidget('primaryContact', new sfWidgetFormInputCheckbox);

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

      default:
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      default:
        $this->contactInformation[$field->getName()] = $this->form->getValue($field->getName());
    }
  }

  public function processForm()
  {
    // HACK For now, parameter name and action name are the same. Should
    // really be configurable, ideally by interpreting
    // $form->getWidgetSchema()->getNameFormat()?
    $params = array($this->request[$this->actionName]);
    if (isset($this->request["{$this->actionName}s"]))
    {
      // If dialog JavaScript did it's work, then use array of parameters
      $params = $this->request["{$this->actionName}s"];
    }

    foreach ($params as $item)
    {
      // Continue only if user typed something
      foreach ($item as $value)
      {
        if (0 < strlen($value))
        {
          break;
        }
      }

      if (1 > strlen($value))
      {
        continue;
      }

      $this->form->bind($item);
      if ($this->form->isValid())
      {
        if (isset($item['id']))
        {
          $this->contactInformation = QubitContactInformation::getById(preg_replace('/^.*\/(\d+)$/', '$1', $item['id']));
        }
        else
        {
          $this->resource->contactInformations[] = $this->contactInformation = new QubitContactInformation;
        }

        foreach ($this->form as $field)
        {
          if (isset($item[$field->getName()]))
          {
            $this->processField($field);
          }
        }

        if (isset($item['id']))
        {
          $this->contactInformation->save();

          if ($this->contactInformation->primaryContact)
          {
            $this->contactInformation->makePrimaryContact();
          }
        }
      }
    }

    if (isset($this->request->deleteContactInformations))
    {
      foreach ($this->request->deleteContactInformations as $item)
      {
        $contactInformation = QubitContactInformation::getById($item);

        if (isset($contactInformation))
        {
          $contactInformation->delete();
        }
      }
    }
  }

  public function execute($request)
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
    $this->form->getWidgetSchema()->setNameFormat('editContactInformation[%s]');

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }
  }
}
