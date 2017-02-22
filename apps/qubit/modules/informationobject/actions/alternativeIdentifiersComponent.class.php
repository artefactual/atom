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

class InformationObjectAlternativeIdentifiersComponent extends sfComponent
{
  public function execute($request)
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->addField('label');
    $this->addField('identifier');

    $this->alternativeIdentifiers = $this->resource->getProperties(null, 'alternativeIdentifiers');
  }

  protected function addField($name)
  {
    $this->form->setValidator($name, new sfValidatorString);
    $this->form->setWidget($name, new sfWidgetFormInput);
  }

  public function processForm()
  {
    $finalAlternativeIdentifiers = array();

    if (is_array($this->request->alternativeIdentifiers))
    {
      foreach ($this->request->alternativeIdentifiers as $item)
      {
        // Continue only if both fields are populated
        if (1 > strlen($item['label']) || 1 > strlen($item['identifier']))
        {
          continue;
        }

        $property = null;
        if (isset($item['id']))
        {
          $property = QubitProperty::getById($item['id']);

          // Store alternative identifiers that haven't been deleted by multiRow.js
          $finalAlternativeIdentifiers[] = $property->id;
        }

        if (is_null($property))
        {
          $this->resource->propertys[] =  $property = new QubitProperty;
          $property->scope = 'alternativeIdentifiers';
        }

        $property->name = $item['label'];
        $property->value = $item['identifier'];

        // Save the old properties, because adding a new property with "$this->resource->properties[] ="
        // overrides the unsaved changes
        //
        // We also do an additional check against resource id and property objectId; if they do
        // not match, we're in duplicate record mode and want to avoid modifying the original
        // record's alternative identifiers.
        if (isset($item['id']) && $property->objectId == $this->resource->id)
        {
          $property->save();
        }
      }
    }

    // Delete the old properties if they don't appear in the table (removed by multiRow.js)
    foreach ($this->alternativeIdentifiers as $item)
    {
      if (false === array_search($item->id, $finalAlternativeIdentifiers))
      {
        $item->delete();
      }
    }
  }
}
