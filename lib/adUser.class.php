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

include sfConfig::get('sf_root_dir') .'/vendor/password_compat/password.php';

class adUser extends myUser implements Zend_Acl_Role_Interface
{
  protected $ldapConnection;
  protected $ldapBound;

  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    // initialize parent
    parent::initialize($dispatcher, $storage, $options);

    if (!extension_loaded('ldap'))
    {
      throw new sfConfigurationException('ldapUser class needs the "ldap" extension to be loaded.');
    }
  }

  public function authenticate($username, $password)
  {
    // Allow LDAP authentication to be overridden during development
    $configuration = sfContext::getInstance()->getConfiguration();
    if ($configuration->isDebug() || 'dev' == $configuration->getEnvironment())
    {
      return parent::authenticate($username, $password);
    }

    // Anonymous is not a real user
    if ($username == 'anonymous')
    {
      return false;
    }

    $authenticated = $this->ldapAuthenticate($username, $password);

    // Fallback to non-LDAP authentication if need be and load/create user data
    if (!$authenticated)
    {
      $authenticated = parent::authenticate($username, $password);
      // Load user
      $criteria = new Criteria;
      $criteria->add(QubitUser::EMAIL, $username);
      $user = QubitUser::getOne($criteria);
    }
    else
    {
      // Load user using username or, if one doesn't exist, create it
      $criteria = new Criteria;
      // Use the AD Username or else you will get the UPN as username.
      $criteria->add(QubitUser::EMAIL, $username);
      if (null === $user = QubitUser::getOne($criteria))
      {
        $user = $this->createUserFromLdapInfo($username);
      }
    }

    // Unbind if necessary to be easy on the LDAP server
    if ($this->ldapBound)
    {
      ldap_unbind($this->ldapConnection);
    }

    // Sign in user if authentication was successful
    if ($authenticated)
    {
      $this->signIn($user);
    }

    return $authenticated;
  }

  protected function createUserFromLdapInfo($username)
  {
    $user = new QubitUser();
    $conn = $this->getLdapConnection();
    // Do AD search for user's email address
    $base_dn = (string)QubitSetting::getByName('ldapBaseDn');
    $filter='(&(objectCategory=person)(objectClass=user)(userPrincipalName='. $username .'))';
    $result = ldap_search($conn, $base_dn, $filter);
    $entries = ldap_get_entries($conn, $result);
    $noentries = ldap_count_entries($conn, $result);
	  
    // If user is found and email exists, store it
    if ($noentries > 0)
    {
      //In order to relocate the user when logging in again, the email must match the UPN
      //which in some organisations will match the email address in any case.
      $user->email = $username;
      $user->username = $entries[0]['name'][0];
    } else { $user->username = $username; }
	  
    $user->save();
    return $user;
  }

  protected function getLdapConnection()
  {
    if (isset($this->ldapConnection))
    {
      return $this->ldapConnection;
    }
	  
    $host = QubitSetting::getByName('ldapHost');
	  
    if (null !== $host)
    {
      // If using an URI you only need to send the host URI, so the $port will be null
      $connection = ldap_connect($host->getValue(array('sourceCulture' => true)));
      ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
      $this->ldapConnection = $connection;
      return $connection;
    }
  }

  protected function ldapBind($username, $password)
  {
    if ($conn = $this->getLdapConnection())
    {
      $this->ldapBound = ldap_bind($conn, $username, $password);
      return $this->ldapBound;
    }
  }

  /**
   * ldapAuthenticate caches the result of ldapBind with a short TTL
   * to avoid hitting the directory.
   */
  private function ldapAuthenticate($username, $password)
  {
    try
    {
      // Try to load a cache engine
      $cache = QubitCache::getInstance();
    }
    catch (Exception $e)
    {
      return $this->ldapBind($username, $password);
    }
    $cacheKey = 'ldap-hash:'.$username;
	  
    // Look up cache entry and verify hash if exists
    if ($cache->has($cacheKey) && (null !== $hash = $cache->get($cacheKey)))
    {
      return password_verify($password, $hash);
    }
	  
    // Authenticate against LDAP
    if (!$this->ldapBind($username, $password))
    {
      return false;
    }
	  
    // Cache entry
    $hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 10));
    $cache->set($cacheKey, $hash, 120);
    return true;
  }
}
