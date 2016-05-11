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

class QubitGrantedRight extends BaseGrantedRight
{
  const
    DENY_RIGHT = 0,
    GRANT_RIGHT = 1,
    CONDITIONAL_RIGHT = 2;

  /**
   * Initializes internal state of QubitGrantedRight object.
   * @see        parent::__construct()
   */
  public function __construct()
  {
    // Make sure that parent constructor is always invoked, since that
    // is where any default values for this object are set.
    parent::__construct();
  }

  /**
   * This method will check global PREMIS settings against granted rights
   * on an information object, & return which groups are allowed to perform
   * a specified action.
   *
   * @param  int  id  The ID of the information object to check permissions against.
   * @param  string  action  The action to check against, e.g. 'readReference'
   * @param  string  denyReason  An optional parameter for the function to pass out
   *                 which rule was responsible for any denial (e.g. 'conditional')
   *
   * @return  array  An array of groupIds that are allowed to perform $action on
   *                 the specified information object.
   */
  private static function checkPremisRightsAgainstGroups($id, $action, &$denyReason = null)
  {
    list($actId, $premisPerms) = self::getPremisSettings();
    $grantedRights = self::getByObjectIdAndAct($id, $actId);

    $groupIds = array(
      QubitAclGroup::AUTHENTICATED_ID,
      QubitAclGroup::ADMINISTRATOR_ID,
      QubitAclGroup::ANONYMOUS_ID
    );

    if ($id == QubitInformationObject::ROOT_ID)
    {
      throw new sfException('Cannot call checkPremisRightsAgainstGroups with ROOT_ID');
    }

    // Get usage, e.g. 'reference', 'master'
    $usage = strtolower(str_replace('read', '', $action));

    // Check the $grantedRights of the information object against the global
    // PREMIS rights for $action.
    foreach ($grantedRights as $right)
    {
      if ($right->actId != $actId)
      {
        continue;
      }

      if (empty($right->rights) || empty($right->rights->basisId))
      {
        continue;
      }

      switch ($right->restriction)
      {
        case QubitGrantedRight::DENY_RIGHT:
          $restriction = 'disallow';
          break;

        case QubitGrantedRight::GRANT_RIGHT:
          $restriction = 'allow';
          break;

        case QubitGrantedRight::CONDITIONAL_RIGHT:
          $restriction = 'conditional';
          break;

        default:
          throw new sfException("Invalid restriction value given: {$right->restriction}");
      }

      // Remove unauthenticated user access and finish loop,
      // as one "denied" permission overules any "grants" we'll see.
      $basisSlug = $right->rights->basis->slug;
      if (empty($premisPerms[$basisSlug]) ||
          empty($premisPerms[$basisSlug]["{$restriction}_{$usage}"]) ||
          !$premisPerms[$basisSlug]["{$restriction}_{$usage}"])
      {
        if (($key = array_search(QubitAclGroup::ANONYMOUS_ID, $groupIds)) !== false)
        {
          unset($groupIds[$key]);
          if ($denyReason !== null)
          {
            $denyReason = self::getAccessWarning($basisSlug, $restriction);
          }
        }

        break;
      }
    }

    return $groupIds;
  }

  private static function getAccessWarning($basisSlug, $restriction)
  {
    if ($restriction === 'conditional')
    {
      $setting = QubitSetting::getByNameAndScope("{$basisSlug}_conditional", 'access_statement');
    }
    else
    {
      $setting = QubitSetting::getByNameAndScope("{$basisSlug}_disallow", 'access_statement');
    }

    if ($setting === null)
    {
      return false;
    }

    return $setting->getValue(array('cultureFallback' => true));
  }

  /**
   * A wrapper for checkPremisRightsAgainstGroups. This method will check to see
   * if the current user is anonymous. If so, it will check the current global
   * PREMIS settings against an information object given a specified digital object
   * type (e.g. thumb, reference, master).
   *
   * @param int  id  The information object id
   * @param string  action  The digital object action, i.e. 'readThumb', 'readMaster', 'readReference'
   * @param  string  denyReason  An optional parameter for the function to pass out
   *                 which rule was responsible for any denial (e.g. 'conditional')
   *
   * @return bool  Whether or not the user has permission
   */
  public static function checkPremis($id, $action, &$denyReason = null)
  {
    if (!isset($id))
    {
      throw new sfException('QubitInformationObject id not set in checkPremis call.');
    }

    if (sfContext::getInstance()->getUser()->isAuthenticated() || !QubitGrantedRight::hasGrantedRights($id))
    {
      // PREMIS rules apply to unauthenticated users only.
      // Also, don't bother checking if no granted rights exist here.
      return true;
    }

    // TODO: Make 'readThumb' 'readThumbnail' across all the code to make it consistent
    // between the PREMIS action settings and regular db ACL settings
    if ($action === 'readThumbnail')
    {
      $action = 'readThumb';
    }

    $groupsAllowed = QubitGrantedRight::checkPremisRightsAgainstGroups($id, $action, $denyReason);

    return in_array(QubitAclGroup::ANONYMOUS_ID, $groupsAllowed);
  }

  /**
	 * Get all the granted rights for a particular information object
	 * @param $id  The information object ID
	 * @param $actId  The optional ID for the act type (gets all types by default)
	 * @return array  An array of QubitGrantedRight objects
	 */
  public static function getByObjectIdAndAct($id, $actId = null)
  {
    $c = new Criteria;

    $c->add(QubitInformationObject::ID, $id);
    $c->addJoin(QubitRelation::SUBJECT_ID, QubitInformationObject::ID);
    $c->add(QubitRelation::TYPE_ID, QubitTerm::RIGHT_ID);
    $c->addJoin(QubitRelation::OBJECT_ID, QubitGrantedRight::RIGHTS_ID);

    if ($actId)
    {
      $c->add(QubitGrantedRight::ACT_ID, $actId);
    }

    return QubitGrantedRight::get($c);
  }

  /**
   * Return act_id and permissions for global PREMIS rights settings
   */
  public static function getPremisSettings()
  {
    $premisAccessRight = QubitSetting::getByName('premisAccessRight');
    $act = QubitTaxonomy::getBySlug($premisAccessRight->getValue(array('sourceCulture' => true)));

    if ($act === null)
    {
      throw new sfException("Invalid Act specified for PREMIS rights: $premisAccessRight");
    }

    /**
     * $permissions will be a multidimensional array with different
     * permissions for each basis (indexed by its slug):
     * 'copyright' => array
     *   'allow_master'          => int 0,
     *   'allow_reference'       => int 1,
     *   'allow_thumb'           => int 1,
     *   'conditional_master'    => int 0,
     *   'conditional_reference' => int 1,
     *   'conditional_thumb'     => int 1,
     *   'disallow_master'       => int 1,
     *   'disallow_reference'    => int 0,
     *   'disallow_thumb'        => int 0
     */
    $permissions = QubitSetting::getByName('premisAccessRightValues');

    return array($act->id, unserialize($permissions->getValue(array('sourceCulture' => true))));
  }

  /**
   * Gets the string related to a restriction.
   *
   * @param int  restrictionId  The restriction number.
   * @return string  The string representing the restriction.
   */
  public static function getRestrictionString($restrictionId)
  {
    $mapConst = array(
      self::DENY_RIGHT => 'Disallow',
      self::GRANT_RIGHT => 'Allow',
      self::CONDITIONAL_RIGHT => 'Conditional'
    );

    if (!array_key_exists($restrictionId, $mapConst))
    {
      throw new sfException ("Invalid restriction type in getRestrictionString: $restrictionId");
    }

    return $mapConst[$restrictionId];
  }

  /**
   * Gets whether or not an information object has granted rights.
   *
   * @param int  id  The information object id
   * @return bool  True if has granted rights, false otherwise
   */
  public static function hasGrantedRights($id)
  {
    return count(self::getByObjectIdAndAct($id)) > 0;
  }
}
