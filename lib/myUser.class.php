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

class myUser extends sfBasicSecurityUser implements Zend_Acl_Role_Interface
{
  public $user = null;

  // Instance of QubitClipboard that uses the current
  // storage to save a list of selected information objects
  private $clipboard = null;

  /**
   * Required for Zend_Acl_Role_Interface
   */
  public function getRoleId()
  {
    if ($this->isAuthenticated())
    {
      return $this->getUserID();
    }
    else
    {
      return QubitAclGroup::ANONYMOUS_ID;
    }
  }

  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    // initialize parent
    parent::initialize($dispatcher, $storage, $options);

    // On timeout, remove *all* user credentials
    if ($this->isTimedOut())
    {
      $this->signOut();

      return;
    }

    if ($this->isAuthenticated())
    {
      try
      {
        $this->user = QubitUser::getById($this->getUserID());
      }
      catch (Exception $e)
      {
        $this->user = null;
      }

      // If this user's account has been *deleted* or this user session is from a
      // different install of qubit on the same server (cross-site), then signout
      // user
      if (null === $this->user || !$this->user->active)
      {
        $this->signOut();
      }
    }
  }

  public function signIn($user)
  {
    $this->setAuthenticated(true);
    $this->user = $user;

    foreach ($user->getAclGroups() as $group)
    {
      $this->addCredential($group->getName(array('culture' => 'en')));
    }

    $this->setAttribute('user_id', $user->id);
    $this->setAttribute('user_slug', $user->slug);
    $this->setAttribute('user_name', $user->username);
  }

  public function signOut()
  {
    $this->getAttributeHolder()->removeNamespace('credentialScope');

    $this->clearCredentials();
    $this->setAuthenticated(false);

    $this->getAttributeHolder()->remove('user_id');
    $this->getAttributeHolder()->remove('user_slug');
    $this->getAttributeHolder()->remove('user_name');
    $this->getAttributeHolder()->remove('login_route');
    $this->getAttributeHolder()->remove('nav_context_module');
  }

  public function removeAttribute($attribute)
  {
    $this->getAttributeHolder()->remove($attribute);
  }

  public function getUserID()
  {
    return $this->getAttribute('user_id');
  }

  public function getUserSlug()
  {
    return $this->getAttribute('user_slug');
  }

  public function getUserName()
  {
    return $this->getAttribute('user_name');
  }

  public function authenticate($username, $password)
  {
    $authenticated = false;
    // anonymous is not a real user
    if ($username == 'anonymous')
    {
      return false;
    }

    $user = QubitUser::checkCredentials($username, $password, $error);

    // user account exists?
    if ($user !== null)
    {
      $authenticated = true;
      $this->signIn($user);
    }

    return $authenticated;
  }

  public function getQubitUser()
  {
    return $this->user;
  }

  public function getAclGroups()
  {
    if (!$this->isAuthenticated())
    {
      return array(QubitAclGroup::getById(QubitAclGroup::ANONYMOUS_ID));
    }
    else
    {
      return $this->user->getAclGroups();
    }
  }

  public function hasGroup($checkGroups)
  {
    $hasGroup = false;

    if ($this->isAuthenticated())
    {
      $hasGroup = $this->user->hasGroup($checkGroups);
    }
    else
    {
      if (!is_array($checkGroups))
      {
        $checkGroups = array($checkGroups);
      }

      if (in_array(QubitAclGroup::ANONYMOUS_ID, $checkGroups))
      {
        $hasGroup = true;
      }
    }

    return $hasGroup;
  }

  public function listGroups()
  {
    if ($this->isAuthenticated())
    {
      $groups = array(QubitAclGroup::getById(QubitAclGroup::AUTHENTICATED_ID));

      if (null !== $this->user->aclUserGroups)
      {
        foreach ($this->user->aclUserGroups as $aclUserGroup)
        {
          $groups[] = QubitAclGroup::getById($aclUserGroup->groupId);
        }
      }

      return $groups;
    }
    else
    {
      return QubitAclGroup::getById(QubitAclGroup::ANONYMOUS_ID);
    }
  }

  /**
   * Using $sf_user->hasGroup() since it relies on database,
   * $sf_user->hasCredential('administrator') relies on session storage
   * See 4214.
   *
   * @return Boolean
   */
  public function isAdministrator()
  {
    return $this->hasGroup(QubitAclGroup::ADMINISTRATOR_ID);
  }

  public function isAuthenticated()
  {
    if (sfConfig::get('app_read_only', false))
    {
      return false;
    }

    return parent::isAuthenticated();
  }

  public function getClipboard()
  {
    if (!isset($this->clipboard))
    {
      $this->clipboard = new QubitClipboard($this->storage);
    }

    return $this->clipboard;
  }
}
