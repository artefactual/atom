<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class QubitDeaccession extends BaseDeaccession
{
  public function __toString()
  {
    return (string) $this->accession->__toString().' - D'.$this->identifier;
  }

  protected function insert($connection = null)
  {
    if (!isset($this->slug))
    {
      $this->slug = QubitSlug::slugify($this->__get('identifier', array('sourceCulture' => true)));
    }

    return parent::insert($connection);
  }

  public function save($connection = null)
  {
    parent::save($connection);

    QubitSearch::updateAccessionIndex($this->accession);

    return $this;
  }

  public function delete($connection = null)
  {
    QubitSearch::deleteById($this->accession);

    return parent::delete($connection);
  }
}
