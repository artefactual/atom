<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Form for adding and editing related events
 *
 * @package    qubit
 * @subpackage information object
 * @version    svn: $Id: eventComponent.class.php 10288 2011-11-08 21:25:05Z mj $
 * @author     David Juhasz <david@artefactual.com>
 */
class InformationObjectEventComponent extends EventEditComponent
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'actor',
      'date',
      'endDate',
      'startDate',
      'description',
      'place',
      'type');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'actor':
        $this->form->setValidator('actor', new sfValidatorString);
        $this->form->setWidget('actor', new sfWidgetFormSelect(array('choices' => array())));

        $this->form->getWidgetSchema()->actor->setHelp($this->context->i18n->__('Use the actor name field to link an authority record to this description. Search for an existing name in the authority records by typing the first few characters of the name. Alternatively, type a new name to create and link to a new authority record.'));

        break;

      case 'description':
        $this->form->setValidator('description', new sfValidatorString);
        $this->form->setWidget('description', new sfWidgetFormInput);

        break;

      case 'place':
        $this->form->setValidator('place', new sfValidatorString);
        $this->form->setWidget('place', new sfWidgetFormSelect(array('choices' => array())));

        $this->form->getWidgetSchema()->place->setHelp($this->context->i18n->__('Search for an existing term in the places taxonomy by typing the first few characters of the term name. Alternatively, type a new term to create and link to a new place term.'));

        break;

      default:

        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'actor':
        unset($this->event->actor);

        $value = $this->form->getValue('actor');
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->event->actor = $params['_sf_route']->resource;
        }

        break;

      case 'place':
        $value = $this->form->getValue('place');
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
        }

        foreach ($this->event->objectTermRelationsRelatedByobjectId as $item)
        {
          if (isset($value) && $params['_sf_route']->resource->id == $item->id)
          {
            unset($value);
          }
          else
          {
            $item->delete();
          }
        }

        if (isset($value))
        {
          $relation = new QubitObjectTermRelation;
          $relation->term = $params['_sf_route']->resource;

          $this->event->objectTermRelationsRelatedByobjectId[] = $relation;
        }

        break;

      default:

        return parent::processField($field);
    }
  }
}
