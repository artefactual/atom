<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Represent the time, place and/or agent of events in an artifact's history
 *
 * @package    qubit
 * @subpackage event
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitEvent extends BaseEvent
{
  // Flag for updating search index on save or delete
  public
    $indexOnSave = true;

  /**
   * Additional save functionality, e.g. update search index
   *
   * @param mixed $connection provide a database connection
   * @return QubitInformationObject self-reference
   */
  public function save($connection = null)
  {
    // TODO $cleanInformationObject = $this->informationObject->clean;
    $cleanInformationObjectId = $this->__get('informationObjectId', array('clean' => true));

    parent::save($connection);

    if ($this->indexOnSave)
    {
      if ($this->informationObjectId != $cleanInformationObjectId && null !== QubitInformationObject::getById($cleanInformationObjectId))
      {
        QubitSearch::updateInformationObject(QubitInformationObject::getById($cleanInformationObjectId));
      }

      if (isset($this->informationObject))
      {
        QubitSearch::updateInformationObject($this->informationObject);
      }
    }

    return $this;
  }

  protected function insert($connection = null)
  {
    $this->slug = QubitSlug::slugify($this->slug);

    return parent::insert($connection);
  }

  public function delete($connection = null)
  {
    parent::delete($connection);

    if (isset($this->informationObject))
    {
      QubitSearch::updateInformationObject($this->getInformationObject());
    }
  }

  public function getPlace(array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->id);
    $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::PLACE_ID);
    $relation = QubitObjectTermRelation::get($criteria);

    if (count($relation) > 0)
    {
      return $relation[0]->getTerm();
    }
    else
    {
      return null;
    }
  }
}
