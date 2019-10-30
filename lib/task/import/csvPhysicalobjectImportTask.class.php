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
 * Import csv authoriy record data
 *
 * @package    AccessToMemory
 * @subpackage lib/task/csvImport
 * @author     David Juhasz <djuhasz@artefactual.com>
 */
class csvPhysicalobjectImportTask extends arBaseTask
{
  /**
   * @see sfBaseTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('filename', sfCommandArgument::REQUIRED,
        'The input file name (csv format).')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null,
        sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED,
        'The environment', 'cli'),
      new sfCommandOption('connection', null,
        sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),

      // Import options
      new sfCommandOption('culture', null,
        sfCommandOption::PARAMETER_REQUIRED,
        'ISO 639-1 Code for rows without an explicit culture',
        'en'),
      new sfCommandOption('error-log', null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Log errors to indicated file'),
      new sfCommandOption('header', null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Provide column names (CSV format) and import first row of file as data'),
      new sfCommandOption('index', null,
        sfCommandOption::PARAMETER_NONE,
        'Update search index during import'),
      new sfCommandOption('rows-until-update', null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Show import progress every [n] rows (n=0: errors only)',
        1),
      new sfCommandOption('skip-rows', null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Skip [n] rows before importing',
        0),
      new sfCommandOption('source-name', null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Source name to use when inserting keymap entries'),
      new sfCommandOption('update', null,
        sfCommandOption::PARAMETER_NONE,
        'Update existing record if name matches imported name.'
      ),
    ));

    $this->namespace = 'csv';
    $this->name = 'physicalobject-import';
    $this->briefDescription = 'Import physical object CSV data.';
    $this->detailedDescription = <<<EOF
      Import physical object CSV data
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    $importOptions = $this->setImportOptions($options);

    $importer = new PhysicalObjectCsvImporter(
      $this->context, $this->getDbConnection(), $importOptions);
    $importer->setFilename($arguments['filename']);

    // Set frequency of progress updates
    if (isset($options['rows-until-update']))
    {
      $importer->setProgressFrequency($options['rows-until-update']);
    }

    $this->log(sprintf('Importing physical object data from %s...'.PHP_EOL,
      $importer->getFilename()));

    if (isset($options['skip-rows']) && $options['skip-rows'] > 0)
    {
      if (1 == $options['skip-rows'])
      {
        $this->log('Skipping first row...');
      }
      else
      {
        $this->log(sprintf('Skipping first %u rows...', $options['skip-rows']));
      }
    }

    $importer->doImport();

    $this->log(sprintf(PHP_EOL.'Done! Imported %u of %u rows.',
      $importer->countRowsImported(), $importer->countRowsTotal()));
  }

  protected function getDbConnection()
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    return $databaseManager->getDatabase('propel')->getConnection();
  }

  protected function setImportOptions($options)
  {
    $opts = array();

    $keymap = [
      'culture'     => 'defaultCulture',
      'error-log'   => 'errorLog',
      'header'      => 'header',
      'index'       => 'updateSearchIndex',
      'skip-rows'   => 'offset',
      'source-name' => 'sourceName',
      'update'      => 'updateOnMatch'
    ];

    foreach ($keymap as $oldkey => $newkey)
    {
      if (empty($options[$oldkey]))
      {
        continue;
      }

      $opts[$newkey] = $options[$oldkey];
    }

    return $opts;
  }
}
