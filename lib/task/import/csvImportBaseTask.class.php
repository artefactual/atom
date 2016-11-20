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
abstract class csvImportBaseTask extends arBaseTask
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

    if ($options['limit'] && !$options['update'])
    {
      throw new sfException('The --limit option requires the --update option to be present.');
    }

    if ($options['keep-digital-objects'] && 'match-and-update' != trim($options['update']))
    {
      throw new sfException('The --keep-digital-objects option can only be used when --update=\'match-and-update\' option is present.');
    }

    $this->validateUpdateOptions($options);
  }

  /**
   * Validate --update option values, throw an exception if invalid value specified.
   *
   * @param array $options  CLI options passed in during import.
   */
  protected function validateUpdateOptions($options)
  {
    if (!$options['update'])
    {
      return;
    }

    $validParams = array('match-and-update', 'delete-and-replace');

    if (!in_array(trim($options['update']), $validParams))
    {
      $msg  = sprintf('Parameter "%s" is not valid for --update option. ', $options['update']);
      $msg .= sprintf('Valid options are: %s', implode(', ', $validParams));
      throw new sfException($msg);
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

  /**
   * Import events
   */
  static function importEvents(&$import)
  {
    $events = array();

    // Event columns grouped by version
    foreach (array(
      '2.1' => array(
        'actorName'    => 'creators',
        'actorHistory' => 'creatorHistories',
        'date'         => 'creatorDates',
        'startDate'    => 'creatorDatesStart',
        'endDate'      => 'creatorDatesEnd',
        'description'  => 'creatorDateNotes',
        'type'         => '-',
        'place'        => '-'),
      '2.2' => array(
        'actorName'    => 'creators',
        'actorHistory' => 'creatorHistories',
        'date'         => 'creationDates',
        'startDate'    => 'creationDatesStart',
        'endDate'      => 'creationDatesEnd',
        'description'  => 'creationDateNotes',
        'type'         => 'creationDatesType',
        'place'        => '-'),
      '2.3' => array(
        'actorName'    => 'eventActors',
        'actorHistory' => 'eventActorHistories',
        'date'         => 'eventDates',
        'startDate'    => 'eventStartDates',
        'endDate'      => 'eventEndDates',
        'description'  => 'eventDescriptions',
        'type'         => 'eventTypes',
        'place'        => 'eventPlaces')) as $version => $propertyColumns)
    {
      // Get event data if one of the columns is populated in the current index
      $index = 0;
      while (isset($import->rowStatusVars[$propertyColumns['actorName']][$index])
        || isset($import->rowStatusVars[$propertyColumns['actorHistory']][$index])
        || isset($import->rowStatusVars[$propertyColumns['date']][$index])
        || isset($import->rowStatusVars[$propertyColumns['startDate']][$index])
        || isset($import->rowStatusVars[$propertyColumns['endDate']][$index])
        || isset($import->rowStatusVars[$propertyColumns['description']][$index])
        || isset($import->rowStatusVars[$propertyColumns['type']][$index])
        || isset($import->rowStatusVars[$propertyColumns['place']][$index]))
      {
        // Two columns are used in 2.1 and 2.2: 'creators' and 'creatorHistories'.
        // To avoid adding duplicate events, if we are checking the 2.1 version
        // and only those columns are populated, the events are not created and
        // those columns will try to be related with the other 2.2 date columns.
        // This could create duplicates in CSV files mixing 2.1 and 2.2 date columns,
        // to avoid that, all values are removed after they are added to event data.
        if ($version == '2.1'
          && !isset($import->rowStatusVars[$propertyColumns['date']][$index])
          && !isset($import->rowStatusVars[$propertyColumns['startDate']][$index])
          && !isset($import->rowStatusVars[$propertyColumns['endDate']][$index])
          && !isset($import->rowStatusVars[$propertyColumns['description']][$index]))
        {
          $index++;

          continue;
        }

        $eventData = array();
        foreach ($propertyColumns as $property => $column)
        {
          // Ignore 'NULL' values
          if (isset($import->rowStatusVars[$column][$index])
           && $import->rowStatusVars[$column][$index] != 'NULL')
          {
            $eventData[$property] = $import->rowStatusVars[$column][$index];

            // Remove values to avoid duplicates in CSV files mixing version columns.
            // Use 'NULL' to let them be set (but ignored) for the next version cicle.
            $import->rowStatusVars[$column][$index] = 'NULL';
          }
        }

        if (count($eventData))
        {
          $events[] = $eventData;
        }

        $index++;
      }
    }

    // Create events
    foreach ($events as $eventData)
    {
      if (!isset($eventData['type']))
      {
        // Creation is the default event type
        $eventTypeId = QubitTerm::CREATION_ID;
      }
      else
      {
        // Get or add term if event type is set
        $typeTerm = $import->createOrFetchTerm(QubitTaxonomy::EVENT_TYPE_ID, $eventData['type'], $import->columnValue('culture'));
        $eventTypeId = $typeTerm->id;

        unset($eventData['type']);
      }

      // Add row culture to fetch place term in event creation/update
      $eventData['culture'] = $import->columnValue('culture');

      $import->createOrUpdateEvent($eventTypeId, $eventData);
    }
  }
}
