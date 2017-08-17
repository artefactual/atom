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
 * Access Control List (ACL) functionality
 *
 * @package    AccesstoMemory
 * @subpackage qbAclPlugin
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitAcl
{
  const GRANT = 2;
  const INHERIT = 1;
  const DENY  = 0;

  public static $ACTIONS = array(
    'read' => 'Read',
    'create' => 'Create',
    'update' => 'Update',
    'delete' => 'Delete',
    'translate' => 'Translate'
  );

  protected static $_instance;

  protected
    $_roles = array(),
    $_resources = array(),
    $_user;

  public
    $acl;

  public function __construct($user = null)
  {
    if (null === $user)
    {
      $this->_user = sfContext::getInstance()->user;
    }
    else
    {
      $this->_user = $user;
    }

    $this->acl = new Zend_Acl;
    $this->buildUserRoleList($this->_user);
  }

  public static function getInstance()
  {
    if (null === self::$_instance)
    {
      self::$_instance = new self();
    }

    return self::$_instance;
  }

  /**
   * Each request is completely unique in PHP however our workers run in a loop
   * where state is held across jobs. This class was built as a singleton so we
   * need this extra method to void the instance when needed when running ACL
   * checks from a worker process.
   *
   */
  public static function destruct()
  {
    self::$_instance = null;
  }

  /**
   * Test user access to the given resource
   *
   * Note: Current sf_user is assumed, but can be overridden with
   * $options['userId'].
   *
   * @param mixed   $resource object to which user is requesting access
   * @param integer $actions requested action key
   * @param array   $options optional parameters
   */
  public static function check($resource, $actions, $options = array())
  {
    if ($resource instanceof sfOutputEscaper)
    {
      $resource = sfOutputEscaper::unescape($resource);
    }

    $hasAccess = false;

    // Skip ACL checks for command-line tasks
    if ('cli' == sfContext::getInstance()->getConfiguration()->getEnvironment())
    {
      return true;
    }

    if (!is_array($actions))
    {
      $actions = array($actions);
    }

    $user = sfContext::getInstance()->user;
    if (isset($options['user']))
    {
      $user = $options['user'];
    }

    // Loop through actions and return on first "true" result (OR condition)
    while ($action = array_shift($actions))
    {
      // Short-circuit decision tree for 'translate', because we want
      // translate permissions to apply system-wide
      //
      // TODO: Get rid of this when we are using ACL system-wide
      //
      if ('translate' == $action)
      {
        if (self::isAllowed($user, $resource, 'translate', $options))
        {
          return true;
        }
        else
        {
          continue;
        }
      }

      // OR condition, first "true" result returns
      if ($hasAccess = self::checkAccessByClass($resource, $user, $action, $options))
      {
        return $hasAccess;
      }
    }

    return $hasAccess;
  }

  /*
   * Check if the current user has access to this resource, based on
   * class specific rules. This is a helper function to QubitAcl::check().
   *
   * @param $resource  The current resource we're checking user access against.
   * @param $user  The current user
   * @param $action  Which permissions action we're checking
   * @param $options  Access check options.
   */
  private static function checkAccessByClass($resource, $user, $action, $options)
  {
    switch (get_class($resource))
    {
      // Allow access to editors and administrators
      case 'QubitAccession':
      case 'QubitDeaccession':
      case 'QubitDonor':
      case 'QubitFunction':
      case 'QubitRightsHolder':
        $hasAccess = $user->isAuthenticated() && ($user->hasGroup(QubitAclGroup::ADMINISTRATOR_ID) ||
                     $user->hasGroup(QubitAclGroup::EDITOR_ID));
        break;

      // Administrator only
      case 'QubitUser':
      case 'QubitMenu':
      case 'QubitStaticPage':
      case 'QubitAclGroup':
      case 'QubitAclUser':
        $hasAccess = $user->hasGroup(QubitAclGroup::ADMINISTRATOR_ID);
        break;

      // Rely on ACL for authorization
      // TODO Switch *all* authorization to ACL
      default:
        $hasAccess = self::isAllowed($user, $resource, $action, $options);
    }

    return $hasAccess;
  }

  public static function isAllowed($role, $resource, $action, $options = array())
  {
    if (!isset(class_implements($role)['Zend_Acl_Role_Interface']))
    {
      self::getInstance()->addRole($role);
    }

    // If attempting to read a draft information object, check viewDraft permission
    if ('read' == $action && $resource instanceOf QubitInformationObject)
    {
      if (null === $resource->getPublicationStatus())
      {
        throw new sfException('No publication status set for information object id: '.$resource->id);
      }

      if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $resource->getPublicationStatus()->statusId)
      {
        $instance = self::getInstance()->buildAcl($resource, $options);

        return ($instance->acl->isAllowed($role, $resource, 'read') && $instance->acl->isAllowed($role, $resource, 'viewDraft'));
      }
    }

    // If resource is a new object (no id yet) figure out if we should test
    // authorization against parent (e.g. creating a new resource)
    if (is_object($resource) && !isset($resource->id))
    {
      if (!isset($resource->parentId))
      {
        return false;
      }

      if ('create' == $action)
      {
        // For create action always check permissions against parent
        $resource = $resource->parent;
      }
      else if ($resource instanceOf QubitInformationObject)
      {
        // Special rules for information object
        $resource = QubitInformationObjectAcl::getParentForIsAllowed($resource, $action);
      }

      // If we still don't have a valid resource id, then deny access
      if (!isset($resource) || !isset($resource->id))
      {
        return false;
      }
    }

    // HACKS for limiting term permissions by taxonomy
    if ($resource instanceOf QubitTaxonomy && 'createTerm' == $action)
    {
      $term = clone QubitTerm::getById(QubitTerm::ROOT_ID);
      $term->taxonomyId = $resource->id;
      $action = 'create';

      $resource = $term;
    }
    else if ($resource instanceOf QubitTerm && array_key_exists('taxonomyId', $options))
    {
      // Create clone resource that we can assign to an arbitrary taxonomy
      $resource = clone $resource;
      $resource->taxonomyId = $options['taxonomyId'];
    }

    self::getInstance()->buildAcl($resource, $options);

    return self::getInstance()->acl->isAllowed($role, $resource, $action);
  }

  protected function addRole($role)
  {
    if (is_object($role))
    {
      if (!in_array($role->id, $this->_roles))
      {
        foreach ($role->ancestors->andSelf()->orderBy('lft') as $ancestor)
        {
          if (!in_array($ancestor->id, $this->_roles))
          {
            $this->acl->addRole($ancestor, $ancestor->parentId);
          }
        }
      }
    }
    else if (!in_array($role, $this->_roles))
    {
      $this->acl->addRole($role);
    }

    return $this;
  }

  protected function buildUserRoleList($user)
  {
    // Don't add user twice
    if (in_array($user->getUserID(), $this->_roles))
    {
      return $this;
    }

    $parents = array(); // Immediate parents of user role

    if ($user->isAuthenticated())
    {
      // Add authenticated group
      $this->acl->addRole(QubitAclGroup::getById(QubitAclGroup::AUTHENTICATED_ID));
      $this->_roles[] = QubitAclGroup::AUTHENTICATED_ID;

      // Add groups (if user belongs to any)
      if (0 < count($aclUserGroups = $user->user->getAclUserGroups()))
      {
        foreach ($aclUserGroups as $aclUserGroup)
        {
          $aclGroup = $aclUserGroup->group;
          $this->acl->addRole($aclGroup, $aclGroup->parent);
          $this->_roles[] = $aclGroup->id;
          $parents[] = $aclGroup->id;
        }
      }
      else
      {
        $parents = QubitAclGroup::AUTHENTICATED_ID;
      }

      // Add user role
      $this->acl->addRole($user->getUserID(), $parents);
      $this->_roles[] = $user->getUserID();
    }
    else
    {
      // Add anonymous role
      $this->acl->addRole(QubitAclGroup::getById(QubitAclGroup::ANONYMOUS_ID));
      $this->_roles[] = QubitAclGroup::ANONYMOUS_ID;
    }

    return $this;
  }

  protected function buildResourceList($resource, $options = array())
  {
    $resourceId = (is_object($resource)) ? $resource->id : $resource;

    // Don't add same resource twice
    if (in_array($resourceId, $this->_resources))
    {
      return $this;
    }

    // Add resource hierarchy
    if (is_object($resource) && 0 < count($resources = $resource->ancestors->andSelf()->orderBy('lft')))
    {
      foreach ($resources as $r)
      {
        if (!in_array($r->id, $this->_resources))
        {
          $this->acl->addResource($r->id, $r->parentId);
          $this->_resources[] = $r->id;
        }
      }
    }
    else
    {
      $this->acl->addResource($resource);
      $this->_resources[] = $resourceId;
    }

    return $this;
  }

  protected function buildAcl($resource, $options = array())
  {
    $resources = $this->_resources;

    if (null !== $resource)
    {
      $this->buildResourceList($resource, $options);
    }
    else if (!isset($this->resources['null']))
    {
      // Still test ACL against 'null' resource (requires permissions on null)
      $this->_resources['null'] = null;
    }

    // Only add permissions for resources that have not already been added
    $newResources = array_diff($this->_resources, $resources);
    if (array() === $newResources)
    {
      return $this;
    }

    // Add all permissions related to the current roles and resources
    $criteria = new Criteria;
    $c1 = $criteria->getNewCriterion(QubitAclPermission::GROUP_ID, $this->_roles, Criteria::IN);
    if ($this->_user->isAuthenticated())
    {
      $c2 = $criteria->getNewCriterion(QubitAclPermission::USER_ID, $this->_user->getUserID());
      $c1->addOr($c2);
    }
    $c3 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, $newResources, Criteria::IN);
    $c4 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
    $c3->addOr($c4);
    $c1->addAnd($c3);
    $criteria->add($c1);

    if (0 < count($permissions = QubitAclPermission::get($criteria)))
    {
      foreach ($permissions as $permission)
      {
        $aclMethod = (1 == $permission->grantDeny) ? 'allow' : 'deny';

        if (isset($permission->userId))
        {
          // Ignore permission belonging to other users, refs #7817
          if ($permission->userId != $this->_user->getUserID())
          {
            continue;
          }

          $roleId = $permission->userId;
        }
        else
        {
          $roleId = $permission->groupId;
        }

        /* Debugging
        var_dump('id:', $permission->id, 'access:', $aclMethod, 'role:', $roleId, 'resource:', $permission->objectId, 'action:', $permission->action);
        echo '<br>';
        */

        // Test assertion for translate, update and any permission with a conditional
        if (
          null != $permission->conditional || in_array($permission->action, array('update', 'translate')))
        {
          call_user_func_array(array($this->acl, $aclMethod), array(
            $roleId,
            $permission->objectId,
            $permission->action,
            new QubitAclConditionalAssert($permission)
          ));
        }
        else
        {
          call_user_func_array(array($this->acl, $aclMethod), array(
            $roleId,
            $permission->objectId,
            $permission->action)
          );
        }
      }
    }

    return $this;
  }

  /**
   * Add permissions to repository access array
   *
   * @param $repositoryAccess input access array
   * @param $permissions QubitQuery permission list
   * @return array output access array
   */
  public static function addRepositoryAccess($repositoryAccess, $permissions)
  {
    foreach ($permissions as $permission)
    {
      if (null === $permission->grantDeny)
      {
        continue;
      }

      $access = (1 == $permission->grantDeny) ? self::GRANT : self::DENY;

      // If no repository specified, then apply rule to all repositories
      if (null === ($repository = $permission->getRepository()))
      {
        $repositoryAccess[] = array('id' => '*', 'access' => $access);
        break;
      }

      // Add repository access if there is no pre-existing rule for that repo
      else
      {
        $preExistingRule = false;
        foreach ($repositoryAccess as $rule)
        {
          if ($repository->id == $rule['id'])
          {
            $preExistingRule = true;
            break;
          }
        }

        if (!$preExistingRule)
        {
          $repositoryAccess[] = array('id' => $repository->id, 'access' => $access);
        }
      }
    }

    return $repositoryAccess;
  }

  /**
   * List the repository access rules for the current user
   *
   * @param $action integer Access privilige being requested
   * @param $options array optional parameters
   * @return array
   */
  public static function getRepositoryAccess($action, $options = array())
  {
    $repositoryAccess = array();
    $userGroupIds = array();

    // If user is logged in
    if (sfContext::getInstance()->user->isAuthenticated())
    {
      $userId = sfContext::getInstance()->user->getUserID();

      // Test user permissions
      $criteria = new Criteria;
      $criteria->add(QubitAclPermission::USER_ID, $userId);

      // "Null" action == all actions
      $c1 = $criteria->getNewCriterion(QubitAclPermission::ACTION, $action);
      $c2 = $criteria->getNewCriterion(QubitAclPermission::ACTION, null, Criteria::ISNULL);
      $c1->addOr($c2);
      $c3 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, QubitInformationObject::ROOT_ID);
      $c4 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
      $c3->addOr($c4);
      $c1->addAnd($c3);
      $criteria->add($c1);
      $criteria->addDescendingOrderByColumn(QubitAclPermission::ID);

      if (0 < count($permissions = QubitAclPermission::get($criteria)))
      {
        $repositoryAccess = self::addRepositoryAccess($repositoryAccess, $permissions);
      }
    }
    else
    {
      // Add anonymous group if user is not logged in
      $userGroupIds[] = QubitAclGroup::ANONYMOUS_ID;
    }

    if (0 == count($repositoryAccess) || '*' != $repositoryAccess[count($repositoryAccess) - 1]['id'])
    {
      // Test user group permissions
      foreach (sfContext::getInstance()->user->listGroups() as $group)
      {
        $userGroupIds[] = $group->id;
      }

      $criteria = new Criteria;
      $criteria->add(QubitAclPermission::GROUP_ID, $userGroupIds, Criteria::IN);
      $c1 = $criteria->getNewCriterion(QubitAclPermission::ACTION, $action);
      $c2 = $criteria->getNewCriterion(QubitAclPermission::ACTION, null, Criteria::ISNULL);
      $c1->addOr($c2);
      $c3 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, QubitInformationObject::ROOT_ID);
      $c4 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
      $c3->addOr($c4);
      $c1->addAnd($c3);
      $criteria->add($c1);
      $criteria->addDescendingOrderByColumn(QubitAclPermission::ID);

      if (0 < count($permissions = QubitAclPermission::get($criteria)))
      {
        $repositoryAccess = self::addRepositoryAccess($repositoryAccess, $permissions);
      }
    }

    // Default is to deny access if no permissions specified
    if (0 == count($repositoryAccess) || '*' != $repositoryAccess[count($repositoryAccess) - 1]['id'])
    {
      $repositoryAccess[] = array('id' => '*', 'access' => self::DENY);
    }

    // Collapse access rules so that e.g.
    // ('1' => deny, '2' => allow, '*' => deny) -> ('2' => allow, '*' => deny)
    // ('1' => deny, '2' => allow, '*' => allow) -> (1' => deny, '*' => allow)
    $globalPermission = $repositoryAccess[count($repositoryAccess) - 1]['access'];
    $collapsedRules = array();
    foreach ($repositoryAccess as $i => $val)
    {
      if ('*' == $val['id'] || $globalPermission != $val['access'])
      {
        $collapsedRules[] = $val;
      }
    }

    return $collapsedRules;
  }

  public static function forwardUnauthorized($headerOnly = false)
  {
    sfContext::getInstance()->response->setStatuscode(403);

    if ($headerOnly)
    {
      return sfView::HEADER_ONLY;
    }

    if (!sfContext::getInstance()->user->isAuthenticated())
    {
      self::forwardToLoginAction();
    }
    else
    {
      self::forwardToSecureAction();
    }
  }

  /**
   * Forwards the current request to the secure action.
   *
   * Copied from sfBasicSecurityFilter
   *
   * @see lib/vendor/symfony/lib/filter/sfBasicSecurityFilter.class.php
   * @throws sfStopException
   */
  public static function forwardToSecureAction()
  {
    sfContext::getInstance()->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

    throw new sfStopException();
  }

  /**
   * Forwards the current request to the login action.
   *
   * Copied from sfBasicSecurityFilter
   *
   * @see lib/vendor/symfony/lib/filter/sfBasicSecurityFilter.class.php
   * @throws sfStopException
   */
  public static function forwardToLoginAction()
  {
    sfContext::getInstance()->getController()->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));

    throw new sfStopException();
  }

  /**
   * Get a list of user permissions by action and class of resource
   *
   * @param sfBasicSecurityUser $user - user session
   * @param string $action - requested ACL action
   * @param string $class - resource class
   *
   * @return QubitQuery list of QubitAclPermissions
   */
  public static function getUserPermissionsByAction($user, $class, $action)
  {
    // Get user's groups
    if ($user->isAuthenticated())
    {
      foreach ($user->listGroups() as $group)
      {
        $userGroupIds[] = $group->id;
      }
    }
    else
    {
      $userGroupIds = array(QubitAclGroup::ANONYMOUS_ID);
    }

    // Find relevant rules
    $criteria = new Criteria;
    $c1 = $criteria->getNewCriterion(QubitAclPermission::ACTION, $action);
    $c2 = $criteria->getNewCriterion(QubitAclPermission::ACTION, null, Criteria::ISNULL);
    $c1->addOr($c2);

    // Find by group/user
    $c3 = $criteria->getNewCriterion(QubitAclPermission::GROUP_ID, $userGroupIds, Criteria::IN);
    if ($user->isAuthenticated())
    {
      $c4 = $criteria->getNewCriterion(QubitAclPermission::USER_ID, $user->getUserID());
      $c3->addOr($c4);
    }
    $c1->addAnd($c3);

    // Find by object type
    $criteria->addJoin(QubitAclPermission::OBJECT_ID, QubitObject::ID, Criteria::LEFT_JOIN);
    $c4 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
    $c5 = $criteria->getNewCriterion(QubitObject::CLASS_NAME, $class);
    $c4->addOr($c5);

    // Final conjunction
    $c1->addAnd($c4);
    $criteria->add($c1);

    return QubitAclPermission::get($criteria);
  }

  public static function addFilterDraftsCriteria($criteria)
  {
    // Draft permissions criteria
    $filterCriteria = self::getFilterCriterion($criteria, QubitInformationObject::getRoot(), 'viewDraft');

    // Avoid to add criteria if not needed
    // Show ALL drafts and published descriptions (don't add to criteria)
    if (true === $filterCriteria)
    {
      return $criteria;
    }

    $criteria->addJoin(QubitInformationObject::ID, QubitStatus::OBJECT_ID, Criteria::LEFT_JOIN);

    // Either object must be published, or...
    $ct1 = $criteria->getNewCriterion(QubitStatus::TYPE_ID, QubitTerm::STATUS_TYPE_PUBLICATION_ID);
    $ct2 = $criteria->getNewCriterion(QubitStatus::STATUS_ID, QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID);
    $ct1->addAnd($ct2);

    // Show a limited set of draft descriptions + all published descriptions
    // Otherwise, show only published descriptions
    if (!is_bool($filterCriteria))
    {
      $ct1->addOr($filterCriteria);
    }

    return $criteria->addAnd($ct1);
  }

  /**
   * Get a new criterion to filter a SQL query by ACL rules
   *
   * @param Criteria $criteria
   * @param mixed $root - root object for list
   * @return Criterion
   */
  public static function getFilterCriterion($criteria, $root, $action)
  {
    $user = sfContext::getInstance()->user;
    $rootClass = get_class($root);

    if ('createTerm' != $action)
    {
      $permissions = self::getUserPermissionsByAction($user, $rootClass, $action);
    }
    else
    {
      $permissions = self::getUserPermissionsByAction($user, 'QubitTerm', 'create');
    }

    // Build access control list
    $allows = $bans = $ids = array();
    $forceBan = false;

    if (0 < count($permissions))
    {
      foreach ($permissions as $permission)
      {
        switch ($action)
        {
          case 'createTerm':
            if (null !== $slug = $permission->getConstants(array('name' => 'taxonomy')))
            {
              $criteria2 = new Criteria;
              $criteria2->add(QubitSlug::SLUG, $slug);
              $criteria2->addJoin(QubitSlug::OBJECT_ID, QubitTaxonomy::ID);

              if (null !== $taxonomy = QubitTaxonomy::getOne($criteria2))
              {
                // Add id directly to the allows and bans arrays
                // self::isAllowed gives unexpected results if there are more than one taxonomy rule
                if ($permission->grantDeny == 1)
                {
                  $allows[] = $taxonomy->id;
                }
                else
                {
                  $bans[] = $taxonomy->id;
                }
              }
            }
            else
            {
              // Grant or deny all if the permission hasn't taxonomy's constants
              return $permission->grantDeny == 1;
            }

            break;

          case 'viewDraft':
            if (null !== $repository = $permission->getConstants(array('name' => 'repository')))
            {
              $repoId = QubitPdo::fetchColumn('SELECT object_id FROM slug WHERE slug=?', array($repository));
              $sql = 'SELECT id FROM information_object WHERE repository_id=?';

              // Stay within the query limit
              if ($criteria->getLimit() > 0)
              {
                $sql .= ' LIMIT ' . $criteria->getLimit();
              }

              $rows = QubitPdo::fetchAll($sql, array($repoId));
              foreach ($rows as $row)
              {
                $ids[] = $row->id;
              }

              // Special case because isAllowed() on ROOT will return true if
              // user has grant permission on ANY repository. This will force
              // showing ONLY resources in allowed repositories
              $forceBan = count($rows) > 0;
            }

            break;

          default:
            $ids[] = $permission->objectId;
        }
      }

      $resourceCache = array(); // Hydrating tons of ORM objects via getById() is very slow, cache results

      foreach ($ids as $id)
      {
        if (array_key_exists($id, $resourceCache))
        {
          continue;
        }

        $resource = call_user_func(array($rootClass, 'getById'), $id);
        $resourceCache[$id] = $resource;

        if (self::isAllowed($user, $resource, $action))
        {
          $allows[] = $id;
        }
        else
        {
          $bans[] = $id;
        }
      }
    }

    $isAllowedActionOnRoot = QubitAcl::isAllowed($user, $root, $action);

    // Special cases - avoid adding unnecessary criteria
    if (0 == count($allows) && !$isAllowedActionOnRoot)
    {
      return false; // No allows, always false
    }
    else if (!$forceBan && 0 == count($bans) && $isAllowedActionOnRoot)
    {
      return true; // No bans, always true
    }

    // If more allows than bans, then add list of allowed resources. Otherwise, add list of banned resources.
    if (count($allows) >= count($bans))
    {
      $criterion = self::getAllowedResourcesCriterion($criteria, $rootClass, $resourceCache, $allows, true);
    }
    else
    {
      $criterion = self::getAllowedResourcesCriterion($criteria, $rootClass, $resourceCache, $bans, false);
    }

    return $criterion;
  }

  /**
   * Get a Criteria query either including allowed object ids, or filter out denied object ids.
   *
   * @param Criteria  $criteria  The Criteria object passed to getFilterCriterion
   * @param string  $rootClass  The class name of the resource being checked on.
   * @param array  $resourceCache  A cache of objects so we don't need to call getById() frequently.
   * @param array  $resourceIds  An array of object ids which we are either allowed / denied to view.
   * @param boolean  $allow  Which type of access the ids in the array represent, allow access (true) or deny (false).
   *
   * @return Criterion
   */
  private static function getAllowedResourcesCriterion($criteria, $rootClass, $resourceCache, $resourceIds, $allow)
  {
    while ($resourceId = array_shift($resourceIds))
    {
      if (array_key_exists($resourceId, $resourceCache))
      {
        $resource = $resourceCache[$resourceId];
      }
      else
      {
        $resource = call_user_func(array($rootClass, 'getById'), $resourceId);
      }

      // If object has no children include it by id and carry on
      if (1 == ($resource->rgt - $resource->lft))
      {
        $criterion = $criteria->getNewCriterion(
          constant("$rootClass::ID"),
          $resourceId,
          $allow ? Criteria::EQUAL : Criteria::NOT_EQUAL
        );
      }
      else // Else, include object and all children
      {
        $criterion = $criteria->getNewCriterion(
          constant("$rootClass::LFT"),
          $resource->lft,
          $allow ? Criteria::GREATER_EQUAL : Criteria::LESS_EQUAL
        );

        $criterion2 = $criteria->getNewCriterion(
          constant("$rootClass::RGT"),
          $resource->rgt,
          $allow ? Criteria::LESS_EQUAL : Criteria::GREAT_EQUAL
        );

        $criterion->addAnd($criterion2);
      }
    }

    return $criterion;
  }
}
