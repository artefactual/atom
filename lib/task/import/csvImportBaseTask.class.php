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
 * Import csv data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
abstract class csvImportBaseTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('filename', sfCommandArgument::REQUIRED, 'The input file (csv format).')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('rows-until-update', null, sfCommandOption::PARAMETER_OPTIONAL, 'Output total rows imported every n rows.'),
      new sfCommandOption('skip-rows', null, sfCommandOption::PARAMETER_OPTIONAL, 'Skip n rows before importing.'),
      new sfCommandOption('error-log', null, sfCommandOption::PARAMETER_OPTIONAL, 'File to log errors to.')
    ));
  }

  /**
   * Validate import-related options, throwing exceptions or warning when
   * appropriate
   *
   * @param array $options  options
   *
   * @return void
   */
  protected function validateOptions($options)
  {
    $numericOptions = array('rows-until-update', 'skip-rows');

    foreach($numericOptions as $option)
    {
      if ($options[$option] && !is_numeric($options[$option]))
      {
        throw new sfException($option .' must be an integer');
      }
    }

    if ($options['error-log'] && !is_dir(dirname($options['error-log'])))
    {
      throw new sfException('Path to error log is invalid.');
    }

    if ($this->acceptsOption('source-name') && !$options['source-name'])
    {
      print "WARNING: If you're importing multiple CSV files as part of the "
        ."same import it's advisable to use the source-name CLI option to "
        ."specify a source name (otherwise the filename will be used as a "
        . "source name).\n";
    }
  }

  /**
   * Checks to see if a particular option is supported
   *
   * @param string $name  option name
   *
   * @return boolean
   */
  protected function acceptsOption($name)
  {
    foreach($this->getOptions() as $option)
    {
      if ($name == $option->getName()) return true;
    }
    return false;
  }
}
