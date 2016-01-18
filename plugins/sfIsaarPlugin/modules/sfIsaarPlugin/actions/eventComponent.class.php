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

class sfIsaarPluginEventComponent extends EventEditComponent
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'informationObject',
      'type',
      'resourceType',
      'startDate',
      'endDate',
      'date');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'informationObject':
        $this->form->setValidator($name, new sfValidatorString(array('required' => true)));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => array())));

        break;

      case 'resourceType':
        $term = QubitTerm::getById(QubitTerm::ARCHIVAL_MATERIAL_ID);

        $this->form->setDefault('resourceType', $this->context->routing->generate(null, array($term, 'module' => 'term')));
        $this->form->setValidator('resourceType', new sfValidatorString);
        $this->form->setWidget('resourceType', new sfWidgetFormSelect(array('choices' => array($this->context->routing->generate(null, array($term, 'module' => 'term')) => $term))));

        break;

      default:

        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'informationObject':
        unset($this->event->object);

        $value = $this->form->getValue('informationObject');

        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->event->object = $params['_sf_route']->resource;
        }

        break;

      default:

        return parent::processField($field);
    }
  }
}
