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
  public function execute($request)
  {
    parent::execute($request);

    if ('' == preg_replace('/[\s\t\r\n]*/', '', $request->subquery))
    {
      $this->queryBool->addMust(new Elastica_Query_MatchAll());
    }
    else
    {
      $queryText = new Elastica_Query_QueryString($request->subquery);
      $queryText->setDefaultOperator('AND');
      $queryText->setDefaultField(sprintf('i18n.%s.title', $this->context->user->getCulture()));

      $this->queryBool->addMust($queryText);
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

        break;

      case 'updatedUp':

        break;

      default:
        if ('alphabetic' == $this->sortSetting)
        {

        }
        else if ('lastUpdated' == $this->sortSetting)
        {

        }
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
