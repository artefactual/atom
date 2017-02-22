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
  public function execute($request)
  {
    if ('print' == $request->getGetParameter('media'))
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    // Get entity type name
    $this->type = $request->getGetParameter('type', 'informationObject');
    $allSlugs = $this->context->user->getClipboard()->getAllByClassName();

    // Get entity type class name
    $this->entityType = 'Qubit'.ucfirst($this->type);

    if (!isset($allSlugs[$this->entityType]) || !count($allSlugs[$this->entityType]))
    {
      $resultSet = new \Elastica\ResultSet(new Elastica\Response(null), new Elastica\Query);
    }
    else
    {
      parent::execute($request);

      $slugs = $allSlugs[$this->entityType];
      $this->search->queryBool->addMust(new \Elastica\Query\Terms('slug', $slugs));

      // Sort
      switch ($request->sort)
      {
        // Sort by highest ES score
        case 'relevance':
          $this->search->query->addSort(array('_score' => 'desc'));

          break;

        case 'identifier':
          $this->search->query->addSort(array('identifier' => 'asc'));

          break;

        case 'referenceCode':
          $this->search->query->addSort(array('referenceCode.untouched' => 'asc'));

          break;

        case 'alphabetic':
          $field = sprintf('i18n.%s.title.untouched', $this->selectedCulture);
          $this->search->query->addSort(array($field => 'asc'));

          break;

        case 'startDate':
          $this->search->query->setSort(array('dates.startDate' => 'asc'));

          break;

        case 'endDate':
          $this->search->query->setSort(array('dates.endDate' => 'desc'));

          break;

        case 'lastUpdated':
        default:
          $this->search->query->setSort(array('updatedAt' => 'desc'));
      }

      $this->search->query->setQuery($this->search->queryBool);

      // Filter drafts in case they were manually added to the clipboard
      QubitAclSearch::filterDrafts($this->search->filterBool);

      // Set filter
      if (0 < count($this->search->filterBool->toArray()))
      {
        $this->search->query->setPostFilter($this->search->filterBool);
      }

      $resultSet = QubitSearch::getInstance()->index->getType($this->entityType)->search($this->search->query);
    }

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($this->limit);
    $this->pager->init();

    $this->uiLabels = array(
      'informationObject' => sfConfig::get('app_ui_label_informationobject'),
      'actor'             => sfConfig::get('app_ui_label_actor'),
      'repository'        => sfConfig::get('app_ui_label_repository')
    );
  }
}
