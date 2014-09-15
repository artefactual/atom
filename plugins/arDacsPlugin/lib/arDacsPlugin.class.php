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

class arDacsPlugin extends sfIsadPlugin
{
  // sfIsadPlugin is not using properties
  protected
    $property;

  public function __get($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    switch ($name)
    {
      case 'technicalAccess':

        return $this->property('technicalAccess')->__get('value', $options);

        break;

      default:

        return parent::__get($name);
    }
  }

  public function __set($name, $value)
  {
    switch ($name)
    {
      case 'technicalAccess':

        $this->property('technicalAccess')->value = $value;

        return $this;

      default:

        parent::__set($name, $value);

        return $this;
    }
  }

  protected function property($name)
  {
    if (!isset($this->property[$name]))
    {
      $criteria = new Criteria;
      $this->resource->addPropertysCriteria($criteria);
      $criteria->add(QubitProperty::NAME, $name);

      if (1 == count($query = QubitProperty::get($criteria)))
      {
        $this->property[$name] = $query[0];
      }
      else
      {
        $this->property[$name] = new QubitProperty;
        $this->property[$name]->name = $name;

        $this->resource->propertys[] = $this->property[$name];
      }
    }

    return $this->property[$name];
  }

  public static function eventTypes()
  {
    $types = array(
      QubitTerm::getById(QubitTerm::CREATION_ID),
      QubitTerm::getById(QubitTerm::PUBLICATION_ID));

    $criteria = new Criteria;
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTermI18n::NAME, array('Broadcasting', 'Record-keeping activity'), Criteria::IN);
    $criteria->add(QubitTermI18n::CULTURE, 'en');
    if (null !== $terms = QubitTerm::get($criteria))
    {
      foreach ($terms as $item)
      {
        $types[] = $item;
      }
    }

    return $types;
  }
}
