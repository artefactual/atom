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
 * @subpackage repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class InformationObjectBrowseAction extends DefaultBrowseAction
{
  const INDEX_TYPE = 'QubitInformationObject';

  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'languages' =>
        array('type' => 'term',
              'field' => 'i18n.languages',
              'filter' => 'hideDrafts',
              'size' => 10),
      'levels' =>
        array('type' => 'term',
              'field' => 'levelOfDescriptionId',
              'filter' => 'hideDrafts',
              'size' => 10),
      'mediatypes' =>
        array('type' => 'term',
              'field' => 'digitalObject.mediaTypeId',
              'filter' => 'hideDrafts',
              'size' => 10),
      'digitalobjects' =>
        array('type' => 'query',
              'field' => array('hasDigitalObject' => true),
              'filter' => 'hideDrafts',
              'populate' => false),
      'repos' =>
        array('type' => 'term',
              'field' => 'repository.id',
              'filter' => 'hideDrafts',
              'size' => 10),
      'places' =>
        array('type'   => 'term',
              'field'  => 'places.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'subjects' =>
        array('type'   => 'term',
              'field'  => 'subjects.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'genres' =>
        array('type'   => 'term',
              'field'  => 'genres.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'creators' =>
        array('type'   => 'term',
              'field'  => 'creators.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'names' =>
        array('type'   => 'term',
              'field'  => 'names.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'collection' =>
        array('type'   => 'term',
              'field'  => 'partOf.id',
              'size'   => 10),
      'toplevel' =>
        array('type'   => 'query',
              'field' => array('parentId' => QubitInformationObject::ROOT_ID),
              'filter' => 'hideDrafts',
              'populate' => false));

  protected function populateFacet($name, $ids)
  {
    switch ($name)
    {
      case 'levels':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      case 'repos':
        $criteria = new Criteria;
        $criteria->add(QubitRepository::ID, array_keys($ids), Criteria::IN);

        foreach (QubitRepository::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      case 'mediatypes':
      case 'places':
      case 'subjects':
      case 'genres':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'creators':
      case 'names':
        $criteria = new Criteria;
        $criteria->add(QubitActor::ID, array_keys($ids), Criteria::IN);

        foreach (QubitActor::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      case 'collection':
        $criteria = new Criteria;
        $criteria->add(QubitInformationObject::ID, array_keys($ids), Criteria::IN);

        foreach (QubitInformationObject::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      default:
        parent::populateFacet($name, $ids);
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if (1 === preg_match('/^[\s\t\r\n]*$/', $request->subquery))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\MatchAll());
    }
    else
    {
      $queryText = new \Elastica\Query\QueryString($request->subquery);
      $queryText->setDefaultOperator('AND');
      $queryText->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.title'));

      $this->search->queryBool->addMust($queryText);
    }

    // Filter by dates
    if ((isset($request->from) && ctype_digit($request->from))
        || (isset($request->to) && ctype_digit($request->to)))
    {
      $rangeFilterOptions = array();

      if (isset($request->from) && ctype_digit($request->from))
      {
        $rangeFilterOptions['gte'] = $request->from;
      }

      if (isset($request->to) && ctype_digit($request->to))
      {
        $rangeFilterOptions['gte'] = $request->to;
      }

      $queryRange = new \Elastica\Query\Range;
      $queryRange->addField('dates.startDate', $rangeFilterOptions);

      $this->search->queryBool->addMust($queryRange);
    }

    if (isset($request->collection) && ctype_digit($request->collection))
    {
      if (null === $this->collectionFilter = QubitInformationObject::getById($request->collection))
      {
        $this->forward404();

        return;
      }

      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('ancestors' => $request->collection)));
    }

    if (isset($request->repos) && ctype_digit($request->repos))
    {
      if (null === $this->repos = QubitRepository::getById($request->repos))
      {
        $this->forward404();

        return;
      }
    }

    // Default to show top level descriptions only when browsing.
    if (!isset($request->topLod))
    {
      $request->topLod = true;
    }

    $this->handleTopLevelDescriptionsOnlyFilter();

    if (isset($request->onlyMedia))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('hasDigitalObject' => true)));
    }

    // Sort
    switch ($request->sort)
    {
      case 'identifier':
        $this->search->query->addSort(array('referenceCode.untouched' => 'asc'));

      // I don't think that this is going to scale, but let's leave it for now
      case 'alphabetic':
        $field = sprintf('i18n.%s.title.untouched', $this->selectedCulture);
        $this->search->query->addSort(array($field => 'asc'));

        break;

      case 'lastUpdated':
      default:
        $this->search->query->setSort(array('updatedAt' => 'desc'));
    }

    $this->search->query->setQuery($this->search->queryBool);

    // Filter drafts
    QubitAclSearch::filterDrafts($this->search->filterBool);

    // Set filter
    if (0 < count($this->search->filterBool->toArray()))
    {
      $this->search->query->setFilter($this->search->filterBool);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();

    $this->populateFacets($resultSet);
  }
}
