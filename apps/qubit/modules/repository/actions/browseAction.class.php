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
class RepositoryBrowseAction extends DefaultBrowseAction
{
  const INDEX_TYPE = 'QubitRepository';

  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'languages' =>
        array('type' => 'term',
              'field' => 'i18n.languages',
              'size' => 10),
      'types' =>
        array('type' => 'term',
              'field' => 'types',
              'size' => 10),
      'regions' =>
        array('type' => 'term',
              'field' => 'contactInformations.i18n.en.region.untouched',
              'size' => 10),
      'geographicSubregions' =>
        array('type' => 'term',
              'field' => 'geographicSubregions',
              'size' => 10),
      'locality' =>
        array('type' => 'term',
              'field' => 'contactInformations.i18n.en.city.untouched',
              'size' => 10),
      'thematicAreas' =>
        array('type' => 'term',
              'field' => 'thematicAreas',
              'size' => 10));

  protected function populateFacet($name, $ids)
  {
    switch ($name)
    {
      case 'types':
      case 'geographicSubregions':
      case 'thematicAreas':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'regions':
      case 'locality':
        foreach ($ids as $key => $count)
        {
          $this->types[$key] = $key;
        }

        break;

      default:
        parent::populateFacet($name, $ids);
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    $this->cardView = 'card';
    $this->tableView = 'table';

    if (1 === preg_match('/^[\s\t\r\n]*$/', $request->subquery))
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll());
    }
    else
    {
      $queryText = new \Elastica\Query\QueryString($request->subquery);
      $queryText->setDefaultOperator('OR');
      $queryText->setDefaultField('_all');

      $this->queryBool->addMust($queryText);
    }

    $i18n = sprintf('i18n.%s.', $this->selectedCulture);

    switch ($request->sort)
    {
      case 'nameUp':
        $this->query->setSort(array($i18n.'authorizedFormOfName.untouched' =>
                              array('order' => 'asc', 'ignore_unmapped' => true)));
        break;

      case 'nameDown':
        $this->query->setSort(array($i18n.'authorizedFormOfName.untouched' =>
                              array('order' => 'desc', 'ignore_unmapped' => true)));
        break;

      case 'regionUp':
        $this->query->setSort(array($i18n.'region.untouched' =>
                              array('order' => 'asc', 'ignore_unmapped' => true)));
        break;

      case 'regionDown':
        $this->query->setSort(array($i18n.'region.untouched' =>
                              array('order' => 'desc', 'ignore_unmapped' => true)));
        break;

      case 'localityUp':
        $this->query->setSort(array($i18n.'city.untouched' =>
                              array('order' => 'asc', 'ignore_unmapped' => true)));
        break;

      case 'localityDown':
        $this->query->setSort(array($i18n.'city.untouched' =>
                              array('order' => 'desc', 'ignore_unmapped' => true)));
        break;

      case 'identifier':
        $this->query->addSort(array('identifier' => 'asc'));
      case 'alphabetic':
        $this->query->addSort(array($i18n.'authorizedFormOfName.untouched' => 'asc'));

        break;

      case 'lastUpdated':
      default:
        $this->query->setSort(array('updatedAt' => 'desc'));
    }

    $this->query->setQuery($this->queryBool);

    // Set filter
    if (0 < count($this->filterBool->toArray()))
    {
      $this->query->setFilter($this->filterBool);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitRepository')->search($this->query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();

    $this->populateFacets($resultSet);

    if (isset($request->view))
    {
      $this->view = $request->view;
    }
    else
    {
      $this->view = sfConfig::get('app_default_repository_view', 'card');
    }
  }
}
