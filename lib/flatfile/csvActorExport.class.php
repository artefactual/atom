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

    $companionFileTypes = array('aliases', 'relations');

    foreach ($companionFileTypes as $type)
    {
      if (!empty($this->options[$type]))
      {
        $this->exportCompanionFile($type, $resource);
      }
    }
  }

  /**
   * Export companion files with this resource. In this case, actor aliases or relations.
   *
   * @param string $type  The type of companion file,
   */
  private function exportCompanionFile($type, $resource)
  {
    $filename = sprintf('%s/%s_%s_%s.csv', $this->path, $this->standard,
                        str_pad($this->fileIndex, 10, '0', STR_PAD_LEFT), $type);

    switch ($type)
    {
      case 'aliases':
        $this->exportAliases($filename, $resource);
        break;

      case 'relations':
        $this->exportRelations($filename, $resource);
        break;

      default:
        throw new sfException("Invalid companion file type in csvActorExport::exportCompanionFile - $type given.");
    }
  }

  private function exportAliases($filename, $resource)
  {

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

      $row = array(
        'sourceAuthorizedFormOfName' => $resource->authorizedFormOfName,
        'targetAuthorizedFormOfName' => $relatedEntity->authorizedFormOfName,
        'category'                   => (string)$category, // Return string representation for QubitTerm
        'description'                => $item->description,
        'date'                       => $item->date,
        'startDate'                  => $item->startDate,
        'endDate'                    => $item->endDate,
        'culture'                    => $resource->culture
      );

      $rows[] = $row;
    }

    $this->writeCompanionCsv($filename, $rows);
  }

  private function writeCompanionCsv($filename, $rows)
  {
    if (empty($rows))
    {
      return;
    }

    if (false === $fh = fopen($filename, 'w'))
    {
      throw new sfException("Failed to create file $filename");
    }

    $header = array_keys($rows[0]);
    fputcsv($fh, $header);

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
