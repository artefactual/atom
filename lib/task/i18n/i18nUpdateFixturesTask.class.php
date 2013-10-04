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
 * Restore i18n strings lost when XLIFF files were broken into plugin-specific
 * directories
 *
 * @package    AccesstoMemory
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 */
class I18nUpdateFixturesTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('path', sfCommandArgument::REQUIRED, 'Path for xliff files'),
    ));

    $this->addOptions(array(
      // http://trac.symfony-project.org/ticket/8352
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('filename', 'f', sfCommandOption::PARAMETER_OPTIONAL, 'Name of XLIFF files', 'messages.xml'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'update-fixtures';
    $this->briefDescription = 'Reads XLIFF files from {{path}} and merges translations to database fixture files';

    $this->detailedDescription = <<<EOF
Reads XLIFF files from {{path}} and merges translations to database fixture files
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    // Extract translation strings from XLIFF files
    $translations = $this->extractTranslations($arguments, $options);

    $this->updateFixtures($translations);
  }

  protected function extractTranslations($arguments = array(), $options = array())
  {
    $translations = array();

    $this->logSection('i18n', sprintf('Find XLIFF files named "%s"', $options['filename']));

    // Search for xliff files
    $files = sfFinder::type('file')->name($options['filename'])->in($arguments['path']);

    if (0 == count($files))
    {
      $this->logSection('i18n', 'No valid files found.  Please check path and filename');

      return;
    }

    // Extract translation strings
    foreach ($files as $file)
    {
      $culture = self::getTargetCulture($file);
      $xliff = new sfMessageSource_XLIFF(substr($file, 0, strrpos('/')));

      if (!($messages = $xliff->loadData($file)))
      {
        continue;
      }

      // Build list of translations, keyed on source value
      foreach ($messages as $source => $message)
      {
        if (0 < strlen($message[0]))
        {
          $translations[$source][$culture] = trim($message[0]);
        }
      }
    }

    return $translations;
  }

  protected function updateFixtures($translations)
  {
    $this->logSection('i18n', 'Writing new translations to fixtures...');

    // Search for YAML files
    $fixturesDirs = array_merge(array(sfConfig::get('sf_data_dir').'/fixtures'), $this->configuration->getPluginSubPaths('/data/fixtures'));
    $files = sfFinder::type('file')->name('*.yml')->in($fixturesDirs);

    if (0 == count($files))
    {
      $this->logSection('i18n', 'Error: Couldn\'t find any fixture files to write.');

      return;
    }

    // Merge translations to YAML files in data/fixtures
    foreach ($files as $file)
    {
      $modified = false;
      $yaml = new sfYaml;
      $fixtures = $yaml->load($file);

      // Descend through fixtures hierarchy
      foreach ($fixtures as $classname => &$fixture)
      {
        foreach ($fixture as $key => &$columns)
        {
          foreach ($columns as $column => &$value)
          {
            if (is_array($value) && isset($value['en']))
            {
              if (isset($translations[$value['en']]))
              {
                $value = array_merge($value, $translations[$value['en']]);

                // Sort keys alphabetically
                ksort($value);

                $modified = true;
              }
            }
          }
        }
      }

      if ($modified)
      {
        $this->logSection('i18n', sprintf('Updating %s...', $file));

        $contents = $yaml->dump($fixtures, 4);

        if (0 < strlen($contents))
        {
          file_put_contents($file, $contents);
        }
      }
    }

    return $this;
  }

  protected static function getTargetCulture($filename)
  {
    libxml_use_internal_errors(true);
    if (!$xml = simplexml_load_file($filename))
    {
      return;
    }
    libxml_use_internal_errors(false);

    $code = (string) $xml->file['target-language'];

    // It looks like Transifex leaves the target-language property emtpy, is
    // that intentional? For now, I'm going to extract the target based in the
    // path of the file.
    if (empty($code))
    {
      if (1 === preg_match("/\/(?P<code>[a-zA-Z_]+)\/messages\.xml$/m", $filename, $matches))
      {
        if (isset($matches['code']))
        {
          $code = $matches['code'];
        }
      }
    }

    return $code;
  }
}
