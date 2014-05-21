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
 * File list report
 *
 * @package    AccesstoMemory
 * @subpackage informationobject
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class InformationObjectFileListAction extends sfAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'sortBy'
    );

  protected function addField($name)
  {
    switch ($name)
    {
      case 'sortBy':
        $choices = array(
          'identifier' => $this->context->i18n->__('Folder ID'),
          'title' => $this->context->i18n->__('Title'),
          'startDate' => $this->context->i18n->__('Date (based on start date)')
        );

        $this->form->setDefault($name, 'identifier');
        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormChoice(array(
          'expanded' => true,
          'choices' => $choices)));

        break;
    }
  }

  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;
    if (!isset($this->resource))
    {
      $this->forward404();
    }

    $this->form = new sfForm;

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->generateReport($request);

        if (0 < count($this->results))
        {
          return sfView::SUCCESS;
        }
        else
        {
          return sfView::ERROR;
        }
      }
    }

    return 'Criteria';
  }

  public function generateReport($request)
  {
    // Get "file" term in "level of description" taxonomy
    $c2 = new Criteria;
    $c2->addJoin(QubitTerm::ID, QubitTermI18n::ID, Criteria::INNER_JOIN);
    $c2->add(QubitTermI18n::NAME, 'file');
    $c2->add(QubitTermI18n::CULTURE, 'en');
    $c2->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);

    $lod = QubitTermI18n::getOne($c2);

    if (null === $lod)
    {
      throw new sfException('Can\'t find "file" level of description in term table');
    }

    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::LFT, $this->resource->lft, Criteria::GREATER_EQUAL);
    $criteria->add(QubitInformationObject::RGT, $this->resource->rgt, Criteria::LESS_EQUAL);
    $criteria->addAscendingOrderByColumn(QubitInformationObject::LFT);

    // Filter drafts
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    $this->results = array();
    $this->resultCount = 0;
    $informationObjects = QubitInformationObject::get($criteria);

    foreach($informationObjects as $item)
    {
      if ($lod->id == $item->levelOfDescriptionId)
      {
        $creationDates = self::getCreationDates($item);
        $parentTitle = QubitInformationObject::getStandardsBasedInstance($item->parent)->__toString();

        $this->results[$parentTitle][] = array(
          'resource' => $item,
          'identifier' => QubitInformationObject::getStandardsBasedInstance($item)->identifier,
          'title' => $item->getTitle(array('cultureFallback' => true)),
          'dates' => (isset($creationDates)) ? Qubit::renderDateStartEnd($creationDates->getDate(array('cultureFallback' => true)), $creationDates->startDate, $creationDates->endDate) : '&nbsp;',
          'startDate' => (isset($creationDates)) ? $creationDates->startDate : null,
          'descriptionStatus' => QubitInformationObject::getStandardsBasedInstance($item)->descriptionStatus,
          'digitalobject' => $item->getDigitalObject()
        );

        $this->resultCount++;
      }
    }

    // Sort items by selected criteria
    $sortBy = $this->form->getValue('sortBy');
    foreach ($this->results as $key => &$items)
    {
      uasort($items, function($a, $b) use ($sortBy) {
         return strnatcasecmp($a[$sortBy], $b[$sortBy]); 
      });
    }
  }

  public static function getLocationString($resource)
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

  public static function getCreationDates($resource)
  {
    $creationEvents = $resource->getCreationEvents();

    if (0 == count($creationEvents))
    {
      if (isset($resource->parent))
      {
        return self::getCreationDates($resource->parent);
      }
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
