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
 * Promote user to admin
 *
 * @package    symfony
 * @subpackage task
 */
class promoteUserToAdminTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('username', sfCommandArgument::REQUIRED, 'The username')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace = 'tools';
    $this->name = 'promote-user-to-admin';
    $this->briefDescription = 'Prompote user to admin.';

    $this->detailedDescription = <<<EOF
Prompote existing user to admin.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $criteria = new Criteria;
    $criteria->add(QubitUser::USERNAME, $arguments['username']);
    if (null === $user = QubitUser::getOne($criteria))
    {
      throw new Exception('Unknown user.');
    }

    // Make sure that the user is active
    $user->active = true;

    // Check if the user is already an administrator
    if ($user->hasGroup(QubitAclGroup::ADMINISTRATOR_ID))
    {
      throw new Exception('The given user is already an administrator.');
    }

    // Give user admin capability
    $group = new QubitAclUserGroup();
    $group->userId = $user->id;
    $group->groupId = QubitAclGroup::ADMINISTRATOR_ID;
    $group->save();

    $this->logSection('info', 'The user '.$user->username.' is now an administrator.');
  }
}
