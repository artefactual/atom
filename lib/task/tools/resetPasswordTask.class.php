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

class resetPasswordTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('username', sfCommandArgument::REQUIRED, 'Username/E-Mail'),
      new sfCommandArgument('password', sfCommandArgument::OPTIONAL, 'Password')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('activate', null, sfCommandOption::PARAMETER_OPTIONAL, 'Activate', false),
    ));

    $this->namespace = 'tools';
    $this->name = 'reset-password';
    $this->briefDescription = 'Generates or set a new password for a given username or e-mail address';
    $this->detailedDescription = <<<EOF
FIXME
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();
    sfContext::createInstance($this->configuration);

    $criteria = new Criteria;
    $c1 = $criteria->getNewCriterion(QubitUser::USERNAME, $arguments['username']);
    $c2 = $criteria->getNewCriterion(QubitUser::EMAIL, $arguments['username']);
    $criteria->add($c1->addOr($c2));
    $user = QubitUser::getOne($criteria);

    // User account exists?
    if ($user !== null)
    {
      if (isset($arguments['password']))
      {
        $password = $arguments['password'];
      }
      else
      {
        $password = $this->generatePassword();
      }

      $user->setPassword($password);

      if (false !== $options['activate'])
      {
        $user->active = true;
      }

      $user->save();

      $this->logSection('reset-password', 'New password: '.$password);
    }
    else
    {
      throw new sfException('Given username does not exist');
    }
  }

  function generatePassword($length = 8)
  {
    // Start with a blank password
    $password = "";

    // Define possible characters - any character in this string can be
    // picked for use in the password, so if you want to put vowels back in
    // or add special characters such as exclamation marks, this is where
    // you should do it
    $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

    // We refer to the length of $possible a few times, so let's grab it now
    $maxlength = strlen($possible);

    // Check for length overflow and truncate if necessary
    if ($length > $maxlength)
    {
      $length = $maxlength;
    }

    // Set up a counter for how many characters are in the password so far
    $i = 0;

    // add random characters to $password until $length is reached
    while ($i < $length)
    {
      // Pick a random character from the possible ones
      $char = substr($possible, mt_rand(0, $maxlength - 1), 1);

      // Have we already used this character in $password?
      if (!strstr($password, $char))
      {
        // No, so it's OK to add it onto the end of whatever we've already got...
        $password .= $char;
        // ... and increase the counter by one
        $i++;
      }
    }

    // Done!
    return $password;
  }
}
