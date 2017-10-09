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

class AccessionBrowseAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->sortOptions = array(
      'lastUpdated' => $this->context->i18n->__('Most recent'),
      'accessionNumber' => $this->context->i18n->__('Accession number'),
      'title' => $this->context->i18n->__('Title'),
      'acquisitionDate' => $this->context->i18n->__('Acquisition date'));

    if (!isset($request->sort))
    {
      if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->subquery))
      {
        $request->sort = 'relevance';
      }
      else if ($this->getUser()->isAuthenticated())
      {
        $request->sort = sfConfig::get('app_sort_browser_user');
      }
      else
      {
        $request->sort = sfConfig::get('app_sort_browser_anonymous');
      }
    }

    $culture = $this->context->user->getCulture();

    $this->query = new \Elastica\Query;
    $this->query->setSize($request->limit);

    if (!empty($request->page))
    {
      $this->query->setFrom(($request->page - 1) * $request->limit);
    }

    $this->queryBool = new \Elastica\Query\BoolQuery;

    if (1 === preg_match('/^[\s\t\r\n]*$/', $request->subquery))
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll);
    }
    else
    {
      $queryString = new \Elastica\Query\QueryString(arElasticSearchPluginUtil::escapeTerm($request->subquery));

      $boost = array(
        'donors.i18n.%s.authorizedFormOfName' => 10,
        'i18n.%s.title' => 10,
        'i18n.%s.scopeAndContent' => 10,
        'i18n.%s.locationInformation' => 5,
        'i18n.%s.processingNotes' => 5,
        'i18n.%s.sourceOfAcquisition' => 5,
        'i18n.%s.archivalHistory' => 5);

      $fields = arElasticSearchPluginUtil::getI18nFieldNames(array(
        'donors.i18n.%s.authorizedFormOfName',
        'i18n.%s.title',
        'i18n.%s.scopeAndContent',
        'i18n.%s.locationInformation',
        'i18n.%s.processingNotes',
        'i18n.%s.sourceOfAcquisition',
        'i18n.%s.archivalHistory',
        'i18n.%s.appraisal',
        'i18n.%s.physicalCharacteristics',
        'i18n.%s.receivedExtentUnits',
        'creators.i18n.%s.authorizedFormOfName'), null, $boost);

      $fields[] = 'identifier^10';
      $fields[] = 'donors.contactInformations.contactPerson';

      $queryString->setFields($fields);

      $this->queryBool->addMust($queryString);

      $this->sortOptions['relevance'] = $this->context->i18n->__('Relevance');
    }

    // Set query
    $this->query->setQuery($this->queryBool);

    // Set order
    switch ($request->sort)
    {
      case 'identifier': // For backward compatibility
      case 'accessionNumber':
        $this->query->setSort(array('identifier' => 'asc'));

        break;

      case 'title':
      case 'alphabetic': // For backward compatibility
        $field = sprintf('i18n.%s.title.untouched', $this->context->user->getCulture());
        $this->query->addSort(array($field => 'asc'));

        break;

      case 'acquisitionDate':
        $this->query->addSort(array('date' => array('order' => 'asc', 'missing' => '_last')));

        break;

      case 'relevance':
        // Keep boost options
        break;

      case 'lastUpdated':
      default:
        $this->query->setSort(array('updatedAt' => 'desc'));

        break;
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitAccession')->search($this->query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
