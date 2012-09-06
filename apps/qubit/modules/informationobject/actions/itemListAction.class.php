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
 * Item list report
 *
 * @package    qubit
 * @subpackage informationobject
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 * @version    svn:$Id: itemListAction.class.php 11156 2012-03-12 23:22:44Z david $
 */
class InformationObjectItemListAction extends sfAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'sortBy',
      'includeThumbnails'
    );

  protected function addField($name)
  {
    switch ($name)
    {
      case 'sortBy':
        $choices = array(
          'referenceCode' => $this->context->i18n->__('Reference code'),
          'title' => $this->context->i18n->__('Title'),
          'startDate' => $this->context->i18n->__('Date (based on start date)')
        );

        if ($this->getUser()->isAuthenticated())
        {
          $choices['locations'] = $this->context->i18n->__('Retrieval information');
        }

        $this->form->setDefault($name, 'referenceCode');
        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormChoice(array(
          'expanded' => true,
          'choices' => $choices)));

        break;

      case 'includeThumbnails':
        $choices = array(
          '1' => $this->context->i18n->__('Yes')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array(
          'choices' => array_keys($choices),
          'multiple' => true)));

        $this->form->setWidget($name, new sfWidgetFormChoice(array(
          'expanded' => true,
          'multiple' => true,
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
    $this->includeThumbnails = false;
    if (is_array($values = $this->form->getValue('includeThumbnails')))
    {
      $this->includeThumbnails = (1 == $values[0]);
    }

    // Get "item" term in "level of description" taxonomy
    $c2 = new Criteria;
    $c2->addJoin(QubitTerm::ID, QubitTermI18n::ID, Criteria::INNER_JOIN);
    $c2->add(QubitTermI18n::NAME, 'item');
    $c2->add(QubitTermI18n::CULTURE, 'en');
    $c2->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);

    $lod = QubitTermI18n::getOne($c2);

    if (null === $lod)
    {
      throw new sfException('Can\'t find "item" level of description in term table');
    }

    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::LFT, $this->resource->lft, Criteria::GREATER_EQUAL);
    $criteria->add(QubitInformationObject::RGT, $this->resource->rgt, Criteria::LESS_EQUAL);
    $criteria->addAscendingOrderByColumn(QubitInformationObject::LFT);

    // Filter drafts
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    $this->results = array();
    $this->resultCount = 0;
    if (null !== ($results = QubitInformationObject::get($criteria)))
    {
      foreach($results as $item)
      {
        if ($lod->id == $item->levelOfDescriptionId)
        {
          $parentTitle = QubitInformationObject::getStandardsBasedInstance($item->parent)->__toString();
          $creationDates = self::getCreationDates($item);

          $this->results[$parentTitle][] = array(
            'resource' => $item,
            'referenceCode' => QubitInformationObject::getStandardsBasedInstance($item)->referenceCode,
            'title' => $item->getTitle(array('cultureFallback' => true)),
            'dates' => (isset($creationDates)) ? Qubit::renderDateStartEnd($creationDates->getDate(array('cultureFallback' => true)), $creationDates->startDate, $creationDates->endDate) : '&nbsp;',
            'startDate' => (isset($creationDates)) ? $creationDates->startDate : null,
            'accessConditions' => $item->getAccessConditions(array('cultureFallback' => true)),
            'locations' => self::getLocationString($item)
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
