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
class I18nRemoveDuplicatesTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      // http://trac.symfony-project.org/ticket/8352
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'remove-duplicates';
    $this->briefDescription = 'Delete duplicate source messages';

    $this->detailedDescription = <<<EOF
FIXME
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->logSection('i18n', sprintf('Removing duplicate i18n sources for the "%s" application', $options['application']));

    // Loop through plugins
    $pluginNames = sfFinder::type('dir')->maxdepth(0)->relative()->not_name('.')->in(sfConfig::get('sf_plugins_dir'));
    foreach ($pluginNames as $pluginName)
    {
      $this->logSection('i18n', sprintf('Removing %s duplicates', $pluginName));

      foreach (sfFinder::type('files')->in(sfConfig::get('sf_plugins_dir').'/'.$pluginName.'/i18n') as $file)
      {
        self::deleteDuplicateSource($file);
      }
    }
  }

  public function deleteDuplicateSource($filename)
  {
    $modified = false;

    // create a new dom, import the existing xml
    $doc = new DOMDocument;
    $doc->formatOutput = true;
    $doc->preserveWhiteSpace = false;
    $doc->load($filename);

    $xpath = new DOMXPath($doc);

    foreach ($xpath->query('//trans-unit') as $unit)
    {
      foreach ($xpath->query('./target', $unit) as $target)
      {
        break; // Only one target
      }

      foreach ($xpath->query('./source', $unit) as $source)
      {
        // If this is a duplicate source key, then delete it
        if (isset($sourceStrings[$source->nodeValue]))
        {
          // If original target string is null, but *this* node has a valid
          // translation
          if (0 == strlen($sourceStrings[$source->nodeValue]->nodeValue) &&
            0 < strlen($target->nodeValue))
          {
            // Copy this translated string to the trans-unit node we are keeping
            $sourceStrings[$source->nodeValue]->nodeValue = $target->nodeValue;
          }

          // Remove duplicate
          $unit->parentNode->removeChild($unit);
          $modified = true;
        }
        else
        {
          $sourceStrings[$source->nodeValue] = $target;
        }

        break; // Only one source
      }
    }

    // Update xliff file if modified
    if ($modified)
    {
      $fileNode = $xpath->query('//file')->item(0);
      $fileNode->setAttribute('date', @date('Y-m-d\TH:i:s\Z'));

      $doc->save($filename);
    }
  }

}
