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

class QubitPhysicalObject extends BasePhysicalObject
{
  /**
   * Call this function when casting object instance as type string
   *
   * @return string  Physical Object Name
   */
  public function __toString()
  {
    $string = $this->name;
    if (!isset($string))
    {
      $string = $this->getName(array('sourceCulture' => true));
    }

    return (string) $string;
  }

  public function insert($connection = null)
  {
    if (!isset($this->slug))
    {
      $this->slug = QubitSlug::slugify($this->__get('name', array('sourceCulture' => true)));
    }

    return parent::insert($connection);
  }

  public function getLabel()
  {
    $label = '';

    if ($this->type)
    {
      $label .= $this->type.': ';
    }

    $label .= $this->__toString();

    if (0 == strlen($location = $this->getLocation()))
    {
      $location = $this->getLocation(array('sourceCulture' => true));
    }

    if (0 < strlen($location))
    {
      $label .= ' - '.$location;
    }

    return $label;
  }

  /**
   * Overwrite BasePhysicalObject::delete() method to add cascading delete
   * logic
   *
   * @param mixed $connection a database connection object
   */
  public function delete($connection = null)
  {
    $this->deleteInformationObjectRelations();

    parent::delete($connection);
  }

  /**
   * Delete relation records linking this physical object to information objects
   */
  public function deleteInformationObjectRelations()
  {
    $informationObjectRelations = QubitRelation::getRelationsBySubjectId($this->id,
    array('typeId'=>QubitTerm::HAS_PHYSICAL_OBJECT_ID));

    foreach ($informationObjectRelations as $relation)
    {
      $relation->delete();
    }
  }

  /**
   * Get related information object via QubitRelation relationship
   *
   * @param array $options list of options to pass to QubitQuery
   * @return QubitQuery collection of Information Objects
   */
  public function getInformationObjects($options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitPhysicalObject::ID, QubitRelation::SUBJECT_ID);
    $criteria->addJoin(QubitRelation::OBJECT_ID, QubitInformationObject::ID);
    $criteria->add(QubitPhysicalObject::ID, $this->id);

    return QubitQuery::createFromCriteria($criteria, 'QubitInformationObject', $options);
  }

  public function getEadContainerAttributes()
  {
    switch ($this->type)
    {
      case 'Cardboard box':
        $result = 'type="box" label="cardboard"';

        break;

      case 'Hollinger box':
        $result = 'type="box" label="hollinger"';

        break;

      case 'Filing cabinet':
        $result = 'type="cabinet" label="filing"';

        break;

      case 'Map cabinet':
        $result = 'type="cabinet" label="map"';

        break;

      default:
        $result = 'type="'.strtolower($this->type).'"';
    }

    return $result;
  }
}
