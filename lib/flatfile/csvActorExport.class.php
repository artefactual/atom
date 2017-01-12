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
 * Export flatfile actor data
 */
class csvActorExport extends QubitFlatfileExport
{
  protected $options = array();

  /*
   * Store export parameters for use.
   *
   * @return void
   */
  public function setOptions($options = array())
  {
    $this->options = $options;
  }

  /*
   * Specific column settings before CSV row write
   *
   * @return void
   */
  protected function modifyRowBeforeExport()
  {
    $this->setMaintenanceNote();
  }

  private function setMaintenanceNote()
  {
    $criteria = new Criteria;
    $criteria->add(QubitNote::OBJECT_ID, $this->resource->id);
    $criteria->add(QubitNote::TYPE_ID, QubitTerm::MAINTENANCE_NOTE_ID);

    if (null !== $note = QubitNote::getOne($criteria))
    {
      $this->setColumn('maintenanceNotes', (string)$note);
    }
  }
}
