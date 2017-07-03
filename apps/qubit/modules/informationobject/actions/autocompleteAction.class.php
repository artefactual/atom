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
 */
class InformationObjectAutocompleteAction extends sfAction
{
  /**
   * Return all information objects (not just top-level) for ajax request
   */
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $culture = $this->context->user->getCulture();

    $this->query = new \Elastica\Query();
    $this->query->setSize($request->limit);
    $this->query->setSort(array(
      'levelOfDescriptionId' => 'asc',
      'identifier' => 'asc',
      'i18n.'.$culture.'.title.untouched' => 'asc'));

    $this->queryBool = new \Elastica\Query\BoolQuery;

    if (1 === preg_match('/^[\s\t\r\n]*$/', $request->query))
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll);
    }
    else
    {
      $queryString = new \Elastica\Query\QueryString('*'.arElasticSearchPluginUtil::escapeTerm($request->query).'*');

      // Search for referenceCode or identifier, and title
      if (1 == sfConfig::get('app_inherit_code_informationobject', 1))
      {
        $queryString->setFields(array('i18n.'.$culture.'.title.autocomplete', 'referenceCode.autocomplete'));

        // Change sort order
        $this->query->setSort(array(
          'levelOfDescriptionId' => 'asc',
          'referenceCode.untouched' => 'asc',
          'i18n.'.$culture.'.title.untouched' => 'asc'));
      }
      else
      {
        $queryString->setFields(array('i18n.'.$culture.'.title.autocomplete', 'identifier'));
      }

      $this->queryBool->addMust($queryString);
    }

    // Filter results by parent
    if (!empty($request->parent) && ctype_digit($request->parent))
    {
      $queryTerm = new \Elastica\Query\Term;
      $queryTerm->setTerm('parentId', $request->parent);

      $this->queryBool->addMust($queryTerm);
    }

    // Filter results by repository
    if (!empty($request->repository) && ctype_digit($request->repository))
    {
      $queryTerm = new \Elastica\Query\Term;
      $queryTerm->setTerm('repository.id', $request->repository);

      $this->queryBool->addMust($queryTerm);
    }

    // Filter drafts
    if (isset($request->filterDrafts) && $request->filterDrafts)
    {
      QubitAclSearch::filterDrafts($this->queryBool);
    }

    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
