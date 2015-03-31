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
 * The interface provided by OAI set implementations
 *
 * @package    AccesstoMemory
 * @subpackage oai
 * @author     Mark Triggs <mark@teaspoon-consulting.com>
 */

interface QubitOaiSet
{
  /**
   * Query OAI set membership by record
   *
   * @param mixed $record A record that can be part of an OAI set
   *
   * @return boolean true if $record is contained in this OAI set.
   */

  public function contains($record);

  /**
   * The OAI set specification for the current set
   *
   * @return string An OAI set specification
   */

  public function setSpec();

  /**
   * The name of the current OAI set
   *
   * @return string A display name
   */

  public function getName();

  /**
   * Apply the current set's restrictions to $criteria
   *
   * @param Criteria $criteria The search criteria to be modified
   *
   */

  public function apply($criteria);
}
