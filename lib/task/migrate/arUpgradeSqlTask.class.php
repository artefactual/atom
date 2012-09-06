<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Migrate qubit data model via direct SQL calls
 *
 * @package    qubit
 * @subpackage migration
 * @version    svn: $Id: arUpgradeSqlTask.class.php 11979 2012-07-25 23:27:32Z sevein $
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitUpgradeSqlTask extends sfBaseTask
{
  protected
    $initialVersion,
    $targetVersion,
    $upgraders = array();

  /**
   * @see sfBaseTask
   */
  protected function configure()
  {
    $this->namespace = 'tools';
    $this->name = 'upgrade-sql';
    $this->briefDescription = 'Migrate the database schema and existing data for compatibility with a newer version of Qubit.';
    $this->detailedDescription = <<<EOF
The [tools:migrate|INFO] task modifies the SQL data structure for compatibility with later versions of the application:

  [./symfony tools:upgrade-sql|INFO]
EOF;

    $this->addArguments(array(
      new sfCommandArgument('target', sfCommandArgument::OPTIONAL, 'Target version')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('no-backup', 'B', sfCommandOption::PARAMETER_NONE, 'Don\'t backup database', null),
      new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, 'Verbose mode', null),
    ));
  }

  /**
   * @see sfBaseTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $dbManager = new sfDatabaseManager($this->configuration);
    $database = $dbManager->getDatabase($options['connection']);

    // Deactivate search index, must be rebuilt later anyways
    QubitSearch::getInstance()->disabled = true;

    // Get initial version
    $sql = 'SELECT value AS version
      FROM setting JOIN setting_i18n ON setting.id = setting_i18n.id
      WHERE name = "version";';

    $this->initialVersion = QubitPdo::fetchColumn($sql);

    // A bug in the migration script for Release 1.1 left the version=62
    // instead of 75, so we need to check if the user is upgrading from 1.1
    // or 1.2
    if (62 == intval($this->initialVersion))
    {
      // Check if "accession_mask" setting exists (added in version 63)
      $sql = "SELECT id FROM setting WHERE name='accession_mask';";
      if (false !== QubitPdo::fetchOne($sql))
      {
        $this->initialVersion = 75;
      }
    }

    // Use old migration script for versions before 62
    if (null == $this->initialVersion || 62 > $this->initialVersion)
    {
      $this->logBlock(array(
        '',
        'Please use the propel:migrate task for upgrading',
        'from Qubit releases prior to Release 1.1',
        ''),
        'ERROR');

      return 1;
    }

    // Attempt to backup database (MySQL only)
    if (!$options['no-backup'])
    {
      $backupName = $this->backupDatabase($database);
    }

    // If backup failed, warn user to backup database manually
    if (!isset($backupName) && !$this->askConfirmation(array(
      'WARNING: Your database has not been backed up!',
      'Please back-up your database manually before you proceed.',
      '',
      'Have you done a manual backup and wish to proceed? (y/N)'),
      'QUESTION_LARGE',
      false))
    {
      $this->logSection('upgrade-sql', 'Upgrade aborted.');

      return 1;
    }

    // Find all the upgrade classes in lib/task/migrate
    $version = $this->initialVersion;

    foreach (sfFinder::type('file')
              ->maxdepth(0)
              ->sort_by_name()
              ->name('arUpgrader*.class.php')
              ->in(sfConfig::get('sf_lib_dir').'/task/migrate') as $filename)
    {
      $className = preg_replace('/.*(arUpgrader\d+).*/', '$1', $filename);
      $class = new $className;

      if ($class::INIT_VERSION > $version)
      {
        continue;
      }

      try
      {
        $this->logSection('upgrade-sql', sprintf('Upgrading from Release %s', $class::MILESTONE));

        while ($class->up($version, $this->configuration, $options))
        {
          // Update version in database
          $sql = 'UPDATE setting_i18n SET value = ? WHERE id = (SELECT id FROM setting WHERE name = ?);';
          QubitPdo::modify($sql, array(++$version, 'version'));
        }
      }
      catch (Exception $e)
      {
        $this->logSection('upgrade-sql', sprintf('The task failed while trying to upgrade to v%s', $version + 1));

        throw $e;
      }
    }

    if ($this->initialVersion == $version)
    {
      $this->logSection('upgrade-sql', sprintf('Already at latest version (%s), no upgrades done', $version));
    }
    else
    {
      $this->logSection('upgrade-sql', sprintf('Successfully upgraded to Release %s v%s', $class::MILESTONE, $version));
    }
  }

  protected function parseDsn($dsn)
  {
    $params = array(
      'host' => 'localhost',
      'port' => '3307');

    // Require a prefix
    if (!preg_match('/^(\w+):/', $dsn, $matches))
    {
      return;
    }
    $params['prefix'] = $matches[1];

    // Require a dbname
    if (!preg_match('/dbname=(\w+)/', $dsn, $matches))
    {
      return;
    }
    $params['dbname'] = $matches[1];

    // Optional params (host, port)
    if (preg_match('/host=([^;]+)/', $dsn, $matches))
    {
      $params['host'] = $matches[1];
    }

    if (preg_match('/port=(\d+)/', $dsn, $matches))
    {
      $params['port'] = $matches[1];
    }

    return $params;
  }

  protected function backupDatabase($database)
  {
    $backupSuccess = false;
    $dsn = $this->parseDsn($database->getParameter('dsn'));

    if (isset($dsn) && 'mysql' == strtolower($dsn['prefix']))
    {
      // MySQL backup
      $backupName = 'db_'.date('YmdHis').'.sql.bak';
      $this->logSection('backup', sprintf('Backing up database "%s" to %s', $dsn['dbname'], $backupName));

      $cmd = sprintf('mysqldump -u %s', $database->getParameter('username'));

      // Passing a blank "-p" will prompt for password, which we don't want
      if (null != $database->getParameter('password'))
      {
        $cmd .= sprintf(' -p%s', $database->getParameter('password'));
      }

      $cmd .= sprintf(' -h %s -P %s %s > %s',
        $dsn['host'],
        $dsn['port'],
        $dsn['dbname'],
        $backupName);

      // Run backup command
      system($cmd, $returned);

      if (0 == $returned)
      {
        $this->logSection('backup', 'Database backup complete!');
        $backupSuccess = true;
      }
      else
      {
        $this->logSection('backup', 'Database backup failed!', null, 'ERROR');
      }
    }

    if ($backupSuccess)
    {
      return $backupName;
    }
  }
}
