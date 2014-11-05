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
 * Purge AtoM data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class purgeTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('use-gitconfig', null, sfCommandOption::PARAMETER_NONE, 'Get username and email from $HOME/.gitconfig'),
      new sfCommandOption('title', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired site title'),
      new sfCommandOption('description', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired site description'),
      new sfCommandOption('url', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired site base URL'),
      new sfCommandOption('username', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired admin username'),
      new sfCommandOption('email', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired admin email address'),
      new sfCommandOption('password', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired admin password'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('demo', null, sfCommandOption::PARAMETER_NONE, 'Use default demo values, do not ask for confirmation')
    ));

    $this->namespace = 'tools';
    $this->name = 'purge';
    $this->briefDescription = 'Purge all data.';

    $this->detailedDescription = <<<EOF
Purge all data.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    if (!function_exists('readline'))
    {
      throw new Exception('This tasks needs the PHP readline extension.');
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $insertSql = new sfPropelInsertSqlTask($this->dispatcher, $this->formatter);
    $insertSql->setCommandApplication($this->commandApplication);
    $insertSql->setConfiguration($this->configuration);

    if ($options['demo'])
    {
      $this->setDemoOptions($options);
    }

    $insertSqlArguments = array();
    $insertSqlOptions = array('no-confirmation' => $options['no-confirmation']);

    $insertSql->run($insertSqlArguments, $insertSqlOptions);

    if ($options['use-gitconfig'])
    {
      // attempt to provide default user admin name and email
      if ($_SERVER['HOME'])
      {
        $gitConfigFile = $_SERVER['HOME'] .'/.gitconfig';
        if (file_exists($gitConfigFile))
        {
          $gitConfig = parse_ini_file($gitConfigFile);

          $defaultUser = strtolower(strtok($gitConfig['name'], ' '));
          $defaultEmail = $gitConfig['email'];
        }
      }
    }

    $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], false);
    $sf_context = sfContext::createInstance($configuration);

    QubitSearch::disable();

    sfInstall::loadData();

    QubitSearch::enable();

    // Flush search index
    QubitSearch::getInstance()->flush();
    $this->logSection('purge', 'The search index has been deleted.');

    // set, or prompt for, site title configuration information
    $siteTitle = (isset($options['title'])) ? $options['title'] : '';
    if (!$siteTitle)
    {
      $siteTitle = readline("Site title [Qubit]: ");
      $siteTitle = (!empty($siteTitle)) ? $siteTitle : 'Qubit';
    }

    // set, or prompt for, site description information
    $siteDescription = (isset($options['description'])) ? $options['description'] : '';
    if (!$siteDescription)
    {
      $siteDescription = readline("Site description [Test site]: ");
      $siteDescription = (!empty($siteDescription)) ? $siteDescription : 'Test site';
    }

    // set, or prompt for, site base URL
    $siteBaseUrl = (isset($options['url'])) ? $options['url'] : '';
    if (!$siteBaseUrl)
    {
      $siteBaseUrl = readline("Site base URL [http://127.0.0.1]: ");
      $siteBaseUrl = (!empty($siteBaseUrl)) ? $siteBaseUrl : 'http://127.0.0.1';
    }

    $this->createSetting('siteTitle', $siteTitle);
    $this->createSetting('siteDescription', $siteDescription);
    $this->createSetting('siteBaseUrl', $siteBaseUrl);

    print "\n";

    addSuperuserTask::addSuperUser($options['username'], $options);

    $this->logSection('propel', 'Purge complete!');
  }

  /*
   * Helper to create a system setting
   *
   * @param string $name  Name of setting
   * @param string $value  Value of setting
   */
  protected function createSetting($name, $value)
  {
    $setting = new QubitSetting();
    $setting->name = $name;
    $setting->value = $value;
    $setting->save();
  }

  /**
   * Set the site to have default demo site values,
   * i.e. admin user is demo@example.com / demo.
   */
  private function setDemoOptions(&$options)
  {
    $options['no-confirmation'] = true;
    $options['title'] = 'Demo site';
    $options['description'] = 'Demo site';
    $options['email'] = 'demo@example.com';
    $options['username'] = 'demo';
    $options['password'] = 'demo';
    $options['url'] = 'http://127.0.0.1';
  }
}
