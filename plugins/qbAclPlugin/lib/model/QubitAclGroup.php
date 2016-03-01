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
 * Extend BaseAclGroup functionality.
 *
 * @package    AccesstoMemory
 * @subpackage acl
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitAclGroup extends BaseAclGroup implements Zend_Acl_Role_Interface
{
  const ROOT_ID          = 1;
  const ANONYMOUS_ID     = 98;
  const AUTHENTICATED_ID = 99;
  const ADMINISTRATOR_ID = 100;
  const ADMIN_ID         = 100;
  const EDITOR_ID        = 101;
  const CONTRIBUTOR_ID   = 102;
  const TRANSLATOR_ID    = 103;

  public function __toString()
  {
    return (string) $this->getName(array('cultureFallback' => true));
  }

  /**
   * Required for Zend_Acl_Role_Interface
   */
  public function getRoleId()
  {
    return $this->id;
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->aclPermissions as $aclPermission)
    {
      $aclPermission->group = $this;
      $aclPermission->save($connection);
    }

    return $this;
  }

  public function isProtected()
  {
    return in_array($this->id, array(
      self::ROOT_ID,
      self::ANONYMOUS_ID,
      self::AUTHENTICATED_ID,
      self::ADMINISTRATOR_ID));
  }
}
