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

    if (!isset($request->page))
    {
      $request->page = 1;
    }

    // Avoid pagination over 10000 records
    if ((int)$request->limit * (int)$request->page > 10000)
    {
      // Show alert
      $message = $this->context->i18n->__("We've redirected you to the first page of results. To avoid using vast amounts of memory, AtoM limits pagination to 10,000 records. To view the last records in the current result set, try changing the sort direction.");
      $this->getUser()->setFlash('notice', $message);

      // Redirect to fist page
      $params = $request->getParameterHolder()->getAll();
      unset($params['page']);
      $this->redirect($params);
    }

    $this->sortOptions = array(
      'lastUpdated' => $this->context->i18n->__('Date modified'),
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

    // Default sort direction
    $sortDir = 'asc';
    if ($request->sort == 'lastUpdated')
    {
      $sortDir = 'desc';
    }

    // Set default sort direction in request if not present or not valid
    if (!isset($request->sortDir) || !in_array($request->sortDir, array('asc', 'desc')))
    {
      $request->sortDir = $sortDir;
    }

    $culture = $this->context->user->getCulture();

    $this->query = new \Elastica\Query;
    $this->query->setSize($request->limit);
    $this->query->setFrom(($request->page - 1) * $request->limit);

    $this->queryBool = new \Elastica\Query\BoolQuery;

    if (1 === preg_match('/^[\s\t\r\n]*$/', $request->subquery))
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll);
    }
    else
    {
      $queryString = new \Elastica\Query\QueryString(arElasticSearchPluginUtil::escapeTerm($request->subquery));
      $queryString->setDefaultOperator('AND');

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
        'alternativeIdentifiers.i18n.%s.name',
        'creators.i18n.%s.authorizedFormOfName',
        'alternativeIdentifiers.i18n.%s.note',
        'alternativeIdentifiers.type.i18n.%s.name'), null, $boost);

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
        $this->query->setSort(array('identifier.untouched' => $request->sortDir));

        break;

      case 'title':
      case 'alphabetic': // For backward compatibility
        $field = sprintf('i18n.%s.title.alphasort', $this->context->user->getCulture());
        $this->query->addSort(array($field => $request->sortDir));

        break;

      case 'acquisitionDate':
        $this->query->addSort(array('date' => array('order' => $request->sortDir, 'missing' => '_last')));

        break;

      case 'relevance':
        // Keep boost options
        break;

      case 'lastUpdated':
      default:
        $this->query->setSort(array('updatedAt' => $request->sortDir));

        break;
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitAccession')->search($this->query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
