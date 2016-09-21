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
 * Delete AtoM user
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class deleteUserTask extends arBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('username', sfCommandArgument::REQUIRED, 'The username to delete.')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('force', 'f', sfCommandOption::PARAMETER_NONE, 'Delete without confirmation', null),
      new sfCommandOption('update-notes', 'n', sfCommandOption::PARAMETER_NONE, 'Dissassociate any notes the user has created', null)
    ));

    $this->namespace = 'tools';
    $this->name = 'delete-user';
    $this->briefDescription = 'Delete a user.';

    $this->detailedDescription = <<<EOF
Delete a user.
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    // Abort if deletion isn't forced or confirmed
    if (!$options['force'] && !$this->getConfirmation($arguments['username']))
    {
      $this->logSection('delete-user', 'Aborted.');
      return;
    }

    // Attempt to find user, exiting if the user doesn't exist
    $criteria = new Criteria;
    $criteria->add(QubitUser::USERNAME, $arguments['username']);
    if (null === $user = QubitUser::getOne($criteria))
    {
      throw new Exception('Unknown user.');
    }

    
    // If user is an administrator, abort if the user is the only administrator
    if ($user->hasGroup(QubitAclGroup::ADMINISTRATOR_ID))
    {
      $criteria = new Criteria;
      $criteria->add(QubitAclUserGroup::GROUP_ID, QubitAclGroup::ADMINISTRATOR_ID);
      $adminCount = count(QubitAclUserGroup::get($criteria));

      if ($adminCount == 1)
      {
        throw new Exception('This is the only administrator: deletion aborted.');
      }
    }

    // If notes are associated with user, abort unless "--update-notes" flag is set
    if (count($user->getNotes()) && !$options['update-notes'])
    {
      throw new Exception('Deleting user would disassociate notes created by the user: deletion aborted (use --update-notes flag to force).');
    }

    $user->delete();
    $this->logSection('delete-user', 'Deleted user "'. $arguments['username'] .'".');
  }

  private function getConfirmation($username)
  {
    return $this->askConfirmation(
      'WARNING: You are about to delete the user "'. $username .'". Are you sure?',
      'QUESTION_LARGE',
      false
    );
  }
}
