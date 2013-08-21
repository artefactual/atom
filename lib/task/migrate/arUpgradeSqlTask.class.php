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
 * Migrate qubit data model via direct SQL calls
 *
 * @package    AccesstoMemory
 * @subpackage migration
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitUpgradeSqlTask extends sfBaseTask
{
  protected
    $initialVersion;

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
      new sfCommandOption('no-confirmation', 'B', sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
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

    sfContext::createInstance($this->configuration);

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

    // Confirmation
    if (
      !$options['no-confirmation']
      &&
      !$this->askConfirmation(array(
          'WARNING: Your database has not been backed up!',
          'Please back-up your database manually before you proceed.',
          'If this task fails you may lose your data.',
          '',
          'Have you done a manual backup and wish to proceed? (y/N)'),
        'QUESTION_LARGE', false)
    )
    {
      $this->logSection('upgrade-sql', 'Task aborted.');

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
          $this->updateDatabaseVersion(++$version);
        }
      }
      catch (Exception $e)
      {
        $this->logSection('upgrade-sql', sprintf('The task failed while trying to upgrade to v%s', $version + 1));

        throw $e;
      }
    }

    // The database version in Release 1.3 was v92. After that, AtoM was forked
    // in two different versions: 1.x and 2.x. Both will be maintained for now.
    // This task selectively upgrades the user to the latest database version
    // available for its milestone. In order to keep things simpler, db upgrades
    // won't be able to target 1.x only. Upgrades targetting only 2.x won't be
    // applied to 1.x users until they decide to migrate to AtoM 2.x.
    // See #4494 for more details.

    // Since some db upgrades are applied to both 1.x and 2.x releases, we need
    // the previous milestone used. This value has been stored in the database
    // since the fork happened.
    $previousMilestone = $this->getPreviousMilestone();

    // Upgrades post to Release 1.3 (v92) are located under
    // task/migrate/migrations and named using the following format:
    // "arMigration%04d.class.php" (the first one is arMigration0093.class.php)
    foreach (sfFinder::type('file')
      ->maxdepth(0)
      ->sort_by_name()
      ->name('arMigration*.class.php')
      ->in(sfConfig::get('sf_lib_dir').'/task/migrate/migrations') as $filename)
    {
      // Initialize migration class
      $className = preg_replace('/.*(arMigration\d+).*/', '$1', $filename);
      $class = new $className;

      // Are we stepping over this migration? Read more below :)
      $omit = false;

      // Upgrading from 1.x to 1.x
      // Ignore upgrades already applied and those targetting only 2.x
      if (version_compare(qubitConfiguration::VERSION, '2.0.0', '<'))
      {
        $omit = $class::VERSION <= $version || 1 < $class::MIN_MILESTONE;
      }
      // Upgrading from 1.x to 2.x
      // This is the trickiest: ignore upgrades already applied *excepting*
      // those targetting 2.x, as they should have been ignored been ignored
      // during 1.x to 1.x upgrades.
      else if (1 == $previousMilestone)
      {
        $omit = $class::VERSION == $version || ($class::VERSION < $version && 1 == $class::MIN_MILESTONE);
      }
      // Upgrading from 2.x to 2.x
      // Ignore upgrades already applied
      else
      {
        $omit = $class::VERSION <= $version;
      }

      if ($omit)
      {
        if ($options['verbose'])
        {
          $this->logSection('upgrade-sql', sprintf('Omitting %s', $version));
        }

        // Bump database version anyways
        $this->updateDatabaseVersion(++$version);

        continue;
      }

      if ($options['verbose'])
      {
        $this->logSection('upgrade-sql', sprintf('Invoking upgrader (%s)', $version));
      }

      // Run migration
      // If an exception is thrown from $class, updateDatabaseVersion() won't make it
      if (true !== $class::up($this->configuration))
      {
        throw new sfException('Failed to apply upgrade '.get_class($class));
      }

      // Bump database version
      $this->updateDatabaseVersion(++$version);
    }

    if ($this->initialVersion == $version)
    {
      $this->logSection('upgrade-sql', sprintf('Already at latest version (%s), no upgrades done', $version));
    }
    else
    {
      $this->logSection('upgrade-sql', sprintf('Successfully upgraded to Release %s v%s', qubitConfiguration::VERSION, $version));

      $this->updateMilestone();
    }
  }

  /**
   * Figure out what's the last milestone used
   *
   * @return int Previous milestone (e.g. 1, 2)
   */
  protected function getPreviousMilestone()
  {
    // There is no doubt that the user is running 1.x if the initial database
    // version was 92 or lower (before the fork happened)
    if ($this->initialVersion <= 92)
    {
      $previousMilestone = 1;
    }
    // Otherwise, we'll look for the milestone in the database
    else
    {
      $sql = 'SELECT value
        FROM setting JOIN setting_i18n ON setting.id = setting_i18n.id
        WHERE name = "milestone";';

      $previousMilestone = QubitPdo::fetchColumn($sql);
    }

    return $previousMilestone;
  }

  /**
   * Update the settings with the latest database version
   *
   * @param int New database version
   */
  protected function updateDatabaseVersion($version)
  {
    $sql = 'UPDATE setting_i18n SET value = ? WHERE id = (SELECT id FROM setting WHERE name = ?);';
    QubitPdo::modify($sql, array($version, 'version'));
  }

  /**
   * Update the settings with the latest milestone
   */
  protected function updateMilestone()
  {
    // Get current codebase milestone
    $substrings = preg_split('/\./', qubitConfiguration::VERSION);
    $milestone = array_shift($substrings);

    // Run SQL query
    $sql = 'UPDATE setting_i18n SET value = ? WHERE id = (SELECT id FROM setting WHERE name = ?);';
    QubitPdo::modify($sql, array($milestone, 'milestone'));
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
}
