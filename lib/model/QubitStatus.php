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

class QubitStatus extends BaseStatus
{
  // Flag for updating search index on save of Status
  protected $indexOnSave = true;

  public function __toString()
  {
    return $this->status->__toString();
  }

  public function save($connection = null)
  {
    // TODO: $cleanObject = $this->object->clean;
    $cleanObjectId = $this->__get('objectId', array('clean' => true));

    parent::save($connection);

    if ($this->indexOnSave())
    {
      if ($this->objectId != $cleanObjectId && null !== QubitInformationObject::getById($cleanObjectId))
      {
        QubitSearch::updateInformationObject(QubitInformationObject::getById($cleanObjectId));
      }

      if ($this->object instanceof QubitInformationObject)
      {
        QubitSearch::updateInformationObject($this->object);
      }
    }

    return $this;
  }

  /**
   * Flag whether to update the search index when saving this object
   *
   * @param boolean $bool flag value
   * @return QubitInformationObject self-reference
   */
  public function setIndexOnSave($bool)
  {
    if ($bool)
    {
      $this->indexOnSave = true;
    }
    else
    {
      $this->indexOnSave = false;
    }

    return $this;
  }

  /**
   * Update search index on save?
   *
   * @return boolean current flag
   */
  public function indexOnSave()
  {
    return $this->indexOnSave;
  }

  public function delete($connection = null)
  {
    parent::delete($connection);

    if ($this->getObject() instanceof QubitInformationObject)
    {
      QubitSearch::updateInformationObject($this->getObject());
    }
  }
}
