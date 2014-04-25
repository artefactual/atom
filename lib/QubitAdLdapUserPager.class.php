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
 * Qubit specifc extension to the sfPropelPager
 *
 * @package AccesstoMemory
 * @author  David Juhasz <david@artefactual.com>
 */
class QubitAdLdapUserPager extends sfPropelPager
{
  /**
   * Override ::getNbResults()
   *
   * @see sfPager
   */
  public function getNbResults()
  {
    return $this->nbResults;
  }

  /**
   * Override ::getResults() to pull data from LDAP
   *
   * @see sfPager
   */
  public function getResults()
  {
    $results = array();

    $limit = $this->maxPerPage;
    $skip = ($this->page - 1) * $limit;

    $ldapUsers = adLdapUser::allUsers();

    $this->nbResults = count($ldapUsers);
    $this->setLastPage(ceil($this->nbResults / $this->maxPerPage));

    $index = 0;
    foreach ($ldapUsers as $userId => $username)
    {
      // If result is in selection range, then add it to results
      if (($index >= $skip) && ($index < ($skip + $limit)))
      {
        // Look up user by username
        $criteria = new Criteria;
        $criteria->add(QubitUser::USERNAME, $username);

        $user = QubitUser::getOne($criteria);

        // Create user if it doesn't already exist
        if ($user == null)
        {
          $user = new QubitUser();
          $user->username = $username;

          // Set LDAP-derived user properties
          $info = adLdapUser::ldapUserInfo($username);
          if (false !== $info)
          {
            foreach($info as $field => $value)
            {
              $user->$field = $value;
            }
          }

          $user->save();
        }

        array_push($results, $user);
      }

      $index++;
    }

    return $results;
  }
}
