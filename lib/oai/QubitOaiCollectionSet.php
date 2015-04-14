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
 * An OAI set for a single collection
 *
 * @package    AccesstoMemory
 * @subpackage oai
 * @author     Mark Triggs <mark@teaspoon-consulting.com>
 */

class QubitOaiCollectionSet implements QubitOaiSet
{
  private $collection;

  public function __construct($collection) {
    $this->collection = $collection;
  }

  public function contains($record) {
    $lft = $record->getLft();
    return ($this->collection['lft'] <= $lft AND $this->collection['rgt'] > $lft);
  }

  public function setSpec() {
    return $this->collection->getOaiIdentifier();
  }

  public function getName() {
    return new sfIsadPlugin($this->collection);
  }

  public function apply($criteria) {
    $criteria->add(QubitInformationObject::PARENT_ID, null, Criteria::ISNOTNULL);

    $criteria->add(QubitInformationObject::LFT, $this->collection['lft'], Criteria::GREATER_EQUAL);
    $criteria->add(QubitInformationObject::RGT, $this->collection['rgt'], Criteria::LESS_EQUAL);
  }
}
