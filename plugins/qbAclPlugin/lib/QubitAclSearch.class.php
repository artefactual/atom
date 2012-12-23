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
 * instances of the Elastica_Query class and they will be filtered using
 * Elastica_Filter. Filters can be much faster compared to queries since they
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
   * @param  Elastica_Query $query Search query object
   * @param  string         $action Action
   * @return Elastica_Query Filtered query
   */
  public static function searchFilterByRepository(Elastica_Query $query, $action)
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
            // TODO $query->addSubquery(QubitSearch::getInstance()->addTerm($repo['id'], 'repositoryId'), true);
          }
          else
          {
            // Prohibit specified repos (all others allowed)
            // TODO $query->addSubquery(QubitSearch::getInstance()->addTerm($repo['id'], 'repositoryId'), false);
          }
        }
      }
    }

    return $query;
  }

  /**
   * Filter search query by resource specific ACL
   *
   * @param  Elastica_Query $query Search query object
   * @param  mixed          $root Root object for list
   * @return Elastica_Query Filtered query
   */
  public static function searchFilterByResource(Elastica_Query $query, $root)
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

      while ($resourceId = array_shift($allows))
      {
        // TODO $query->addSubquery(QubitSearch::getInstance()->addTerm($resourceId, 'id'), true);
      }
    }

    // Otherwise, build a list of banned resources
    else
    {
      $bans = array_keys($resourceAccess, false, true);

      while ($resourceId = array_shift($bans))
      {
        // TODO $query->addSubquery(QubitSearch::getInstance()->addTerm($resourceId, 'id'), false);
      }
    }

    return $query;
  }

  /**
   * Filter search query by resource specific ACL
   *
   * @param  Elastica_Query $query Search query object
   * @return Elastica_Query Filtered query
   */
  public static function searchFilterDrafts(Elastica_Query $query)
  {
    // Filter out 'draft' items by repository
    $repositoryViewDrafts = QubitAcl::getRepositoryAccess('viewDraft');
    if (1 == count($repositoryViewDrafts))
    {
      if (QubitAcl::DENY == $repositoryViewDrafts[0]['access'])
      {
        // Don't show *any* draft info objects
        // TODO $query->addSubquery(QubitSearch::getInstance()->addTerm(QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID, 'publicationStatusId'), true);
      }
    }
    else
    {
      // Get last rule in list, it will be the global rule with the opposite
      // access of the preceeding rules (e.g. if last rule is "DENY ALL" then
      // preceeding rules will be "ALLOW" rules)
      $globalRule = array_pop($repositoryViewDrafts);

      // If global rule is GRANT, then listed repos are exceptions so remove
      // from results
      if (QubitAcl::GRANT == $globalRule['access'])
      {
        while ($repo = array_shift($repositoryViewDrafts))
        {
          // TODO $query->addSubquery(QubitSearch::getInstance()->addTerm($repo['id'], 'repositoryId'), true);
          // TODO $query->addSubquery(QubitSearch::getInstance()->addTerm(QubitTerm::PUBLICATION_STATUS_DRAFT_ID, 'publicationStatusId'), true);
        }
      }

      // If global rule is DENY, then only show the listed repo drafts
      else
      {
        while ($repo = array_shift($repositoryViewDrafts))
        {
          // TODO $query->addSubquery(QubitSearch::getInstance()->addTerm($repo['id'], 'repositoryId'), true);
        }

        // Filter rule should look like "+(id:(356 357 358) status:published)"
        // TODO $query->addSubquery(QubitSearch::getInstance()->addTerm(QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID, 'publicationStatusId'), null);
      }
    }

    return $query;
  }
}
