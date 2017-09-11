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
 * Filter search query objects based in the access lists. The queries are
 * instances of the \Elastica\Query class.
 *
 * @package    AccesstoMemory
 * @subpackage qbAclPlugin
 */
class QubitAclSearch
{
  /**
   * Filter search query by repository
   *
   * @param  \Elastica\Query $query Search query object
   * @param  string         $action Action
   * @return \Elastica\Query Filtered query
   */
  public static function filterByRepository(\Elastica\Query $query, $action)
  {
    $repositoryAccess = QubitAcl::getRepositoryAccess($action);
    if (1 == count($repositoryAccess))
    {
      // If all repositories are denied access, re-route user to login
      if (QubitAcl::DENY == $repositoryAccess[0]['access'])
      {
        QubitAcl::forwardUnauthorized();
      }
    }
    else
    {
      while ($repo = array_shift($repositoryAccess))
      {
        if ('*' == $repo['id'])
        {
          $queryBool = new \Elastica\Query\BoolQuery;

          $query = new \Elastica\Query\Term;
          $query->setTerm('repositoryId', $repo['id']);
          
          if (QubitAcl::DENY == $repo['access'])
          {
            // Require repos to be specifically allowed (all others prohibited)
            // (ZSL) $query->addSubquery(QubitSearch::getInstance()->addTerm($repo['id'], 'repositoryId'), true);
            $queryBool->addMust($query);
          }
          else
          {
            // Prohibit specified repos (all others allowed)
            // (ZSL) $query->addSubquery(QubitSearch::getInstance()->addTerm($repo['id'], 'repositoryId'), false);
            $queryBool->addMustNot($query);
          }

          $query->setPostFilter($queryBool);
        }
      }
    }

    return $query;
  }

  /**
   * Filter search query by resource specific ACL
   *
   * @param  \Elastica\Query $query Search query object
   * @param  mixed          $root Root object for list
   * @return \Elastica\Query Filtered query
   */
  public static function filterByResource(\Elastica\Query $query, $root)
  {
    $user = sfContext::getInstance()->user;

    $permissions = QubitAcl::getUserPermissionsByAction($user, get_class($root), 'read');

    // Build access control list
    $grants = 0;
    if (0 < count($permissions))
    {
      foreach ($permissions as $permission)
      {
        if (!isset($resourceAccess[$permission->objectId]))
        {
          $resourceAccess[$permission->objectId] = QubitAcl::isAllowed($user, $permission->objectId, 'read');

          if ($resourceAccess[$permission->objectId])
          {
            $grants++;
          }
        }
      }
    }

    // If no grants then user can't see anything
    if (0 == $grants)
    {
      QubitAcl::forwardUnauthorized();
    }

    // If global deny is default, then list allowed resources
    else if (!QubitAcl::isAllowed($user, $root->id, 'read'))
    {
      $allows = array_keys($resourceAccess, true, true);

      $ids = array();
      while ($resourceId = array_shift($allows))
      {
        // (ZSL) $query->addSubquery(QubitSearch::getInstance()->addTerm($resourceId, 'id'), true);
        $ids[] = $resourceId;
      }

      if (0 < count($ids))
      {
        $queryIds = new \Elastica\Query\Ids;
        $queryIds->setIds($ids);

        $query->setPostFilter($queryIds);
      }
    }

    // Otherwise, build a list of banned resources
    else
    {
      $bans = array_keys($resourceAccess, false, true);

      $ids = array();
      while ($resourceId = array_shift($bans))
      {
        // (ZSL) $query->addSubquery(QubitSearch::getInstance()->addTerm($resourceId, 'id'), false);
        $ids[] = $resourceId;
      }

      if (0 < count($ids))
      {
        $queryIds = new \Elastica\Query\Ids;
        $queryIds->setIds($ids);

        $queryBool = new \Elastica\Query\BoolQuery;
        $queryBool->addMustNot($ids);

        $query->setPostFilter($queryIds);
      }
    }

    return $query;
  }

  /**
   * Filter search query by resource specific ACL
   *
   * @param  \Elastica\Query\BoolQuery $queryBool Search query object
   */
  public static function filterDrafts(\Elastica\Query\BoolQuery $queryBool)
  {
    // Filter out 'draft' items by repository
    $repositoryViewDrafts = QubitAcl::getRepositoryAccess('viewDraft');
    if (1 == count($repositoryViewDrafts))
    {
      if (QubitAcl::DENY == $repositoryViewDrafts[0]['access'])
      {
        // Don't show *any* draft info objects
        $query = new \Elastica\Query\Term();
        $query->setTerm('publicationStatusId', QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID);

        $queryBool->addMust($query);
      }
    }
    else
    {
      // Get last rule in list, it will be the global rule with the opposite
      // access of the preceeding rules (e.g. if last rule is "DENY ALL" then
      // preceeding rules will be "ALLOW" rules)
      $globalRule = array_pop($repositoryViewDrafts);

      $query = new \Elastica\Query\BoolQuery;

      while ($repo = array_shift($repositoryViewDrafts))
      {
        $query->addShould(new \Elastica\Query\Term(array('repository.id' => (int)$repo['id'])));
      }

      $query->addShould(new \Elastica\Query\Term(array('publicationStatusId' => QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)));

      // Does this ever happen in AtoM?
      if ($globalRule['access'] == QubitAcl::GRANT)
      {
        $queryBool->addMustNot($query);
      }
      else
      {
        $queryBool->addMust($query);
      }
    }
  }
}
