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
 * @package    AccesstoMemory
 * @author     Mike G <mikeg@artefactual.com>
 */

class arGenerateCsvReportJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('objectId', 'reportType');

  private $resource = null;

  public function runJob($parameters)
  {
    $this->parameters = $parameters;

    // Check that object exists and that it is not the root
    if (null === $this->resource = QubitInformationObject::getById($parameters['objectId']))
    {
      $this->error($this->i18n->__('Error: Could not find an information object with id: %1', array('%1' => $parameters['objectId'])));
      return false;
    }

    switch ($this->parameters['reportType'])
    {
      case 'fileList':
        $results = $this->getFileOrItemListResults('file');
        break;

      case 'itemList':
        $results = $this->getFileOrItemListResults('item');
        break;

      case 'storageLocations':
        break;

      case 'boxLabelCsv':
        break;

      default:
        $this->error($this->i18n->__('Invalid report type: %1', array('%1' => $parameters['reportType'])));
        return false;
    }

    $this->writeCsv($results);

    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }

  private function getFilename()
  {
    return 'downloads'.DIRECTORY_SEPARATOR.$this->resource->slug.'-'.$this->parameters['reportType'].'.csv';
  }

  private function getFileOrItemListResults($levelOfDescription)
  {
    $sortBy = isset($this->parameters['sortBy']) ? $this->parameters['sortBy'] : 'referenceCode';

    $c2 = new Criteria;
    $c2->addJoin(QubitTerm::ID, QubitTermI18n::ID, Criteria::INNER_JOIN);
    $c2->add(QubitTermI18n::NAME, $levelOfDescription);
    $c2->add(QubitTermI18n::CULTURE, 'en');
    $c2->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);

    if (null === $lod = QubitTermI18n::getOne($c2))
    {
      throw new sfException("Can't find 'item' level of description in term table");
    }

    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::LFT, $this->resource->lft, Criteria::GREATER_EQUAL);
    $criteria->add(QubitInformationObject::RGT, $this->resource->rgt, Criteria::LESS_EQUAL);
    $criteria->addAscendingOrderByColumn(QubitInformationObject::LFT);

    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);
    $results = array();

    if (null === $ios = QubitInformationObject::get($criteria))
    {
      return array();
    }

    foreach ($ios as $item)
    {
      if ($lod->id != $item->levelOfDescriptionId)
      {
        continue;
      }

      $parentTitle = QubitInformationObject::getStandardsBasedInstance($item->parent)->__toString();
      $creationDates = InformationObjectItemListAction::getCreationDates($item);

      $results[] = array(
        'resource' => $item,
        'referenceCode' => QubitInformationObject::getStandardsBasedInstance($item)->referenceCode,
        'title' => $item->getTitle(array('cultureFallback' => true)),
        'dates' => isset($creationDates) ? Qubit::renderDateStartEnd($creationDates->getDate(array('cultureFallback' => true)),
                   $creationDates->startDate, $creationDates->endDate) : '',
        'startDate' => isset($creationDates) ? $creationDates->startDate : null,
        'accessConditions' => $item->getAccessConditions(array('cultureFallback' => true)),
        'locations' => InformationObjectItemListAction::getLocationString($item)
      );
    }

    // Sort items by selected criteria
    uasort($results, function($a, $b) use ($sortBy) {
      return strnatcasecmp($a[$sortBy], $b[$sortBy]);
    });



    return true;
  }

  private function writeCsv($values)
  {
    if (null === $fh = fopen($this->getFilename(), 'w'))
    {
      throw new sfException('Unable to open file '.$this->getFilename().' - please check permissions.');
    }

    $first = true;

    foreach ($values as $row)
    {
      unset($row['resource']);

      // Write CSV header
      if ($first)
      {
        fputcsv($fh, array_keys($row));
        $first = false;
      }

      fputcsv($fh, $row);
    }

    fclose($fh);
  }

  private function generateItemListCsv()
  {

  }

  private function generateStorageLocationCsv()
  {

  }

  private function generateBoxLabelCsv()
  {

  }
}
