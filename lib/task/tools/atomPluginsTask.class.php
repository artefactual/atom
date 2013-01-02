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
 * Purge Qubit data
 *
 * @package    symfony
 * @subpackage task
 */
class atomPluginsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('action', sfCommandArgument::REQUIRED, 'The action (add, delete or list).'),
      new sfCommandArgument('plugin', sfCommandArgument::OPTIONAL, 'The plugin name.')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('action', null, sfCommandOption::PARAMETER_REQUIRED, 'Desired action')
    ));

    $this->namespace = 'tools';
    $this->name = 'atom-plugins';
    $this->briefDescription = 'Manage AtoM plugins.';

    $this->detailedDescription = <<<EOF
Manage AtoM plugins stored in the database. Examples:
 - symfony atom-plugins add arFoobarPlugin
 - symfony atom-plugins delete arFoobarPlugin
 - symfony atom-plugins list
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    // Retrieve QubitSetting object
    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'plugins');
    if (null === $setting = QubitSetting::getOne($criteria))
    {
      throw new sfException('Database entry could not be found.');
    }

    // Array of plugins
    $plugins = array_values(unserialize($setting->getValue(array('sourceCulture' => true))));

    if (in_array($arguments['action'], array('add', 'delete')) && !isset($arguments['plugin']))
    {
      throw new sfException('Missing plugin name.');
    }

    switch ($arguments['action'])
    {
      case 'add':
        $plugins[] = $arguments['plugin'];

        // Save changes
        $setting->setValue(serialize(array_unique($plugins)), array('sourceCulture' => true));
        $setting->save();

        break;

      case 'delete':
        if (false !== $key = array_search($arguments['plugin'], $plugins))
        {
          unset($plugins[$key]);
        }
        else
        {
          throw new sfException('Plugin could not be found.');
        }

        // Save changes
        $setting->setValue(serialize(array_unique($plugins)), array('sourceCulture' => true));
        $setting->save();

        break;

      case 'list':
        foreach ($plugins as $plugin)
        {
          echo $plugin."\n";
        }

        break;

      default:
        throw new sfException('Missing action');

    }
  }
}
