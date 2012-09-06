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
 * Import a CSV file into Qubit.
 * This class is a wrapper to interact with the import CSV tasks located
 * under the lib/task/import directory
 *
 * @package    Qubit
 * @subpackage library
 * @version    svn:$Id: QubitCsvImport.class.php 9112 2011-05-20 01:35:23Z mj $
 */
class QubitCsvImport
{
  protected
    $errors = null,
    $rootObject = null,
    $parent = null;

  public function import($csvFile, $type = null)
  {
    // Find the proper task
    switch ($type)
    {
      case 'accession':
        $taskClassName = 'csv:accession-import';

        break;

      case 'actor':
        $taskClassName = 'csv:authority-import';

        break;

      case 'event':
        $taskClassName = 'csv:event-import';

        break;

      case 'informationObject':
      default:
        $taskClassName = 'csv:import';

        break;
    }

    // Build command string
    if (isset($this->parent))
    {
      // Example: php symfony csv:import --default-parent-slug="$sourceName" /tmp/foobar
      $command = sprintf('php %s %s --quiet --source-name=%s --default-parent-slug=%s %s',
        escapeshellarg(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'symfony'),
        escapeshellarg($taskClassName),
        escapeshellarg($csvFile),
        escapeshellarg($this->parent->slug),
        escapeshellarg($csvFile));
    }
    else
    {
      // Example: php symfony csv:import /tmp/foobar
      $command = sprintf('php %s %s --quiet --source-name=%s %s',
        escapeshellarg(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'symfony'),
        escapeshellarg($taskClassName),
        escapeshellarg($csvFile),
        escapeshellarg($csvFile));
    }

    // stderr to stdout
    $command .= ' 2>&1';

    // Run
    exec($command, $output, $exitCode);

    // Throw exception if exit code is greater than zero
    if (0 < $exitCode)
    {
      $output = implode($output, "<br />");

      throw new sfException($output);
    }
    else
    {
      // Warnings
      $this->errors = $output;
    }

    return $this;
  }

  /**
   * Return true if import had errors
   *
   * @return boolean
   */
  public function hasErrors()
  {
    return $this->errors != null;
  }

  /**
   * Return array of error messages
   *
   * @return unknown
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Get the root object for the import
   *
   * @return mixed the root object (object type depends on import type)
   */
  public function getRootObject()
  {
    return $this->rootObject;
  }

  /**
   * Get the root object for the import
   *
   * @return mixed the root object (object type depends on import type)
   */
  public function setParent($parent)
  {
    return $this->parent = $parent;
  }
}
