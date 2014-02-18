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
    if (!$this->context->user->hasCredential(array('contributor', 'editor', 'administrator'), false))
    {
      QubitAcl::forwardUnauthorized();
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->sortOptions = array(
      'lastUpdated' => $this->context->i18n->__('Most recent'),
      'alphabetic' => $this->context->i18n->__('Alphabetic'));

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

    $this->query = new \Elastica\Query();
    $this->query->setLimit($request->limit);

    if (!empty($request->page))
    {
      $this->query->setFrom(($request->page - 1) * $request->limit);
    }

    $this->queryBool = new \Elastica\Query\Bool();

    if (1 === preg_match('/^[\s\t\r\n]*$/', $request->subquery))
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll());
    }
    else
    {
      $queryString = new \Elastica\Query\QueryString($request->subquery);

      // TODO: Add boost options to arElasticSearchPluginUtil::setAllFields function
      $queryString->setFields(array(
        'identifier^10',
        'donors.i18n.'.$culture.'.authorizedFormOfName^10',
        'i18n.'.$culture.'.title^10',
        'i18n.'.$culture.'.scopeAndContent^10',
        'i18n.'.$culture.'.locationInformation^5',
        'i18n.'.$culture.'.processingNotes^5',
        'i18n.'.$culture.'.sourceOfAcquisition^5',
        'i18n.'.$culture.'.archivalHistory^5',
        'i18n.'.$culture.'.appraisal',
        'i18n.'.$culture.'.physicalCharacteristics',
        'i18n.'.$culture.'.receivedExtentUnits',
        'donors.contactInformations.contactPerson',
        'creators.i18n.'.$culture.'.authorizedFormOfName'));

      $this->queryBool->addMust($queryString);

      $this->sortOptions['relevance'] = $this->context->i18n->__('Relevance');
    }

    // Set query
    $this->query->setQuery($this->queryBool);

    // Set order
    switch ($request->sort)
    {
      // I don't think that this is going to scale, but let's leave it for now
      case 'alphabetic':
        $this->query->setSort(array('identifier' => 'asc'));

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
