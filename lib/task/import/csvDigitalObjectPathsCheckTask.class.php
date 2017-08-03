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
 * Check digital object paths in CSV data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvDigitalObjectPathsCheckTask extends arBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('path-to-images', sfCommandArgument::REQUIRED, 'Path to directory containing images.'),
      new sfCommandArgument('path-to-csv-file', sfCommandArgument::REQUIRED, 'Path to CSV file.')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('csv-column-name', 'n', sfCommandOption::PARAMETER_OPTIONAL, 'CSV column name containing digital object paths', null)
    ));

    $this->namespace = 'csv';
    $this->name = 'digital-object-path-check';
    $this->briefDescription = 'Check digital object paths in CSV data.';

    $this->detailedDescription = <<<EOF
Compare digital object-related files in a directory to data in a CSV file's
column (digitalObjectPath by default) and display a report.

Determines which files aren't referenced in the CSV data, which are referenced
in CSV data but missing, and which files are references more than once.
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    if (!is_dir($arguments['path-to-images']))
    {
      throw new sfException("Images directory doesn't exist.");
    }

    if (!file_exists($arguments['path-to-csv-file']))
    {
      throw new sfException("CSV file doesn't exist.");
    }

    $csvFilePathColumnName = ($options['csv-column-name']) ? $options['csv-column-name'] : 'digitalObjectPath';

    $this->logSection('digital-object-path-check', 'Checking '. $csvFilePathColumnName .' column.');

    $this->printImageUsageInfo($arguments['path-to-images'], $arguments['path-to-csv-file'], $csvFilePathColumnName);
  }

  private function printImageUsageInfo($pathToImages, $csvFilePath, $csvFilePathColumnName)
  {
    $imageFiles = $this->getImageFiles($pathToImages);

    $columnValues = $this->getCsvColumnValues($csvFilePath, $csvFilePathColumnName);
    $imageUses = $this->summarizeImageUsage($columnValues);

    $this->printImageUses($imageUses);
    $this->printUnusedFiles($imageFiles, $imageUses);
    $this->printMissingFiles($imageUses, $pathToImages);
  }

  private function getImageFiles($pathToImages)
  {
    $imageFiles = array();

    $pathToImages = realpath($pathToImages);
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathToImages));

    foreach ($objects as $filePath => $object)
    {
      if (!is_dir($filePath))
      {
        // Remove absolute path leading to image directory
        $relativeFilePath = substr($filePath, strlen($pathToImages) + 1, strlen($filePath));
        array_push($imageFiles, $relativeFilePath);
      }
    }

    return $imageFiles;
  }

  private function getCsvColumnValues($filepath, $columnName)
  {
    $values = array();

    $fh = fopen($filepath, 'r');

    // Determine column index number using specified name
    $header = fgetcsv($fh, 60000);
    if (false === $imageColumnIndex = array_search($columnName, $header))
    {
      throw new sfException('Column name not found in header.');
    }

    while ($row = fgetcsv($fh, 60000))
    {
      array_push($values, $row[$imageColumnIndex]);
    }

    return $values;
  }

  private function summarizeImageUsage($columnValues)
  {
    $imageUses = array();

    foreach ($columnValues as $columnValue)
    {
      $imageUses[$columnValue] = (!isset($imageUses[$columnValue])) ? 1 : $imageUses[$columnValue] + 1;
    }

    return $imageUses;
  }

  private function printImageUses($imageUses)
  {
    $usedMoreThanOnce = array();

    foreach ($imageUses as $image => $uses)
    {
      if ($uses > 1)
      {
        array_push($usedMoreThanOnce, $image);
      }
    }

    $this->printListOfItemsIfNotEmpty($usedMoreThanOnce, 'Used more than once in CSV:');
  }

  private function printUnusedFiles($imageFiles, $imageUses)
  {
    $unusedFiles = array();

    foreach($imageFiles as $imageFile)
    {
      if (!isset($imageUses[$imageFile]))
      {
        array_push($unusedFiles, $imageFile);
      }
    }

    $this->printListOfItemsIfNotEmpty($unusedFiles, 'Unused files:');
  }

  private function printMissingFiles($imageUses, $pathToImages)
  {
    $missingFiles = array();

    foreach ($imageUses as $image => $uses)
    {
      if (!file_exists($pathToImages .'/'. $image))
      {
        array_push($missingFiles, $image);
      }
    }

    $this->printListOfItemsIfNotEmpty($missingFiles, 'Files referenced in CSV that are missing:');
  }

  private function printListOfItemsIfNotEmpty($list, $listHeader)
  {
    if (count($list))
    {
      print $listHeader ."\n";

      foreach($list as $item)
      {
        print '* '. $item ."\n";
      }

      print "\n";
    }
  }
}
