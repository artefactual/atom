<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class SearchIndexAction extends sfAction
{
  public function execute($request)
  {
    $this->timer = new QubitTimer;

    if ('print' == $request->getGetParameter('media'))
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    // Simple search
    if (isset($request->query))
    {
      $this->title = $this->context->i18n->__('Search for [%1%]', array('%1%' => $request->query));
      $this->response->setTitle("{$this->title} - {$this->response->getTitle()}");
    }

    $query = $this->parseQuery();

    if (!empty($this->error))
    {
      return;
    }

    $query = $this->filterQuery($query);

    try
    {
      $hits = QubitSearch::getInstance()->getEngine()->getIndex()->find($query);
    }
    catch (Exception $e)
    {
      $this->error = $e->getMessage();

      return;
    }

    if (!empty($hits))
    {
      $this->pager = new QubitArrayPager;

      if ('print' != $request->getGetParameter('media'))
      {
        $this->pager->setMaxPerPage($request->limit);
      }
      else
      {
        $this->pager->setMaxPerPage(500); // force for print
      }

      $this->pager->hits = $hits;
      $this->pager->setPage($request->page);
    }
    else if (empty($this->error))
    {
      // no error, must be empty result set
      $this->error = $this->context->i18n->__('No results found.');
    }
  }

  public function parseQuery()
  {
    try
    {
      // Parse query string
      $queryParsed = QubitSearch::getInstance()->parse($this->request->query);
    }
    catch (Exception $e)
    {
      if (false !== strstr($e->getMessage(), 'fopen'))
      {
        throw $e;
      }

      $this->error = $e->getMessage();

      return null;
    }

    $query = new Zend_Search_Lucene_Search_Query_Boolean();
    $query->addSubquery($queryParsed, true);

    return $query;
  }

  public function filterQuery($query)
  {
    // Limit search to current culture and info. objects
    $query->addSubquery(QubitSearch::getInstance()->addTerm('QubitInformationObject', 'className'), true);
    $query->addSubquery(QubitSearch::getInstance()->addTerm($this->context->user->getCulture(), 'culture'), true);

    $query = QubitAcl::searchFilterByRepository($query, 'read');
    $query = QubitAcl::searchFilterDrafts($query);

    // Limit to a repository if in context
    if (isset($this->getRoute()->resource) && $this->getRoute()->resource instanceof QubitRepository)
    {
      $query->addSubquery(QubitSearch::getInstance()->addTerm($this->getRoute()->resource->id, 'repositoryId'), true);
      $this->title .= $this->context->i18n->__(' in %1%', array('%1%' => $this->getRoute()->resource->authorizedFormOfName));
    }

    return $query;
  }
}
