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
    $this->addArguments(array(
      new sfCommandArgument('input', sfCommandArgument::REQUIRED, 'The input file'),
      new sfCommandArgument('output', sfCommandArgument::REQUIRED, 'The output folder')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('slug', 'l', sfCommandOption::PARAMETER_OPTIONAL, 'Information object slug', null)
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
    $conn = $databaseManager->getDatabase('propel')->getConnection();


    $this->checkOutputFolder($arguments);
    $fh = fopen($arguments['input'], 'r');

    if (!$fh)
    {
      throw new sfException('Failed to open file: ' . $arguments['input']);
    }

    while (($line = fgets($fh)) !== false)
    {
      $filePath = trim($line);
      $this->generateDerivatives($filePath, $arguments['output']);
    }

    fclose($fh);
  }

  private function checkOutputFolder($args)
  {
    if (!is_writable($args['output']) && !mkdir($args['output'], 0755))
    {
      throw new sfException('Unable to write to or create folder: ' . $args['output']);
    }
  }

  private function generateDerivatives($filePath, $outputPath)
  {
    $content = file_get_contents($filePath);

    if (!$content)
    {
      $this->log("Couldn't read file '$path'");
      return;
    }

    $do = new QubitDigitalObject;
    $do->usageId = QubitTerm::MASTER_ID;
    $do->assets[] = new QubitAsset($filePath, $content);
  }
}
