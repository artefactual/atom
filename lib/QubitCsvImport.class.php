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
 * Import a CSV file into Qubit.
 * This class is a wrapper to interact with the import CSV tasks located
 * under the lib/task/import directory
 *
 * @package    AccesstoMemory
 * @subpackage library
 */
class QubitCsvImport
{
  protected
    $errors = null,
    $rootObject = null,
    $parent = null;

  public $indexDuringImport = false;
  public $doCsvTransform = false;

  public function import($csvFile, $type = null)
  {
    // perform the transformation, if requested and correctly configured
    if ($this->doCsvTransform)
    {
      $transformedFile = $this->doTransform($csvFile);
    }

    // Find the proper task
    switch ($type)
    {
      case 'accession':
        $taskClassName = 'csv:accession-import';

        break;

      case 'authorityRecord':
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

    // Figure out whether indexing flag should be added to command
    $commandIndexFlag = ($taskClassName != 'csv:event-import' && $this->indexDuringImport) ? '--index' : '';

    // Build command string
    if (isset($this->parent))
    {
      // Example: php symfony csv:import --default-parent-slug="$sourceName" /tmp/foobar
      $command = sprintf('php %s %s %s --quiet --source-name=%s --default-parent-slug=%s %s',
        escapeshellarg(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'symfony'),
        escapeshellarg($taskClassName),
        $commandIndexFlag,
        escapeshellarg($csvFile),  // --source-name should be original pre-transform file name. tmp file name: /tmp/phpLjBIBv ??
        escapeshellarg($this->parent->slug),
        escapeshellarg($transformedFile ? $transformedFile : $csvFile));
    }
    else
    {
      // Example: php symfony csv:import /tmp/foobar
      $command = sprintf('php %s %s %s --quiet --source-name=%s %s',
        escapeshellarg(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'symfony'),
        escapeshellarg($taskClassName),
        $commandIndexFlag,
        escapeshellarg($csvFile),   // --source-name should be original pre-transform file name. tmp file name: /tmp/phpLjBIBv ??
        escapeshellarg($transformedFile ? $transformedFile : $csvFile));
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
   * Trigger a csv transform on the server using a configured transformation
   * script.
   *
   * @package    AccesstoMemory
   * @subpackage library
   */
  public function doTransform($csvFile)
  {
    // ensure csv_transform_script_name is configured.
    if (!sfConfig::get('app_csv_transform_script_name'))
    {
      throw new sfException('Transform failed. Script not found. Please correct AtoM configuration (csv_transform_script_name)');
    }
    // ensure we can find the uploaded source csv file.
    if (!file_exists($csvFile))
    {
      throw new sfException('Transform failed. Unable to locate file: ' . $csvFile);
    }

    // build output filename and path. Take source dir and name and create a
    // parallel temp file based on that.
    $csvFilePath = pathinfo($csvFile);
    $outputFileName = tempnam($csvFilePath['dirname'], $csvFilePath['filename'] . '-');
    $logFileName = $outputFileName . '.log';

    // Example: ./transform_csv.py /tmp/original_file.csv /tmp/transformed_file.csv
    $command = sprintf(sfConfig::get('app_csv_transform_script_name') . ' %s %s',
      escapeshellarg($csvFile),
      escapeshellarg($outputFileName)) ;

    // redirect stderr to stdout to logfile
    $command .= ' 2>&1 > ' . $logFileName;

    exec($command, $output, $exitCode);

    if (0 < $exitCode)
    {
      if (!file_exists($logFileName))
      {
        // can't find output file
        throw new sfException('Transform failed: ' . $exitCode . '; Outputfile not found: ' . $logFileName);
      }
      // log file contains details about the errors.
      $outputLines = file($logFileName, FILE_SKIP_EMPTY_LINES);

      throw new sfException('Transform failed: ' . $exitCode . '; ' .htmlspecialchars(implode('; ' , $outputLines)));
    }

    if (!file_exists($outputFileName))
    {
      throw new sfException('Transform failed: Unable to find transformed file: ' . $outputFileName);
    }

    return $outputFileName;
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
