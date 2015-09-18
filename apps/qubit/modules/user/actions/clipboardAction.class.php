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
 * Show descriptions added to the user clipboard
 *
 * @package    AccesstoMemory
 * @subpackage user
 */
class UserClipboardAction extends DefaultBrowseAction
{
  const INDEX_TYPE = 'QubitInformationObject';

  public function execute($request)
  {
    if ('print' == $request->getGetParameter('media'))
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    $slugs = $this->context->user->getClipboard()->getAll();

    if (count($slugs) == 0)
    {
      $resultSet = new \Elastica\ResultSet(new Elastica\Response(null), new Elastica\Query);
    }
    else
    {
      parent::execute($request);

      $this->search->queryBool->addMust(new \Elastica\Query\Terms('slug', $slugs));

      // Sort
      switch ($request->sort)
      {
        case 'identifier':
          $this->search->query->addSort(array('referenceCode.untouched' => 'asc'));

        case 'lastUpdated':
          $this->search->query->setSort(array('updatedAt' => 'desc'));
          break;

        // I don't think that this is going to scale, but let's leave it for now
        case 'alphabetic':
        default:
          $field = sprintf('i18n.%s.title.untouched', $this->selectedCulture);
          $this->search->query->addSort(array($field => 'asc'));
      }

      $this->search->query->setQuery($this->search->queryBool);

      // Filter drafts in case they were manually added to the clipboard
      QubitAclSearch::filterDrafts($this->search->filterBool);

      // Set filter
      if (0 < count($this->search->filterBool->toArray()))
      {
        $this->search->query->setFilter($this->search->filterBool);
      }

      $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->query);
    }

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
