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
 * Form for adding and editing related events
 *
 * @package    AccesstoMemory
 * @subpackage information object
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
        // Get related term id
        $value = $this->form->getValue('place');
        if (!empty($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $termId = $params['_sf_route']->resource->id;
        }

        // Get term relation
        if (isset($this->event->id))
        {
          $relation = QubitObjectTermRelation::getOneByObjectId($this->event->id);
        }

        // Nothing to do
        if (!isset($termId) && !isset($relation))
        {
          break;
        }

        // The relation needs to be deleted/updated independently
        // if the event exits, otherwise when deleting, it will try to
        // save it again from the objectTermRelationsRelatedByobjectId array.
        // If the event is new, the relation needs to be created and attached
        // to the event in the objectTermRelationsRelatedByobjectId array.
        if (!isset($termId) && isset($relation))
        {
          $relation->delete();

          break;
        }

        if (isset($termId) && isset($relation))
        {
          $relation->termId = $termId;
          $relation->save();

          break;
        }

        $relation = new QubitObjectTermRelation;
        $relation->termId = $termId;

        $this->event->objectTermRelationsRelatedByobjectId[] = $relation;

        break;

      default:

        return parent::processField($field);
    }
  }
}
