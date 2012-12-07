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
 * Migrate qubit data model
 *
 * @package    AccesstoMemory
 * @subpackage migration
 * @author     David Juhasz <david@artefactual.com>
 */
class MigrateTask extends sfBaseTask
{
  const
    FINAL_VERSION = 62,
    NOTICE_FINAL_VERSION = <<<EOL

Notice: This migration script will only migrate your data to version Release 1.1 v%s.
To migrate to Release 1.2 or later, please see the upgrading documentation:

http://accesstomemory.org/wiki/index.php?title=Upgrading

EOL;

  protected
    $data,
    $dataModified = false,
    $initialVersion,
    $targetVersion,
    // list of migratable releases
    $validReleases = array(
      '1.0.3',
      '1.0.4',
      '1.0.5',
      '1.0.6',
      '1.0.7',
      '1.0.8'
    );

  /**
   * @see sfBaseTask
   */
  protected function configure()
  {
    $this->namespace = 'propel';
    $this->name = 'migrate';
    $this->briefDescription = 'Migrate the database schema and existing data for compatibility with a newer version of Qubit.';
    $this->detailedDescription = <<<EOF
The [propel:migrate|INFO] task modifies the given YAML dump file with changes to the data structure and fixtures in subsequent versions of the application:

  [./symfony propel:migrate qubit_data_1.0.8.yml|INFO]
EOF;

    $this->addArguments(array(
      new sfCommandArgument('datafile', sfCommandArgument::REQUIRED, 'The yaml data file containing the current site data')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('target-version', 'T', sfCommandOption::PARAMETER_OPTIONAL, 'Specify the target version for the migrated data')
    ));
  }

  /**
   * @see sfBaseTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!is_readable($arguments['datafile']))
    {
      throw new Exception('The file '.$arguments['datafile'].' is not readable.');
    }

    // load yml dumpfile into an array ($this->data)
    $yamlParser = new sfYamlParser;
    $this->data = $yamlParser->parse(file_get_contents($arguments['datafile']));

    // Determine current version of the application (according to settings)
    if (null !== $this->initialVersion = $this->getDataVersion())
    {
      $this->logSection('migrate', 'Initial data version '.$this->initialVersion);
    }

    if (self::FINAL_VERSION <= $this->initialVersion)
    {
      $this->logBlock(array_merge(
        explode("\n", sprintf(self::NOTICE_FINAL_VERSION, self::FINAL_VERSION)),
        array('Exiting.', '')), 'ERROR');

      return 1;
    }

    // Get target application version for data migration
    if (isset($options['target-version']))
    {
      if (in_array($options['target-version'], $this->validReleases))
      {
        $this->targetVersion = $options['target-version'];
      }

      else if (!preg_match('/^\d+$/', $options['target-version']))
      {
        $this->logBlock(sprintf('Invalid target version "%s".', $options['target-version']), 'ERROR');

        return 1;
      }

      // Version 62 is the last valid version for this migration script
      if (self::FINAL_VERSION < intval($options['target-version']))
      {
        if (!$this->askConfirmation(array_merge(
          explode("\n", sprintf(self::NOTICE_FINAL_VERSION, self::FINAL_VERSION)),
          array(sprintf('Do you want to proceed with migrating your data to version %s (Y/n)?', self::FINAL_VERSION), '')),
          'QUESTION_LARGE'))
        {
          $this->log('Halting migration.');

          return 1;
        }

        $this->targetVersion = self::FINAL_VERSION;
      }
      else
      {
        $this->targetVersion = $options['target-version'];
      }
    }

    // At version 1.0.8 we switched from versioning by release to fine-grained
    // versions (integer)
    if (preg_match('/^\d+$/', $this->initialVersion))
    {
      $this->migrateFineGrained();
    }
    else
    {
      $this->migratePre108();

      if (null == $this->targetVersion || preg_match('/^\d+$/', $this->targetVersion))
      {
        $this->migrateFineGrained();
      }
    }

    // Write new data.yml file (if data was modified)
    if ($this->dataModified)
    {
      $this->writeMigratedData($arguments['datafile']);
    }
    else
    {
      $this->logSection('migrate', 'The specified data file is up-to-date, no migration done.');
    }
  }

  protected function migratePre108()
  {
    // If target release is not set, then use last major milestone
    $targetVersion = $this->targetVersion;
    if (null == $targetVersion)
    {
      $targetVersion = '1.0.8';
    }

    // Incrementally call the upgrade task for each intervening release from
    // initial release to the target release
    $initialIndex = array_search($this->initialVersion, $this->validReleases);
    if (false === $initialIndex)
    {
      $initialIndex = count($this->validReleases) - 2;
    }

    $finalIndex = array_search($targetVersion, $this->validReleases);

    if ($initialIndex !== false && $finalIndex !== false && $initialIndex < $finalIndex)
    {
      for ($i = $initialIndex; $i < $finalIndex; $i++)
      {
        $this->migrator = QubitMigrateFactory::getMigrator($this->data, $this->validReleases[$i]);
        $this->data = $this->migrator->execute();
        $this->dataModified = true;

        // Set release
        if ('1.0.7' == $this->validReleases[$i])
        {
          $this->version = 0; // After 1.0.7 use integer versions
        }
        else
        {
          $this->version = $this->validReleases[$i+1];
        }

        $this->logSection('migrate', 'Data migrated to Release '.$this->validReleases[$i+1]);
      }
    }
  }

  protected function migrateFineGrained()
  {
    if (preg_match('/^\d+$/', $this->initialVersion))
    {
      $this->version = $this->initialVersion;
    }
    else
    {
      $this->version = 0;
    }

    while (self::FINAL_VERSION > $this->version && (null === $this->targetVersion || $this->targetVersion > $this->version))
    {
      $migrator = QubitMigrateFactory::getMigrator($this->data, $this->version);

      $this->data = $migrator->execute();
      $this->dataModified = true;

      $this->version = $migrator::FINAL_VERSION;
    }

    if (null == $this->version)
    {
      // Set version to value in data/fixtures/settings.yml
      $parser = new sfYamlParser;
      $data = $parser->parse(file_get_contents(sfConfig::get('sf_data_dir').'/fixtures/settings.yml'));
      $this->version = $data['QubitSetting']['version']['value'];
    }

    $milestone = $migrator::MILESTONE;

    // Hack for Release 1.2
    if (self::FINAL_VERSION == $migrator::FINAL_VERSION)
    {
      $milestone = '1.2';
    }

    $this->logSection('migrate', 'Data migrated to Release '.$milestone.' v'.$this->version);
  }

  protected function writeMigratedData($originalFileName)
  {
    $migratedFileName = 'migrated_data_'.date('YmdHis').'.yml';
    $dir = dirname($originalFileName);
    $migratedFileName = $dir.DIRECTORY_SEPARATOR.$migratedFileName;

    $this->setDataVersion();

    $yamlDumper = new sfYamlDumper();
    $yamlData = sfYaml::dump($this->data, 3);

    // Remove single quotes from <?php statements to prevent errors on load
    $yamlData = preg_replace("/'(\<\?php echo .+ \?\>)'/", '$1', $yamlData);

    file_put_contents($migratedFileName, $yamlData);
    $this->logSection('migrate', 'Migrated data written to "'.$migratedFileName.'"');
  }

  protected function getDataVersion()
  {
    $currentVersion = null;
    foreach ($this->data['QubitSetting'] as $setting)
    {
      if ($setting['name'] == 'version')
      {
        if (preg_match('/^\d+$/', $setting['value']['en'], $matches))
        {
          $currentVersion = $matches[0];
        }
        else if (preg_match('/\d\.\d(\.\d)?/', $setting['value']['en'], $matches))
        {
          $currentVersion = $matches[0];
        }

        break;
      }
    }

    return $currentVersion;
  }

  protected function setDataVersion()
  {
    foreach ($this->data['QubitSetting'] as $key => $value)
    {
      if ('version' == $value['name'])
      {
        $version = $value['value'][$value['source_culture']];

        break;
      }
    }

    $this->data['QubitSetting'][$key]['value'][$this->data['QubitSetting'][$key]['source_culture']] = $this->version;

    return $this;
  }
}
