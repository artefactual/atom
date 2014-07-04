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
 * Update Acl status on information objects in ElasticSearch
 *
 * @package    symfony
 * @subpackage jobs
 */

class arUpdateAclJob extends arBaseJob
{
  /**
   * Add Acl rules to elastic search.
   *
   * @param  array $parameters  - Information about what info objects to update
   * what Acl action it's for, and which users/groups gain access to said objects.
   *
   * If objectIds contains only ROOT_ID (id=1), assume we're updating *ALL* information objects
   * with these rules.
   */
  public function run($parameters)
  {
    // This will be an array of required parameter names
    $this->addRequiredParameters(array(
      'objectIds',
      'action',
      'userIds',
      'groupIds'
    ));

    // parent::run() will check parameters and throw an exception if any are missing
    parent::run($parameters);
    printf('Entering acl job task' . "\n");

    $ids = $parameters['objectIds'];

    // ROOT_ID is the only thing in the objectIds list, let's update
    // all information objects to use these rules
    if (count($ids) === 1 && $ids[0] === QubitInformationObject::ROOT_ID)
    {
      $rows = QubitPdo::fetchAll(
        'SELECT id FROM information_object WHERE id <> ?',
        array(QubitInformationObject::ROOT_ID)
      );

      array_pop($ids); // Throw away ROOT_ID & add all info object ids
      foreach ($rows as $row)
      {
        $ids[] = $row->id;
      }
    }

    $n = 0;
    foreach ($ids as $objectId)
    {
      $aclEntry = new QubitElasticAclEntry;

      $aclEntry->id = $objectId;
      $aclEntry->action = $parameters['action'];
      $aclEntry->userIds = $parameters['userIds'];
      $aclEntry->groupIds = $parameters['groupIds'];

      arElasticSearchInformationObject::updateAcl($objectId, $aclEntry);

      if (++$n % 100 == 0)
      {
        QubitInformationObject::clearCache();
        print '.';
      }
    }

    // TODO: Update MySQL acl entries?

    QubitSearch::getInstance()->flushBatch(true);

    // Don't forget to set the job status & save at the end!
    $this->job->setStatusCompleted();
    $this->job->save();

    printf('Done acl job task' . "\n");

    return true;
  }
}
