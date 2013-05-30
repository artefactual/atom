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
  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'levels' =>
        array('type' => 'term',
              'field' => 'levelOfDescriptionId',
              'size' => 10),
      'mediatypes' =>
        array('type' => 'term',
              'field' => 'digitalObject.mediaTypeId',
              'size' => 10,
              'populate' => false),
      'repos' =>
        array('type' => 'term',
              'field' => 'repository.id',
              'size' => 10),
      'places' =>
        array('type'   => 'term',
              'field'  => 'places.id',
              'size'   => 10),
      'subjects' =>
        array('type'   => 'term',
              'field'  => 'subjects.id',
              'size'   => 10),
      'creators' =>
        array('type'   => 'term',
              'field'  => 'creators.id',
              'size'   => 10),
      'names' =>
        array('type'   => 'term',
              'field'  => 'names.id',
              'size'   => 10));

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

      case 'places':
      case 'subjects':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->name;
        }

        break;

      case 'creators':
      case 'names':
        $criteria = new Criteria;
        $criteria->add(QubitActor::ID, array_keys($ids), Criteria::IN);

        foreach (QubitActor::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->authorizedFormOfName;
        }

        break;
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ('' == preg_replace('/[\s\t\r\n]*/', '', $request->subquery))
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll());
    }
    else
    {
      $queryText = new \Elastica\Query\QueryString($request->subquery);
      $queryText->setDefaultOperator('AND');
      $queryText->setDefaultField(sprintf('i18n.%s.title', $this->context->user->getCulture()));

      $this->queryBool->addMust($queryText);
    }

    if ((isset($request->from) && false !== ctype_digit($request->from))
        || (isset($request->to) && false !== ctype_digit($request->to)))
    {
      $rangeFilterOptions = array();

      if (isset($request->from) && false !== ctype_digit($request->from))
      {
        $rangeFilterOptions['gte'] = $request->from;
      }

      if (isset($request->to) && false !== ctype_digit($request->to))
      {
        $rangeFilterOptions['gte'] = $request->to;
      }

      $queryRange = new \Elastica\Query\Range;
      $queryRange->addField('dates.startDate', $rangeFilterOptions);

      $this->queryBool->addMust($queryRange);
    }

    // Filter drafts
    $this->query = QubitAclSearch::filterDrafts($this->query);

    $field = sprintf('i18n.%s.title.untouched', $this->context->user->getCulture());

    // Sort
    switch ($request->sort)
    {
      case 'repositoryDown':

        break;

      case 'repositoryUp':

        break;

      case 'titleDown':
        $this->query->setSort(array($field => 'desc'));

        break;

      case 'titleUp':
        $this->query->setSort(array($field => 'asc'));

        break;

      case 'updatedDown':
        $this->query->setSort(array('updatedAt' => 'desc'));

        break;

      case 'updatedUp':
        $this->query->setSort(array('updatedAt' => 'asc'));

        break;

      default:
        if ('alphabetic' == $this->sortSetting)
        {

        }
        else if ('lastUpdated' == $this->sortSetting)
        {

        }
    }

    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();

    $this->populateFacets($resultSet);
  }
}
