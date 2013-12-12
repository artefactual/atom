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

class AccessionDeleteAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->resource = $this->getRoute()->resource;

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'delete'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // Accruals
    $criteria = new Criteria;
    $criteria->add(QubitRelation::TYPE_ID, QubitTerm::ACCRUAL_ID);
    $criteria->add(QubitRelation::OBJECT_ID, $this->resource->id);
    $criteria->addJoin(QubitRelation::SUBJECT_ID, QubitAccession::ID);
    $this->accruals = QubitAccession::get($criteria);

    if ($request->isMethod('delete'))
    {
      foreach ($this->resource->deaccessions as $item)
      {
        $item->delete();
      }

      foreach (QubitRelation::getBySubjectOrObjectId($this->resource->id) as $item)
      {
        $item->delete();
      }

      $this->resource->delete();

      $this->redirect(array('module' => 'accession', 'action' => 'browse'));
    }
  }
}
