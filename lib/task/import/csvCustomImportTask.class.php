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
 * Import csv data using custom logic defined in an external file
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvCustomImportTask extends csvImportBaseTask
{
    protected $namespace        = 'csv';
    protected $name             = 'custom-import';
    protected $briefDescription = 'Import csv data using custom criteria';

    protected $detailedDescription = <<<EOF
Import CSV data using import logic defined in a file
EOF;

  /**
   * @see csvImportBaseTask
   */
  protected function configure()
  {
    parent::configure();

    $this->addOptions(array(
      new sfCommandOption('import-definition', null, sfCommandOption::PARAMETER_REQUIRED, 'PHP file defining and returning an import object.'),
      new sfCommandOption('output-file', null, sfCommandOption::PARAMETER_OPTIONAL, 'Optional output file parameter which can be referenced by import definition logic.'),
      new sfCommandOption('source-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'Source name to use when inserting keymap entries.'),
      new sfCommandOption('ignore-bad-lod', null, sfCommandOption::PARAMETER_NONE, 'Add rows with an unrecognized level of description to end of file, instead of dropping them.')
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    $this->validateOptions($options);

    $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

    $sourceName = ($options['source-name'])
      ? $options['source-name']
      : basename($arguments['filename']);

    if (false === $fh = fopen($arguments['filename'], 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    // Get import definition
    $import = require($options['import-definition']);

    $import->csv($fh, $skipRows);
  }
}
