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
 * instances of the \Elastica\Query class and they will be filtered using
 * \Elastica\Filter. Filters can be much faster compared to queries since they
 * don’t perform any scoring, especially when they are cached.
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
          if (QubitAcl::DENY == $repo['access'])
          {
            // Require repos to be specifically allowed (all others prohibited)
            // (ZSL) $query->addSubquery(QubitSearch::getInstance()->addTerm($repo['id'], 'repositoryId'), true);
            $filter = new \Elastica\Filter\Term;
            $filter->setTerm('repositoryId', $repo['id']);
          }
          else
          {
            // Prohibit specified repos (all others allowed)
            // (ZSL) $query->addSubquery(QubitSearch::getInstance()->addTerm($repo['id'], 'repositoryId'), false);
            $filterTerm = new \Elastica\Filter\Term;
            $filterTerm->setTerm('repositoryId', $repo['id']);
            $filter = new \Elastica\Filter\Not($filterTerm);
          }

          $query->setFilter($filter);
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
      self::forwardUnauthorized();
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
        $filter = new \Elastica\Filter\Ids;
        $filter->setIds($ids);
        $query->setFilter($filter);
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
        $filterIds = new \Elastica\Filter\Ids;
        $filterIds->setIds($ids);
        $filter = new \Elastica\Filter\Not($filterIds);

        $query->setFilter($filter);
      }
    }

    return $query;
  }

  /**
   * Filter search query by resource ES ACL entries.
   *
   * @param  \Elastica\Filter\Bool $filterBool Search query object
   */
  public static function filterDraftsByES(&$filterBool, $action)
  {
    // Merge this with filterDrafts?
    $userId = sfContext::getInstance()->getUser()->getUserID();
    $groupIds = sfContext::getInstance()->getUser()->getGroupIds();

    // Set Acl rule filters if user isn't an administrator
    if (!in_array(QubitAclGroup::ADMINISTRATOR_ID, $groupIds))
    {
      if ($userId)
      {
        $filterBool->addShould(new \Elastica\Filter\Term(array("$action.userIds" => array($userId))));
      }

      foreach ($groupIds as $groupId)
      {
        $filterBool->addShould(new \Elastica\Filter\Term(array("$action.groupIds" => $groupId)));
      }
    }
  }

  /**
   * Check ES for whether or not the current user has access to a specific
   * action.
   *
   * @param  int $id The id of the ES document
   * @param  string $action the action to check again, e.g. aclViewThumb, aclViewDraft
   */
  public static function check($id, $action)
  {
    $docType = 'QubitInformationObject';

    $userId = sfContext::getInstance()->getUser()->getUserID();
    $groupIds = sfContext::getInstance()->getUser()->getGroupIds();

    $query = new \Elastica\Query\Bool;
    $query->addMust(new \Elastica\Query\Term(array('_id' => $id)));

    $results = QubitSearch::getInstance()->index->getType($docType)->search($query)->getResults();
    if (!count($results))
    {
      return false;
    }

    // Should only ever have 1 element since _id corresponds to
    // our information_object.id which is unique.
    $doc = $results[0]->getData();

    if (!isset($doc[$action]))
    {
      throw new sfException("ElasticSearch ACL rules requested on a $docType (id: $id) document not found.");
    }

    $allowedUserIds = $doc[$action]['userIds'];
    $allowedGroupIds = $doc[$action]['groupIds'];

    if (in_array($userId, $allowedUserIds))
    {
      return true;
    }

    foreach ($groupIds as $groupId)
    {
      if (in_array($groupId, $allowedGroupIds))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Filter search query by resource specific ACL
   *
   * @param  \Elastica\Filter\Bool $filterBool Search query object
   */
  public static function filterDrafts(\Elastica\Filter\Bool $filterBool)
  {
    // Filter out 'draft' items by repository
    $repositoryViewDrafts = QubitAcl::getRepositoryAccess('viewDraft');
    if (1 == count($repositoryViewDrafts))
    {
      if (QubitAcl::DENY == $repositoryViewDrafts[0]['access'])
      {
        // Don't show *any* draft info objects
        $filter = new \Elastica\Filter\Term();
        $filter->setTerm('publicationStatusId', QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID);

        $filterBool->addMust($filter);
      }
    }
    else
    {
      // Get last rule in list, it will be the global rule with the opposite
      // access of the preceeding rules (e.g. if last rule is "DENY ALL" then
      // preceeding rules will be "ALLOW" rules)
      $globalRule = array_pop($repositoryViewDrafts);

      $filter = new \Elastica\Filter\Bool();

      while ($repo = array_shift($repositoryViewDrafts))
      {
        $filter->addShould(new \Elastica\Filter\Term(array('repository.id' => (int)$repo['id'])));
      }

      $filter->addShould(new \Elastica\Filter\Term(array('publicationStatusId' => QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)));

      // Does this ever happen in AtoM?
      if ($globalRule['access'] == QubitAcl::GRANT)
      {
        $filter = new \Elastica\Filter\BoolNot($filter);
      }

      $filterBool->addMust($filter);
    }
  }
}
