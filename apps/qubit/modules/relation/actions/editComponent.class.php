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

class RelationEditComponent extends sfComponent
{
  protected function addField($name)
  {
    switch ($name)
    {
      case 'date':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        $this->form->getWidgetSchema()->date->setHelp($this->context->i18n->__('"Record, when relevant, the start and the end date of the relationship." (ISDF 6.3) Enter the date as you would like it to appear in the show page for the function, using qualifiers and/or typographical symbols to express uncertainty if desired.'));

        break;

      case 'endDate':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        $this->form->getWidgetSchema()->endDate->setHelp($this->context->i18n->__('Enter the end year. Do not use any qualifiers or typographical symbols to express uncertainty. If the start and end years are the same, enter data only in the "Date" field and leave the "End date" blank.'));
        $this->form->getWidgetSchema()->endDate->setLabel($this->context->i18n->__('End'));

        break;

      case 'startDate':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        $this->form->getWidgetSchema()->startDate->setHelp($this->context->i18n->__('Enter the start year. Do not use any qualifiers or typographical symbols to express uncertainty.'));
        $this->form->getWidgetSchema()->startDate->setLabel($this->context->i18n->__('Start'));

        break;

      case 'description':
        $this->form->setValidator('description', new sfValidatorString);
        $this->form->setWidget('description', new sfWidgetFormTextarea);

        $this->form->getWidgetSchema()->description->setHelp($this->context->i18n->__('Describe the nature of the relationship between the function and the related resource. (ISDF 6.2)'));

        break;

      case 'resource':
        $this->form->setValidator('resource', new sfValidatorBlacklist(array(
          'forbidden_values' => array($this->context->routing->generate(null, $this->resource)))));
        $this->form->setWidget('resource', new sfWidgetFormSelect(array('choices' => array())));

        break;
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'resource':
        unset($this->relation->object);

        $value = $this->form->getValue('resource');
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->relation->object = $params['_sf_route']->resource;
        }

        break;

      case 'type':
        unset($this->relation->type);

        $value = $this->form->getValue('type');
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->relation->type = $params['_sf_route']->resource;
        }

        break;

      default:
        $this->relation[$field->getName()] = $this->form->getValue($field->getName());
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
          $params = $this->context->routing->parse(Qubit::pathInfo($item['id']));
          $this->relation = $params['_sf_route']->resource;
        }
        else
        {
          $this->resource->relationsRelatedBysubjectId[] = $this->relation = new QubitRelation;
        }

        foreach ($this->form as $field)
        {
          if (isset($item[$field->getName()]))
          {
            $this->processField($field);
          }
        }

        // Only transient objects will be saved automatically
        if (isset($item['id']))
        {
          $this->relation->save();
        }
      }
    }
  }

  public function execute($request)
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }
  }
}
