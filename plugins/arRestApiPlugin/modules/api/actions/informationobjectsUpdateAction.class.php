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

class ApiInformationObjectsUpdateAction extends QubitApiAction
{
  /**
   * TODO: Share code with ApiInformationObjectsCreateAction
   */
  protected function post($request, $payload)
  {
    if (QubitInformationObject::ROOT_ID === (int)$this->request->id)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    if (null === $this->io = QubitInformationObject::getById($this->request->id))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    foreach ($payload as $field => $value)
    {
      $this->processField($field, $value);
    }

    $this->io->save();

    $this->response->setStatusCode(200);

    // TODO: return full object
    return array(
      'id' => (int)$this->io->id,
      'parent_id' => (int)$this->io->parentId);
  }

  protected function processField($field, $value)
  {
    switch ($field)
    {
      case 'level_of_description_id':
      case 'parent_id':
      case 'title':
        $field = lcfirst(sfInflector::camelize($field));
        $this->io->$field = $value;

        break;

      case 'level_of_description':
        $criteria = new Criteria;
        $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
        $criteria->add(QubitTermI18n::NAME, $value, Criteria::LIKE);
        if (null !== $term = QubitTerm::getOne($criteria))
        {
          $this->io->levelOfDescriptionId = $term->id;
        }

        break;
    }
  }
}
