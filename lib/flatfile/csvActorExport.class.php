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

  /**
   * Export a actor, and additionally any aliases / relationships.
   *
   * @param object $resource  object to export
   *
   * @return void
   */
  public function exportResource(&$resource)
  {
    parent::exportResource($resource);

    // Export relations
    $filenamePrepend = ($this->standard !== null) ? $this->standard .'_' : '';
    $filename = sprintf('%s/%s%s_%s.csv', $this->path, $filenamePrepend,
                        str_pad($this->fileIndex, 10, '0', STR_PAD_LEFT), 'relations');

    $this->exportRelations($filename, $resource);
  }

  private function exportRelations($filename, $resource)
  {
    $rows = array();

    foreach ($resource->getActorRelations() as $item)
    {
      $relatedEntity = $item->getOpposedObject($resource->id);

      if (QubitTerm::ROOT_ID == $item->type->parentId)
      {
        $category = $item->type;
      }
      else
      {
        $category = $item->type->parent;
      }

      $rows[] = array(
        'sourceAuthorizedFormOfName' => $resource->authorizedFormOfName,
        'targetAuthorizedFormOfName' => $relatedEntity->authorizedFormOfName,
        'category'                   => (string)$category, // Return string representation for QubitTerm
        'description'                => $item->description,
        'date'                       => $item->date,
        'startDate'                  => $item->startDate,
        'endDate'                    => $item->endDate,
        'culture'                    => $resource->culture
      );
    }

    $this->writeCompanionCsv($filename, $rows);
  }

  private function writeCompanionCsv($filename, array $rows)
  {
    if (empty($rows))
    {
      return;
    }

    if (false === $fh = fopen($filename, 'a'))
    {
      throw new sfException("Failed to create file $filename");
    }

    if (!filesize($filename))
    {
      // Write header
      fputcsv($fh, array_keys($rows[0]));
    }

    foreach ($rows as $row)
    {
      fputcsv($fh, $row);
    }

    fclose($fh);
  }

  /*
   * Specific column settings before CSV row write
   *
   * @return void
   */
  protected function modifyRowBeforeExport()
  {
    $this->setColumn('parallelFormsOfName', $this->getNames(QubitTerm::PARALLEL_FORM_OF_NAME_ID));
    $this->setColumn('standardizedFormsOfName', $this->getNames(QubitTerm::STANDARDIZED_FORM_OF_NAME_ID));
    $this->setColumn('otherFormsOfName', $this->getNames(QubitTerm::OTHER_FORM_OF_NAME_ID));

    $this->setMaintenanceNote();
    $this->setOccupations();
    $this->setPlaceAccessPoints();
    $this->setSubjectAccessPoints();

    // Set digital object public URL
    $this->setColumn('digitalObjectURI', $this->resource->getDigitalObjectPublicUrl());

    // Grab checksum for this digital object
    $this->setColumn('digitalObjectChecksum', $this->resource->getDigitalObjectChecksum());
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

  private function setOccupations()
  {
    $addNotes = false;
    $actorOccupations = $actorOccupationNotes = array();

    foreach ($this->resource->getOccupations() as $occupation)
    {
      $actorOccupations[] = (string)$occupation->term;

      $note = $occupation->getNotesByType(array(
        'noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID
      ))->offsetGet(0);

      if (isset($note))
      {
        $addNotes = true;
        $actorOccupationNotes[] = (string)$note->content;
      }
      else
      {
        $actorOccupationNotes[] = 'NULL';
      }
    }

    $this->setColumn('actorOccupations', implode('|', $actorOccupations));

    if ($addNotes)
    {
      $this->setColumn('actorOccupationNotes', implode('|', $actorOccupationNotes));
    }
  }

  /*
   * Get place access point data
   *
   * @return void
   */
  private function setPlaceAccessPoints()
  {

    $accessPoints = $this->resource->getPlaceAccessPoints();

    $data          = array();
    $data['names'] = array();

    foreach ($accessPoints as $accessPoint)
    {
      if ($accessPoint->term->name)
      {
        $data['names'][] = $accessPoint->term->name;
      }
    }

    $this->setColumn('placeAccessPoints', implode('|', $data['names']));
  }

  /*
   * Get subject access point data
   *
   * @return void
   */
  private function setSubjectAccessPoints()
  {

    $accessPoints = $this->resource->getSubjectAccessPoints();

    $data = array();
    $data['names'] = array();

    foreach ($accessPoints as $accessPoint)
    {
      if ($accessPoint->term->name)
      {
        $data['names'][] = $accessPoint->term->name;
      }
    }

    $this->setColumn('subjectAccessPoints', implode('|', $data['names']));
  }

  /*
   * Get alternative forms of name
   *
   * @return array  List of names
   */
  private function getNames($typeId)
  {
    $results = array();

    foreach ($this->resource->getOtherNames(array('typeId' => $typeId)) as $name)
    {
      $results[] = $name->getName();
    }

    return $results;
  }
}
