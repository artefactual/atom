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
 * Output a list of removed and added i18n messages to allow auditing of
 * changes, and preservation of translations data that may still be valid.
 * Output formats currrently include csv and tab to allow easy import into a
 * spreadsheet application.
 *
 * @package    AccesstoMemory
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 */
class i18nDiffTask extends sfBaseTask
{
  const FORMAT_CSV = 'csv';
  const FORMAT_TAB = 'tab';

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'The target culture'),
    ));

    $this->addOptions(array(
      new sfCommandOption('file', 'f', sfCommandOption::PARAMETER_OPTIONAL, 'Specify a destination filename for writing output', 'stdout'),
      new sfCommandOption('format', 'o', sfCommandOption::PARAMETER_OPTIONAL, 'Specify an output format (currently only supports csv & tab-delimited)', self::FORMAT_CSV)
    ));

    $this->namespace = 'i18n';
    $this->name = 'diff';
    $this->briefDescription = 'Compares existing XLIFF strings to new i18n strings extracted from PHP files as per the i18n:extract task.';

    $this->detailedDescription = <<<EOF
The [i18n:diff|INFO] task compares existing XLIFF strings to new i18n strings
extracted from PHP files for the given application and target culture:

  [./symfony i18n:diff frontend fr|INFO]

By default, the task outputs to STDOUT. To specify an destination file
use the [--file|COMMENT] or [-f|COMMENT] options:

  [./symfony i18n:diff --file="french_diff.csv" frontend fr|INFO]
  [./symfony i18n:diff -f="french_diff.csv" frontend fr|INFO]

By defulat, the task outputs the differences in CSV format. To specify an
alternate file format use the [--format|COMMENT] or [-t|COMMENT] options:

  [./symfony i18n:diff --format="tab" frontend fr|INFO]
  [./symfony i18n:diff -o="tab" frontend fr|INFO]

Possible [--format|COMMENT] values are "csv" and "tab".
EOF;
  }

  /**
   * @see sfTask
   * @see sfI18nExtract
   */
  public function execute($arguments = array(), $options = array())
  {
    $output = "";

    if (strtolower($options['file']) != 'stdout')
    {
      $this->logSection('i18n', sprintf('Diff i18n strings for the "%s" application', $arguments['application']));
    }

    // get i18n configuration from factories.yml
    $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));

    $class = $config['i18n']['class'];
    $params = $config['i18n']['param'];
    unset($params['cache']);

    $this->i18n = new $class($this->configuration, new sfNoCache(), $params);
    $extract = new sfI18nApplicationExtract($this->i18n, $arguments['culture']);
    $extract->extract();

    if (strtolower($options['file']) != 'stdout')
    {
      $this->logSection('i18n', sprintf('found "%d" new i18n strings', count($extract->getNewMessages())));
      $this->logSection('i18n', sprintf('found "%d" old i18n strings', count($extract->getOldMessages())));
    }

    // Column headers
    $rows[0] = array('Action', 'Source', 'Target');

    // Old messages
    foreach ($this->getOldTranslations($extract) as $source=>$target)
    {
      $rows[] = array('Removed', $source, $target);
    }

    // New messages
    foreach ($extract->getNewMessages() as $message)
    {
      $rows[] = array('Added', $message);
    }

    // Choose output format
    switch (strtolower($options['format']))
    {
      case 'csv':
        foreach ($rows as $row)
        {
          $output .= '"'.implode('","', array_map('addslashes', $row))."\"\n";
        }
        break;
      case 'tab':
        foreach ($rows as $row)
        {
          $output .= implode("\t", $row)."\n";
        }
        break;
    }

    // Output file
    if (strtolower($options['file']) != 'stdout')
    {
      echo "\n".$options['file'];
      // Remove '=' if using -f="file.csv" notation
      $filename = (substr($options['file'], 0, 1) == '=') ? substr($options['file'], 1) : $options['file'];
      file_put_contents($filename, $output);
    }
    else
    {
      echo $output;
    }
  }

  /**
   * Loads old translations currently saved in the message sources.
   *
   * @param sfI18nApplicationExtract $extract
   * @return array of source and target translations
   */
  public function getOldTranslations($extract)
  {
    $oldMessages = array_diff($extract->getCurrentMessages(), $extract->getAllSeenMessages());

    foreach ($this->i18n->getMessageSource()->read() as $catalogue => $translations)
    {
      foreach ($translations as $key => $value)
      {
        $allTranslations[$key] = $value[0];
      }
    }

    foreach ($oldMessages as $message)
    {
      $oldTranslations[$message] = $allTranslations[$message];
    }

    return $oldTranslations;
  }
}
