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
 * An OAI set for all top-level (collection) records
 *
 * @package    AccesstoMemory
 * @subpackage oai
 * @author     Mark Triggs <mark@teaspoon-consulting.com>
 */

class QubitOaiTopLevelSet implements QubitOaiSet
{
  public function contains($record) {
    /* Allow the collection set to take responsibility for records to preserve
     * the current behaviour. */
    return false;
  }

  public function setSpec() {
    return "oai:virtual:top-level-records";
  }

  public function getName() {
    return "Top-level collection record set";
  }

  public function apply($criteria) {
    $criteria->add(QubitInformationObject::PARENT_ID, QubitInformationObject::ROOT_ID);
  }
}
