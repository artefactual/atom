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
 * Simplified of the i18nConsolidateTask that doesn't re-extract messages from
 * the source files.  However, there is no way currently to record the source
 * file for XLIFF strings. :(
 *
 * @package    symfony
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 * @version    SVN: $Id: i18nConsolidateSimpleTask.class.php 11573 2012-04-30 23:13:55Z david $
 */
class i18nConsolidateSimpleTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'Message culture'),
      new sfCommandArgument('target', sfCommandArgument::REQUIRED, 'Target directory')
    ));

    $this->addOptions(array(
      // http://trac.symfony-project.org/ticket/8352
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', true),
    ));

    $this->namespace = 'i18n';
    $this->name = 'consolidate-simple';
    $this->briefDescription = 'Simplified script to combine all application messages into a single output (XLIFF) file for ease of use by translators';

    $this->detailedDescription = <<<EOF
Combine all application messages into a single output (XLIFF) file for ease of use by translators.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    if (!file_exists($arguments['target']))
    {
      throw new sfException('Target directory "'.$arguments['target'].'" doesn\t exist');
    }

    $this->logSection('i18n', sprintf('Consolidating %s i18n messages', $arguments['culture']));

    // get i18n configuration from factories.yml
    $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));

    $class = $config['i18n']['class'];
    $params = $config['i18n']['param'];
    unset($params['cache']);

    // Extract i18n messages from php and yaml files (including plugins)
    $i18n = new $class($this->configuration, new sfNoCache(), $params);
    $consolidate= new i18nConsolidated($i18n, $arguments);
    $consolidate->save();
  }
}

class i18nConsolidated
{
  protected
    $culture,
    $inputI18n,
    $outputI18n,
    $sourceFiles = array();

  public function __construct($i18n, $arguments)
  {
    // Load input XLIFF files
    $this->inputI18n = $i18n;
    $this->inputI18n->setMessageSource($i18n->getConfiguration()->getI18NGlobalDirs(), $arguments['culture']);
    $this->inputI18n->getMessageSource()->load();

    // Create output file
    $this->consolidated = clone $i18n;
    $this->consolidated->setMessageSource(array($arguments['target']), $arguments['culture']);
  }

  public function save()
  {
    $messages = array();
    $translates = array();

    foreach ($this->inputI18n->getMessageSource()->read() as $catalogue => $translations)
    {
      foreach ($translations as $key => $values)
      {
        $messages[] = $key;

        // build associative array containing translated values
        if (!isset($translates[$key]) || 0 == strlen($translates[$key][0]))
        {
          $translates[$key] = $values;
        }
      }
    }

    // Get messages from data/fixtures
    foreach ($this->getTranslationsFromYaml(sfConfig::get('sf_data_dir').'/fixtures') as $key => $values)
    {
      $messages[] = $key;

      if (!isset($translates[$key]) || 0 == strlen($translates[$key][0]))
      {
        $translates[$key] = $values;
      }
    }

    // Sort and remove duplicates
    $messages = array_unique($messages);
    sort($messages);

    // Add sources to XLIFF file
    foreach ($messages as $message)
    {
      $this->consolidated->getMessageSource()->append($message);
    }

    // Save all sources to consolidated i18n file
    $this->consolidated->getMessageSource()->save();

    // Now save translated strings
    foreach ($translates as $key => $item)
    {
      $this->consolidated->getMessageSource()->update($key, $item[0], $item[2]);
    }
  }

  /**
   * Extracts i18n strings from YML fixtures
   *
   * @param string $dir The PHP full path name
   */
  protected function getTranslationsFromYaml($dir)
  {
    // Search for YAML files
    $files = sfFinder::type('file')->name('*.yml')->in($dir);

    if (0 == count($files))
    {
      $this->logSection('i18n', 'Warning: Couldn\'t find any fixture files.');

      return;
    }

    $translations = array();
    foreach ($files as $file)
    {
      $yaml = new sfYaml;
      $fixtures = $yaml->load($file);

      if (null == $fixtures)
      {
        continue;
      }

      // Descend through fixtures hierarchy
      foreach ($fixtures as $classname => $fixture)
      {
        foreach ($fixture as $key => $item)
        {
          $values = null;

          // translated column varies by object type
          switch ($classname)
          {
            case 'QubitAclGroup':
            case 'QubitTaxonomy':
            case 'QubitTerm':
              $values = $item['name'];
              break;

            case 'QubitMenu':
              $values = $item['label'];
              break;

            case 'QubitSetting':
              $values = $item['value'];
              break;
          }

          // Ignore non-i18n values
          if (!isset($values) || !is_array($values) || !isset($values['en']))
          {
            continue;
          }

          $target = null;
          if (isset($values[$this->culture]))
          {
            $target = $values[$this->culture];
          }

          // Mimic XLIFF translation array structure: (target, id, note)
          $translations[$values['en']] = array(
            $target,
            null,
            str_replace(sfConfig::get('sf_web_dir'), 'http://code.google.com/p/qubit-toolkit/source/browse/trunk', $file));
        }
      }
    }

    return $translations;
  }
}
