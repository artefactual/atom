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
 * Show paginated list of actors.
 *
 * @package    AccesstoMemory
 * @subpackage actor
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class ActorBrowseAction extends DefaultBrowseAction
{
  const INDEX_TYPE = 'QubitActor';

  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'languages' =>
        array('type' => 'term',
              'field' => 'i18n.languages',
              'size' => 10),
      'types' =>
        array('type' => 'term',
              'field' => 'entityTypeId',
              'size' => 10),
      'maintainingRepository' =>
        array('type' => 'term',
              'field' => 'maintainingRepositoryId',
              'size' => 10),
      'occupation' =>
        array('type' => 'term',
              'field' => 'occupations.id',
              'size' => 10));

  protected function populateFacet($name, $ids)
  {
    switch ($name)
    {
      case 'types':
      case 'occupation':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'maintainingRepository':
        $criteria = new Criteria;
        $criteria->add(QubitActor::ID, array_keys($ids), Criteria::IN);

        foreach (QubitActor::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getAuthorizedFormOfName(array('cultureFallback' => true));
        }

        break;

      default:
        parent::populateFacet($name, $ids);
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if (isset($request->repos) && ctype_digit($request->repos))
    {
      $this->repos = QubitRepository::getById($request->repos);

      // Add repo to the user session as realm
      $this->context->user->setAttribute('search-realm', $request->repos);
    }
    else if (sfConfig::get('app_enable_institutional_scoping'))
    {
      // Remove search realm
      $this->context->user->removeAttribute('search-realm');
    }

    if (1 === preg_match('/^[\s\t\r\n]*$/', $request->subquery))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\MatchAll());
    }
    else
    {
      $queryText = new \Elastica\Query\QueryString(arElasticSearchPluginUtil::escapeTerm($request->subquery));
      $queryText->setDefaultOperator('OR');
      arElasticSearchPluginUtil::setFields($queryText, 'actor');

      $this->search->queryBool->addMust($queryText);
    }

    $this->search->query = QubitAclSearch::filterByResource($this->search->query, QubitActor::getById(QubitActor::ROOT_ID));

    switch ($request->sort)
    {
      // I don't think that this is going to scale, but let's leave it for now
      case 'alphabetic':
        $field = sprintf('i18n.%s.authorizedFormOfName.untouched', $this->selectedCulture);
        $this->search->query->setSort(array($field => 'asc'));

        break;

      case 'identifier':
        $this->search->query->setSort(array('descriptionIdentifier' => 'asc'));

        break;

      case 'lastUpdated':
      default:
        $this->search->query->setSort(array('updatedAt' => 'desc'));
    }

    $this->search->query->setQuery($this->search->queryBool);

    // Set filter
    if (0 < count($this->search->filterBool->toArray()))
    {
      $this->search->query->setPostFilter($this->search->filterBool);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitActor')->search($this->search->query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($this->limit);
    $this->pager->init();

    $this->populateFacets($resultSet);
  }
}
