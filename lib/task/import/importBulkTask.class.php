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

class importBulkTask extends arBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('folder', sfCommandArgument::REQUIRED, 'The import folder or file')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('index', null, sfCommandOption::PARAMETER_NONE, 'Set to enable indexing on imported objects'),
      new sfCommandOption('taxonomy', null, sfCommandOption::PARAMETER_OPTIONAL, 'Set the taxonomy id to insert the SKOS concepts into'),
      new sfCommandOption('completed-dir', null, sfCommandOption::PARAMETER_OPTIONAL, 'Directory to move completed files into'),
      new sfCommandOption('schema', null, sfCommandOption::PARAMETER_OPTIONAL, 'Schema to use if importing a CSV file'),
      new sfCommandOption('output', null, sfCommandOption::PARAMETER_OPTIONAL, 'Filename to output results in CSV format'),
      new sfCommandOption('verbose', '-v', sfCommandOption::PARAMETER_NONE, 'Verbose output'),
      new sfCommandOption(
        'update',
        null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Attempt to update if description has already been imported. Valid option values are "match-and-update" & "delete-and-replace".'
      ),
      new sfCommandOption(
        'skip-matched',
        null,
        sfCommandOption::PARAMETER_NONE,
        'When importing records without --update, use this option to skip creating new records when an existing one matches.'
      ),
      new sfCommandOption(
        'skip-unmatched',
        null,
        sfCommandOption::PARAMETER_NONE,
        "When importing records with --update, skip creating new records if no existing records match."
      ),
      new sfCommandOption(
        'limit',
        null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Limit --update matching to under a specified top level description or repository via slug.'
      ),
    ));

    $this->namespace        = 'import';
    $this->name             = 'bulk';
    $this->briefDescription = 'Bulk import multiple XML/CSV files at once';
    $this->detailedDescription = <<<EOF
Bulk import multiple XML/CSV files at once
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    // Overall timing
    $timer = new QubitTimer;

    if (empty($arguments['folder']) || !file_exists($arguments['folder']))
    {
      throw new sfException('You must specify a valid import folder or file');
    }

    // Set indexing preference
    if (!$options['index'])
    {
      QubitSearch::disable();
    }

    if (is_dir($arguments['folder']))
    {
      // Recurse into the import folder
      $files = $this->dir_tree(rtrim($arguments['folder'], '/'));
    }
    else
    {
      $files = array($arguments['folder']);
    }

    // TODO: Add some colour
    $this->log("Importing ".count($files)." files from ".$arguments['folder']." (indexing is ".($options['index'] ? "ENABLED" : "DISABLED").") ...\n");

    $count = 0;
    $total = count($files);

    foreach ($files as $file)
    {
      $start = microtime(true);
      $importer = null;

      if ($options['verbose'])
      {
        print 'Importing: '. $file ."\n";
      }

      // Choose import type based on file extension, eg. csv, xml
      if ('csv' == pathinfo($file, PATHINFO_EXTENSION))
      {
        $importer = new QubitCsvImport;
        $importer->import($file, $options);
      }
      elseif ('xml' == pathinfo($file, PATHINFO_EXTENSION))
      {
        $importer = new QubitXmlImport;
        $options['strictXmlParsing'] = false;

        $importer->import($file, $options);
      }
      else
      {
        // Move on to the next file
        continue;
      }

      if (isset($options['completed-dir']) && !empty($importer))
      {
        $path_info = pathinfo($file);
        $move_source = $path_info['dirname'] .'/'. $path_info['basename'];
        $move_destination = $options['completed-dir'] .'/'. $path_info['basename'];
        rename($file, $move_destination);
      }

      if (!$options['verbose'])
      {
        print '.';
      }

      if ($importer->hasErrors())
      {
        foreach ($importer->getErrors() as $message)
        {
          $this->log('('. $file .'): '. $message);
        }
      }

      print '.';

      // Try to free up memory
      unset($importer);

      $count++;
      $split = round(microtime(true) - $start, 2);

      // Store details if output is specified
      if ($options['output'])
      {
        $rows[] = array($file, $split . 's', memory_get_usage() . 'B');
      }

      if ($options['verbose'])
      {
        $this->log(basename($file)." imported (".round($split, 2)." s) (".$count."/".$total.")");
      }
    }

    // Create/open output file if specified
    if ($options['output'])
    {
      $fh = fopen($options['output'], 'w+');

      fputcsv($fh, array('File', 'Time elapsed (secs)', 'Memory used'));
      foreach ($rows as $row)
      {
        fputcsv($fh, $row);
      }

      fputcsv($fh, array()); // Blank row to separate our summary info
      fputcsv($fh, array('Total time elapsed:', $timer->elapsed() . 's'));
      fputcsv($fh, array('Peak memory usage:', round(memory_get_peak_usage() / 1048576, 2) . 'MB'));

      fclose($fh);
    }

    // Optimize index if enabled
    if (!$options['noindex'])
    {
      QubitSearch::getInstance()->optimize();
    }

    $this->log("\nImported ".$count." XML/CSV files in ".$timer->elapsed()." s. ".memory_get_peak_usage()." bytes used.");
  }

  protected function dir_tree($dir)
  {
    $path = '';
    $stack[] = $dir;

    while ($stack)
    {
      $thisdir = array_pop($stack);

      if ($dircont = scandir($thisdir))
      {
        $i = 0;

        while (isset($dircont[$i]))
        {
          if ($dircont[$i] !== '.' && $dircont[$i] !== '..' && !preg_match('/^\..*/', $dircont[$i]))
          {
            $current_file = "{$thisdir}/{$dircont[$i]}";

            if (is_file($current_file))
            {
              $path[] = "{$thisdir}/{$dircont[$i]}";
            }
            elseif (is_dir($current_file))
            {
              $stack[] = $current_file;
            }
          }

          $i++;
        }
      }
    }

    return $path;
  }
}
