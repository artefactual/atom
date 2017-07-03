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
      $resultSet = new \Elastica\ResultSet(new Elastica\Response(null), new Elastica\Query, array());
    }
    else
    {
      parent::execute($request);

      $slugs = $allSlugs[$this->entityType];
      $this->search->queryBool->addMust(new \Elastica\Query\Terms('slug', $slugs));
      $this->setSortOptions();
      $this->setESSort($request);

      if ('QubitInformationObject' == $this->entityType)
      {
        QubitAclSearch::filterDrafts($this->search->queryBool);
      }

      $this->search->query->setQuery($this->search->queryBool);

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

  /**
   * Set available sorting options based on entity type.
   */
  private function setSortOptions()
  {
    $this->sortOptions = array(
      'lastUpdated' => $this->context->i18n->__('Most recent'),
      'alphabetic'  => $this->context->i18n->__('Alphabetic'),
      'relevance'   => $this->context->i18n->__('Relevance'),
    );

    // IOs and Repos have identifier sort option in common
    if (in_array($this->entityType, array('QubitInformationObject', 'QubitRepository')))
    {
      $this->sortOptions['identifier'] = $this->context->i18n->__('Identifier');
    }

    // IO specific sort options
    if ('QubitInformationObject' === $this->entityType)
    {
      $this->sortOptions['referenceCode'] = $this->context->i18n->__('Reference code');
      $this->sortOptions['startDate'] = $this->context->i18n->__('Start date');
      $this->sortOptions['endDate'] = $this->context->i18n->__('End date');
    }
  }

  /**
   * Set which field to sort by for current ES query.
   *
   * @param sfRequest $request  Current request object.
   */
  private function setESSort($request)
  {
    // Prevent selecting an inappropriate sort field when switching entity types.
    // e.g.: if we are sorting by start date for archival descriptions, but switch to auth recs we
    // will default to sort by relevance since authority records don't have start dates to sort over.
    $request->sort = isset($this->sortOptions[$request->sort]) ? $request->sort : 'relevance';

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

      // Sort by title if information object, go with authorized form of name if repository / actor
      case 'alphabetic':
        $fieldName = 'QubitInformationObject' === $this->entityType ? 'title' : 'authorizedFormOfName';
        $field = sprintf('i18n.%s.%s.untouched', $this->selectedCulture, $fieldName);
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
  }
}
