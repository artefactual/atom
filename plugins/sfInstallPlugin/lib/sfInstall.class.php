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

// TODO Integrate with symfony/data/bin/check_configuration.php
class sfInstall
{
  public static $MINIMUM_MEMORY_LIMIT_MB = 64;

  // Returns an array of missing dependencies
  // TODO This is already implemented in PEAR.  Make this check more robust by
  // calling their code.
  // TODO Break this up so we can give more detailed output (like which
  // extensions *are* loaded?
  public static function checkDependencies()
  {
    require_once sfConfig::get('sf_root_dir').'/vendor/FluentDOM/FluentDOM.php';

    $dependencies = array();

    // Check if any dependencies are defined
    $packageXmlPath = sfConfig::get('sf_config_dir').'/package.xml';
    if (!file_exists($packageXmlPath))
    {
      return $dependencies;
    }

    $fd = FluentDOM($packageXmlPath)
      ->namespaces(array('p' => 'http://pear.php.net/dtd/package-2.0'));

    // Check if a minimum PHP version is defined, and if it is less than our
    // current version
    if (0 < strlen($min = $fd->find('p:dependencies/p:required/p:php/p:min')) && 0 > version_compare(PHP_VERSION, $min))
    {
      $dependencies['php']['min'] = $min;
    }

    foreach ($fd->find('p:dependencies/*/p:extension/p:name') as $node)
    {
      if (!extension_loaded($node->textContent))
      {
        $dependencies['extensions'][] = $node->textContent;
      }
    }

    return $dependencies;
  }

  public static function checkWritablePaths()
  {
    // TODO This is a late night hack.  It should probably get moved into its
    // own check
    // Copied from sfFileLogger::initialize()
    if (!is_dir(sfConfig::get('sf_log_dir')))
    {
      mkdir(sfConfig::get('sf_log_dir'), 0777, true);
    }

    $writablePaths = array();

    $finder = sfFinder::type('any');

    foreach (array(sfConfig::get('sf_cache_dir'), sfConfig::get('sf_data_dir'), sfConfig::get('sf_log_dir')) as $path)
    {
      // TODO sfFinder::in() does not include the argument path
      if (!is_writable($path))
      {
        $writablePaths[] = $path;
      }

      foreach ($finder->in($path) as $path)
      {
        if (!is_writable($path))
        {
          $writablePaths[] = $path;
        }
      }
    }

    return $writablePaths;
  }

  public static function checkDatabasesYml()
  {
    $databasesYml = array();

    $databasesYmlPath = sfConfig::get('sf_config_dir').'/databases.yml';

    // Read databases.yml contents from existing databases.yml,
    // databases.yml.tmpl (for a Subversion checkout), or symfony skeleton
    // databases.yml, whichever is found first
    $databasesYmlPaths = array();
    $databasesYmlPaths[] = $databasesYmlPath;
    $databasesYmlPaths[] = $databasesYmlPath.'.tmpl';
    $databasesYmlPaths[] = sfConfig::get('sf_lib_dir').'/task/generator/skeleton/project/config/databases.yml';

    foreach ($databasesYmlPaths as $path)
    {
      if (false !== $databasesYmlContents = file_get_contents($path))
      {
        break;
      }
    }

    if (false === file_put_contents($databasesYmlPath, $databasesYmlContents))
    {
      $databasesYml['notWritable'] = 'notWritable';
    }

    return $databasesYml;
  }

  public static function checkPropelIni()
  {
    $propelIni = array();

    $propelIniPath = sfConfig::get('sf_config_dir').'/propel.ini';

    // Read propel.ini contents from existing propel.ini, propel.ini.tmpl (for
    // a Subversion checkout), or symfony skeleton propel.ini, whichever is
    // found first
    $propelIniPaths = array();
    $propelIniPaths[] = $propelIniPath;
    $propelIniPaths[] = $propelIniPath.'.tmpl';
    $propelIniPaths[] = sfConfig::get('sf_lib_dir').'/task/generator/skeleton/project/config/propel.ini';

    foreach ($propelIniPaths as $path)
    {
      if (false !== $propelIniContents = file_get_contents($path))
      {
        break;
      }
    }

    if (false === file_put_contents($propelIniPath, $propelIniContents))
    {
      $propelIni['notWritable'] = 'notWritable';
    }

    return $propelIni;
  }

  // TODO Use sfWebBrowserPlugin
  protected static function get($url)
  {
    $request = sfContext::getInstance()->request;
    $browser = new sfWebBrowser;

    try
    {
      if (true !== $browser->get($request->getUriPrefix().$url)->responseIsError())
      {
        // Successful response (e.g. 200, 201, etc.)
        return $browser->getResponseText();
      }
      else
      {
        // Error response (e.g. 404, 500, etc.)
        return false;
      }
    }
    catch (Exception $e)
    {
      // Adapter error [curl,fopen,fsockopen] (e.g. Host not found)
      return false;
    }
  }

  public static function setNoScriptName($noScriptName, $settingsYmlContents)
  {
    // TODO Make this pattern more robust, or parse the YAML?
    $pattern = '/^(prod:\v+  .settings:\v+    no_script_name:\h*)[^\v]+/';
    $replacement = '\1'.($noScriptName ? 'on' : 'off');

    return preg_replace($pattern, $replacement, $settingsYmlContents);
  }

  public static function checkSettingsYml($noScriptName)
  {
    $settingsYml = array();

    $settingsYmlPath = sfConfig::get('sf_app_config_dir').'/settings.yml';

    // Read settings.yml contents from existing settings.yml, settings.yml.tmpl
    // (for a Subversion checkout), or symfony skeleton settings.yml, whichever
    // is found first
    $settingsYmlPaths = array();
    $settingsYmlPaths[] = $settingsYmlPath;
    $settingsYmlPaths[] = $settingsYmlPath.'.tmpl';
    $settingsYmlPaths[] = sfConfig::get('sf_lib_dir').'/task/generator/skeleton/app/app/config/settings.yml';

    foreach ($settingsYmlPaths as $path)
    {
      if (false !== $settingsYmlContents = file_get_contents($path))
      {
        break;
      }
    }

    $settingsYmlContents = sfInstall::setNoScriptName($noScriptName, $settingsYmlContents);

    if (false === file_put_contents($settingsYmlPath, $settingsYmlContents))
    {
      $settingsYml['notWritable'] = 'notWritable';
    }

    chdir(sfConfig::get('sf_root_dir'));

    // TODO By instantiating a new application configuration the cache clear
    // task may change these settings, leading to bugs in code which expects
    // them to remain constant
    $saveDebug = sfConfig::get('sf_debug');
    $saveLoggingEnabled = sfConfig::get('sf_logging_enabled');

    // TODO We do not want to cache anything during install, but currently we
    // must clear the cache after adding enabling sfInstallPlugin : (
    $cacheClear = new sfCacheClearTask(sfContext::getInstance()->getEventDispatcher(), new sfFormatter());
    $cacheClear->run();

    sfConfig::set('sf_debug', $saveDebug);
    sfConfig::set('sf_logging_enabled', $saveLoggingEnabled);

    // Clear PHP opcode cache. This was added to correct issue where occasionally
    // during installation, the cache will contain the vendor skeleton .yml
    // files, which override the AtoM config files that get written to the cache
    // folder during install. This prevents the i18n and Qubit helpers being
    // loaded (from apps/qubit/config/settings.yml) triggering the i18n errors.
    if (function_exists('opcache_reset'))
    {
      opcache_reset();
    }
    if (function_exists('apcu_clear_cache'))
    {
      apcu_clear_cache();
    }

    return $settingsYml;
  }

  /**
   * Check that memory_limit ini value meets Qubit's minimum requirements
   * (currently 64 MB)
   *
   * @return current memory limit if less than 64M
   */
  public static function checkMemoryLimit()
  {
    $memoryLimit = ini_get('memory_limit');

    // Convert memoryLimit to float or integer value in units of MB
    // See http://ca.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
    switch (strtolower(substr($memoryLimit, -1)))
    {
      case 'k':
        $memoryLimit = round(intval(substr($memoryLimit, 0, -1)) / 1024, 3);

        break;

      case 'm':
        $memoryLimit = intval(substr($memoryLimit, 0, -1));

        break;

      case 'g':
        $memoryLimit = intval(substr($memoryLimit, 0, -1)) * 1024;

        break;

      default:
        // If suffix is not K, M, or G (case-insensitive), then value is assumed to be bytes
        $memoryLimit = round(intval($memoryLimit) / 1048576, 3);
    }

    if ($memoryLimit < self::$MINIMUM_MEMORY_LIMIT_MB)
    {
      return $memoryLimit;
    }
  }

  public static function configureDatabase(array $options = array())
  {
    $database = array();
    $configFile = sfConfig::get('sf_config_dir').'/config.php';

    $configHandler = new sfInstallDatabaseConfigHandler;

    sfInstallDatabaseConfigHandler::$options = $options;
    file_put_contents($configFile, $configHandler->execute(ProjectConfiguration::getActive()->getConfigPaths('config/databases.yml')));

    // Invalidate cache
    if (function_exists('opcache_invalidate'))
    {
      $e = opcache_invalidate($configFile, true);
    }

    $databaseManager = sfContext::getInstance()->databaseManager;

    // TODO Currently need to reload after configuring the database
    $databaseManager->loadConfiguration();

    try
    {
      sfContext::getInstance()->getDatabaseConnection('propel');
    }
    catch (Exception $e)
    {
      $database[] = $e;
    }

    return $database;
  }

  public static function configureSearch(array $options = array())
  {
    $errors = array();
    $defaults = sfConfig::get('sf_plugins_dir').'/arElasticSearchPlugin/config/search.yml';
    $config = arElasticSearchConfigHandler::getConfiguration(array($defaults));

    if (isset($options['searchHost']))
    {
      $config['server']['host'] = $options['searchHost'];
    }
    else
    {
      $config['server']['host'] = 'localhost';
    }

    if (isset($options['searchPort']))
    {
      $config['server']['port'] = $options['searchPort'];
    }
    else
    {
      $config['server']['port'] = '9200';
    }

    // Quick check of connectivity, I should put this somewhere else :)
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, sprintf("http://%s", $config['server']['host']));
    curl_setopt($curl, CURLOPT_PORT, $config['server']['port']);
    curl_setopt($curl, CURLOPT_HEADER, false);
    ob_start();
    curl_exec($curl);
    $response = json_decode(ob_get_clean());
    if (0 < curl_errno($curl))
    {
      $errors[] = sprintf("Can't connect to the server (%s).", curl_error($curl));
    }

    if (0 < count($errors))
    {
      return $errors;
    }

    $curlHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if (200 !== $curlHttpCode)
    {
      $errors[] = "Elasticsearch error: " . $curlHttpCode;
    }

    if (0 < count($errors))
    {
      return $errors;
    }

    if (isset($options['searchIndex']))
    {
      $config['index']['name'] = $options['searchIndex'];
    }
    else
    {
      $config['index']['name'] = 'atom';
    }

    $env = array();
    $env['all'] = $config;

    $location = sfConfig::get('sf_config_dir').'/search.yml';
    if (false === file_put_contents($location, sfYaml::dump($env, 9)))
    {
      $errors[] = "Can't write configuration file ".$location;
    }

    return $errors;
  }

  public static function insertSql()
  {
    $arguments = array();

    $options = array();
    $options[] = 'no-confirmation';

    $dispatcher = sfContext::getInstance()->getEventDispatcher();
    $formatter = new sfAnsiColorFormatter;

    chdir(sfConfig::get('sf_root_dir'));

    $insertSql = new sfPropelInsertSqlTask($dispatcher, $formatter);
    $insertSql->run($arguments, $options);
  }

  public static function loadData()
  {
    QubitSearch::disable();

    $object = new QubitInformationObject;
    $object->id = QubitInformationObject::ROOT_ID;
    $object->save();

    $object = new QubitActor;
    $object->id = QubitActor::ROOT_ID;
    $object->save();

    $object = new QubitRepository;
    $object->id = QubitRepository::ROOT_ID;
    $object->save();

    $object = new QubitSetting;
    $object->name = 'plugins';
    $object->value = serialize(array(
      'sfDcPlugin',
      'arDominionPlugin',
      'sfEacPlugin',
      'sfEadPlugin',
      'sfIsaarPlugin',
      'sfIsadPlugin',
      'arDacsPlugin',
      'sfIsdfPlugin',
      'sfIsdiahPlugin',
      'sfModsPlugin',
      'sfRadPlugin',
      'sfSkosPlugin'));
    $object->save();

    $dispatcher = sfContext::getInstance()->getEventDispatcher();
    $formatter = new sfAnsiColorFormatter;

    chdir(sfConfig::get('sf_root_dir'));

    $loadData = new sfPropelDataLoadTask($dispatcher, $formatter);
    $loadData->run();

    $premisAccessRightValues = array();
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item)
    {
      $premisAccessRightValues[$item->slug] = array(
        'allow_master'          => 1,
        'allow_reference'       => 1,
        'allow_thumb'           => 1,
        'conditional_master'    => 0,
        'conditional_reference' => 1,
        'conditional_thumb'     => 1,
        'disallow_master'       => 0,
        'disallow_reference'    => 0,
        'disallow_thumb'        => 0
      );
    }
    $setting = new QubitSetting;
    $setting->name = 'premisAccessRightValues';
    $setting->sourceCulture = sfConfig::get('sf_default_culture');
    $setting->setValue(serialize($premisAccessRightValues), array('sourceCulture' => true));
    $setting->save();

    $accessDisallowWarning = sfContext::getInstance()->i18n->__('Access to this record is restricted because it contains personal or confidential information. Please contact the Reference Archivist for more information on accessing this record.');
    $accessConditionalWarning = sfContext::getInstance()->i18n->__('This record has not yet been reviewed for personal or confidential information. Please contact the Reference Archivist to request access and initiate an access review.');
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item)
    {
      $setting = new QubitSetting;
      $setting->name = "{$item->slug}_disallow";
      $setting->scope = 'access_statement';
      $setting->setValue($accessDisallowWarning, array('culture' => 'en'));

      foreach (QubitI18N::getTranslations($accessDisallowWarning) as $langCode => $message)
      {
        $setting->setValue($message, array('culture' => $langCode));
      }

      $setting->save();

      $setting = new QubitSetting;
      $setting->name = "{$item->slug}_conditional";
      $setting->scope = 'access_statement';
      $setting->setValue($accessConditionalWarning, array('culture' => 'en'));

      foreach (QubitI18N::getTranslations($accessConditionalWarning) as $langCode => $message)
      {
        $setting->setValue($message, array('culture' => $langCode));
      }

      $setting->save();
    }
  }

  public static function populateSearchIndex()
  {
    QubitSearch::enable();
    QubitSearch::getInstance()->populate();
  }

  public static function addSymlinks()
  {
    if (!function_exists('symlink'))
    {
      return;
    }

    $symlinks = array('sf' => sfConfig::get('sf_root_dir').'/vendor/symfony/data/web/sf');

    foreach ($symlinks as $name => $path)
    {
      symlink($path, $name);
    }

    return $symlinks;
  }
}
