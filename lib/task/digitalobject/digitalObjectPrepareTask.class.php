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

class digitalObjectPrepareTask extends sfBaseTask
{
  protected function configure()
  {
    error_reporting(E_ALL);

    $this->addArguments(array(
      new sfCommandArgument('input', sfCommandArgument::REQUIRED, 'The input file'),
      new sfCommandArgument('output', sfCommandArgument::OPTIONAL, 'The output folder', 'prepared_derivatives')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('slug', 'l', sfCommandOption::PARAMETER_OPTIONAL, 'Information object slug', null),
      new sfCommandOption('no-overwrites', null, sfCommandOption::PARAMETER_NONE, 'Do not generate/overwrite derivatives if already in prep folder')
    ));

    $this->namespace = 'digitalobject';
    $this->name = 'prepare';
    $this->briefDescription = 'Generates digital object derivatives and puts them into a specified folder';
    $this->detailedDescription = <<<EOF
This task will generate the derivatives for a list of digital objects.
The input argument will be a file with each line containing a path to
digital object files. The output argument will be the folder to put
all the generated derivatives.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $timer = new QubitTimer;

    sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->conn = $databaseManager->getDatabase('propel')->getConnection();


    $this->checkOutputFolder($arguments);
    $fh = fopen($arguments['input'], 'r');

    if (!$fh)
    {
      throw new sfException('Failed to open file: ' . $arguments['input']);
    }

    $filePaths = array();

    // Just build an array of the file paths first,
    // we need to do this to get the total digital object count!
    while (($line = fgets($fh)) !== false)
    {
      $line = trim($line);
      if (strlen($line))
      {
        $filePaths[] = $line;
      }
    }

    fclose($fh);

    $n = 0;
    foreach ($filePaths as $path)
    {
      $msg = sprintf('%s Generating derivatives for %s (%d of %d)', strftime('%r'), basename($path),
                     ++$n, count($filePaths));

      $this->logSection('digitalobject', $msg);

      $this->generateDerivatives($path, $arguments['output'], $options);
    }
  }

  private function checkOutputFolder($args)
  {
    if (!is_writable($args['output']) && !mkdir($args['output'], 0755, true))
    {
      throw new sfException('Unable to write to or create folder: ' . $args['output']);
    }
  }

  private function generateDerivatives($filePath, $outputPath, $options)
  {
    $content = file_get_contents($filePath);

    if (!$content)
    {
      $this->log("Couldn't read file '$path'");
      return;
    }

    $asset = new QubitAsset($filePath, $content);

    if ($options['no-overwrites'] && $this->derivativesAlreadyExist($outputPath, $filePath, $asset->getChecksum()))
    {
      $this->logSection('digitalobject', 'Derivatives already exist in preperation folder, skipping...');
      return;
    }

    $do = new QubitDigitalObject;
    $do->name = basename($filePath);
    $do->usageId = QubitTerm::MASTER_ID;
    $do->assets[] = $asset;
    $do->setMimeAndMediaType();

    $options = array(
      'preparedFilePath' => $filePath,
      'preparedFileDerivFolder' => $outputPath,
      'preparedFileChecksum' => $asset->getChecksum()
    );

    $do->createRepresentations($do->usageId, $this->conn, $options);
  }

  /**
   * If both derivatives for a given master digital object exist in our
   * prepare folder, return true. Otherwise false.
   */
  private function derivativesAlreadyExist($prepFolder, $masterFilePath, $masterChecksum)
  {
    $derivPath = rtrim($prepFolder, '/') . '/' . $masterChecksum[0] . '/' . $masterChecksum[1] . '/' .
                 $masterChecksum[2] . '/' . $masterChecksum;

    foreach (array(QubitTerm::THUMBNAIL_ID, QubitTerm::REFERENCE_ID) as $derivType)
    {
      $path = sprintf('%s/%s_%s.%s', $derivPath, $this->stripExtension($masterFilePath),
                      $derivType, QubitDigitalObject::THUMB_EXTENSION);

      if (!file_exists($path))
      {
        return false;
      }
    }

    return true;
  }

  private function stripExtension($filePath)
  {
    $parts = pathinfo($filePath);
    return $parts['filename'];
  }
}
