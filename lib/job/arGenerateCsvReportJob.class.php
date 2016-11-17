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
  protected
    $extraRequiredParameters = array('objectId', 'reportType', 'reportTypeLabel', 'reportFormat');

  private
    $resource = null;

  const
    itemOrFileTemplatePath = 'apps/qubit/modules/informationobject/templates/itemOrFileListSuccess.php',
    storageLocationsTemplatePath = 'apps/qubit/modules/informationobject/templates/storageLocationsSuccess.php',
    boxLabelTemplatePath = 'apps/qubit/modules/informationobject/templates/boxLabelSuccess.php',
    reportsDir = 'downloads/reports';

  private
    $templatePaths = array(
      'itemList' => self::itemOrFileTemplatePath,
      'fileList' => self::itemOrFileTemplatePath,
      'storageLocations' => self::storageLocationsTemplatePath,
      'boxLabel' => self::boxLabelTemplatePath,
    );

  public function runJob($parameters)
  {
    $this->params = $parameters;
    $this->createReportsDir();

    // Check that object exists and that it is not the root
    if (null === $this->resource = QubitInformationObject::getById($this->params['objectId']))
    {
      $this->error($this->i18n->__('Error: Could not find an information object with id: %1',
                                   array('%1' => $this->params['objectId'])));
      return false;
    }

    $this->filename = self::getFilename($this->resource, $this->params['reportFormat'], $this->params['reportType']);

    switch ($this->params['reportType'])
    {
      case 'itemList':
      case 'fileList':
        $results = $this->getFileOrItemListResults($this->params['reportType'] == 'itemList' ? 'item' : 'file');

        if ('csv' === $this->params['reportFormat'])
        {
          $ret = $this->writeItemOrListCsv($results);
        }
        else
        {
          $ret = $this->writeHtml($results);
        }

        break;

      case 'storageLocations':
        $results = $this->getStorageLocationsResults();

        if ('csv' === $this->params['reportFormat'])
        {
          $ret = $this->writeStorageLocationsCsv($results);
        }
        else
        {
          $ret = $this->writeHtml($results);
        }

        break;

      case 'boxLabel':
        $results = $this->getBoxLabelResults();

        if ('csv' === $this->params['reportFormat'])
        {
          $ret = $this->writeBoxLabelCsv($results);
        }
        else
        {
          $ret = $this->writeHtml($results);
        }

        break;

      default:
        $this->error($this->i18n->__('Invalid report type: %1', array('%1' => $this->params['reportType'])));
        return false;
    }

    $this->job->setStatusCompleted();
    $this->job->save();

    return $ret;
  }

  private function createReportsDir()
  {
    $dirPath = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.self::reportsDir;

    if (!is_dir($dirPath) && !mkdir($dirPath, 0755))
    {
      throw new sfException('Failed to create reports directory.');
    }
  }

  public static function getFilename($resource, $format, $type)
  {
    return self::reportsDir.DIRECTORY_SEPARATOR.$resource->slug.'-'.$type.'.'.$format;
  }

  private function getStorageLocationsResults()
  {
    $criteria = new Criteria;

    $criteria->setDistinct();
    $criteria->add(QubitInformationObject::LFT, $this->resource->lft, Criteria::GREATER_EQUAL);
    $criteria->add(QubitInformationObject::RGT, $this->resource->rgt, Criteria::LESS_EQUAL);
    $criteria->add(QubitRelation::TYPE_ID, QubitTerm::HAS_PHYSICAL_OBJECT_ID);
    $criteria->addJoin(QubitRelation::OBJECT_ID, QubitInformationObject::ID);
    $criteria->addJoin(QubitRelation::SUBJECT_ID, QubitPhysicalObject::ID);

    return QubitPhysicalObject::get($criteria);
  }

  private function getFileOrItemListResults($levelOfDescription)
  {
    $sortBy = isset($this->params['sortBy']) ? $this->params['sortBy'] : 'referenceCode';

    $c2 = new Criteria;
    $c2->addJoin(QubitTerm::ID, QubitTermI18n::ID, Criteria::INNER_JOIN);
    $c2->add(QubitTermI18n::NAME, $levelOfDescription);
    $c2->add(QubitTermI18n::CULTURE, 'en');
    $c2->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);

    if (null === $lod = QubitTermI18n::getOne($c2))
    {
      throw new sfException("Can't find '$levelOfDescription' level of description in term table");
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
      $creationDates = $this->getCreationDates($item);

      $results[$parentFile][] = array(
        'resource' => $item,
        'referenceCode' => QubitInformationObject::getStandardsBasedInstance($item)->referenceCode,
        'title' => $item->getTitle(array('cultureFallback' => true)),
        'dates' => isset($creationDates) ? Qubit::renderDateStartEnd($creationDates->getDate(array('cultureFallback' => true)),
                   $creationDates->startDate, $creationDates->endDate) : '',
        'startDate' => isset($creationDates) ? $creationDates->startDate : null,
        'accessConditions' => $item->getAccessConditions(array('cultureFallback' => true)),
        'locations' => $this->getLocationString($item)
      );
    }

    // Sort items by selected criteria
    foreach ($results as $key => &$items)
    {
      uasort($items, function($a, $b) use ($sortBy) {
        return strnatcasecmp($a[$sortBy], $b[$sortBy]);
      });
    }

    return $results;
  }

  private function getBoxLabelResults()
  {
    $results = array();

    foreach ($this->resource->descendants->andSelf()->orderBy('rgt') as $informationObject)
    {
      $creationDates = array();

      foreach ($informationObject->getDates(array('type_id' => QubitTerm::CREATION_ID)) as $item)
      {
        $creationDates[] = $item->getDate(array('cultureFallback' => true));
      }

      // Write reference code, container name, title, creation dates
      foreach ($informationObject->getPhysicalObjects() as $item)
      {
        $results[] = array(
          'referenceCode' => $informationObject->referenceCode,
          'physicalObjectName' => $item->__toString(),
          'title' => $informationObject->__toString(),
          'creationDates' => implode($creationDates, '|'),
        );
      }
    }

    return $results;
  }

  private function writeBoxLabelCsv($results)
  {
    if (!count($results))
    {
      $this->info($this->i18n->__('No results found for box label report.'));
      return true;
    }

    if (null === $fh = fopen($this->filename, 'w'))
    {
      throw new sfException('Unable to open file '.$this->filename.' - please check permissions.');
    }

    fputcsv($fh, array_keys($results[0]));

    foreach ($results as $item)
    {
      fputcsv($fh, $item);
    }

    fclose($fh);
    return true;
  }

  private function writeStorageLocationsCsv($results)
  {
    if (!count($results))
    {
      $this->info($this->i18n->__('No results found for storage locations report.'));
      return true;
    }

    if (null === $fh = fopen($this->filename, 'w'))
    {
      throw new sfException('Unable to open file '.$this->filename.' - please check permissions.');
    }

    fputcsv($fh, array($this->i18n->__('Name'), $this->i18n->__('Location'), $this->i18n->__('Type')));

    foreach ($results as $item)
    {
      fputcsv($fh, array($item->name, $item->location, $item->type));
    }

    fclose($fh);
    return true;
  }

  private function writeItemOrListCsv($results)
  {
    if (!count($results))
    {
      $this->info($this->i18n->__('No results found for item or list report.'));
      return true;
    }

    if (null === $fh = fopen($this->filename, 'w'))
    {
      throw new sfException('Unable to open file '.$this->filename.' - please check permissions.');
    }

    // Iterate over descriptions and their report results
    foreach ($results as $tldTitle => $items)
    {
      fputcsv($fh, array($this->i18n->__('Archival description hierarchy:')));

      // Display hierarchy leading up to the top level of description before report results for items / files
      foreach ($items[0]['resource']->getAncestors()->orderBy('lft') as $ancestor)
      {
        if ($ancestor->id != QubitInformationObject::ROOT_ID)
        {
          fputcsv($fh, array($ancestor->getTitle(array('cultureFallback' => true))));
        }
      }

      fputcsv($fh, array('---'));
      $first = true;

      // Display items or files
      foreach ($items as $row)
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
    }

    fclose($fh);
    return true;
  }

  private function writeHtml($results)
  {
    if (!count($results))
    {
      $this->info($this->i18n->__('No results found for '.$this->params['reportTypeLabel'].' report.'));
      return true;
    }

    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Asset', 'Tag', 'Url'));

    if (null === $fh = fopen($this->filename, 'w'))
    {
      throw new sfException('Unable to open file '.$this->filename.' - please check permissions.');
    }

    $resource = $this->resource; // Pass resource to template.

    if ('itemList' === $this->params['reportType'] || 'fileList' === $this->params['reportType'])
    {
      $includeThumbnails = $this->params['includeThumbnails'];
      $sortBy = $this->params['sortBy'];
      $reportTypeLabel = $this->params['reportTypeLabel'];
    }

    ob_start();
    include $this->templatePaths[$this->params['reportType']];
    $output = ob_get_clean();

    fwrite($fh, $output);
    fclose($fh);
  }

  private function getLocationString($resource)
  {
    $locations = array();
    if (null !== ($physicalObjects = $resource->getPhysicalObjects()))
    {
      foreach ($physicalObjects as $item)
      {
        $locations[] = $item->getLabel();
      }
    }

    return implode('; ', $locations);
  }

  private function getCreationDates($resource)
  {
    $creationEvents = $resource->getCreationEvents();

    if (!count($creationEvents) && isset($resource->parent))
    {
      return $this->getCreationDates($resource->parent);
    }
    else
    {
      foreach ($creationEvents as $item)
      {
        if (null != $item->getDate(array('cultureFallback' => true)) || null != $item->startDate)
        {
          return $item;
        }
      }
    }
  }
}
