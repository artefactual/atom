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
 * Import flatfile data
 *
 * @package    symfony
 * @subpackage library
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class QubitFlatfileImport
{
  public $context;                   // optional sfContext
  public $className;                 // optional class name of object to create/save
  public $errorLog;                  // optional location of error log file
  public $displayProgress = true;    // display progress by default
  public $rowsUntilProgressDisplay;  // optional display progress every n rows

  public $searchIndexingDisabled = true;  // disable per-object search indexing by default
  public $updateExisting         = false; // if object already imported, attempt update

  public $status          = array(); // place to store data related to overall import
  public $rowStatusVars   = array(); // place to store data related to current row

  public $columnNames     = array(); // column names from first row of imported CSV
  public $ignoreColumns   = array(); // columns in CSV to ignore
  public $renameColumns   = array(); // CSV header column substitutions
  public $addColumns      = array(); // columns to add to internal row buffer

  public $standardColumns = array(); // columns in CSV are object properties
  public $columnMap       = array(); // columns in CSV that map to object properties
  public $propertyMap     = array(); // columns in CSV that map to Qubit properties
  public $noteMap         = array(); // columns in CSV that should become notes
  public $handlers        = array(); // columns in CSV paired with custom handling logic
  public $variableColumns = array(); // columns in CSV to be later referenced by logic
  public $arrayColumns    = array(); // columns in CSV to explode and later reference

  public $updatePreparationLogic;  // Optional pre-update logic (remove related data, etc.)
  public $rowInitLogic;            // Optional logic to create/load object if not using $className
  public $preSaveLogic;            // Optional pre-save logic
  public $saveLogic;               // Optional logic to save object if not using $className
  public $postSaveLogic;           // Optional post-save logic
  public $completeLogic;           // Optional cleanup, etc. logic for after import

  // Replaceable logic to filter content before entering Qubit
  public $contentFilterLogic;

  public function __construct($options = array())
  {
    // Replaceable logic to filter content before entering Qubit
    $this->contentLogic = function($text)
    {
      return $text;
    };

    $this->setPropertiesFromArray($this, $options, true);

    // initialize bookkeeping of rows processed
    $this->status['rows']       = 0;
    $this->status['duplicates'] = 0;
    $this->status['updated']    = 0;
  }


  /*
   *
   *  General helper methods
   *  ----------------------
   */

  /**
   * Use an array of properties and their respective values to set an object's
   * properties (restricting to a set of allowed properties and allowing the
   * specification of properties that should be ignored and not set)
   *
   * @param object &$object  object to act upon
   * @param array $propertyArray  array of properties and their respective values
   * @param array $allowedProperties  array of properties that can be set or true if any allowed
   * @param array $ignore  array of properties that should be ignored
   *
   * @return void
   */
  public function setPropertiesFromArray(&$object, $propertyArray, $allowedProperties, $ignore = array())
  {
    // set properties from options, halting upon invalid option
    foreach ($propertyArray as $option => $value)
    {
      if (!in_array($option, $ignore))
      {
        // if allowing all properties, inspect object to see if property is legitimate
        // otherwise use array of allowed properties
        $settingAllowed = (
          ($allowedProperties === true && property_exists(get_class($object), $option))
          || (is_array($allowedProperties) && in_array($option, $allowedProperties))
        );
        if ($settingAllowed)
        {
          $object->$option = $value;
        }
        else
        {
          throw new Exception('Option "'. $option .'" not allowed.');
        }
      }
    }
  }

  /*
   * Utility function to filter data, with a function that can be optionally
   * overridden, before it enters Qubit
   *
   * This function will be automatically applied to data handled by the
   * standardColumns, columnMap, propertyMap, and noteMap handlers
   *
   * This function will not be applied to data handled by variableColumns
   * or arrayColumns or other handlers allowing the user to do ad-hoc things
   *
   * @param string $text  Text to process
   */
  public function content($text)
  {
    if ($this->contentFilterLogic)
    {
      return call_user_func_array($this->contentFilterLogic, array($text));
    }
    else
    {
      return $text;
    }
  }

  /**
   * Attempt to parse date from non-machine-readable text,
   * returning false upon failure and logging failures.
   *
   * @param string $dateText  description of date
   *
   * @return string  date in YYYY-MM-DD format
   */
  public function parseDateLoggingErrors($dateText)
  {
    $date = $this->parseDate($dateText);
    if ($date)
    {
      return $date;
    }
    else
    {
      $this->logError('Could not parse date: '. $dateText);
      return false;
    }
  }

  /**
   * Attempt to parse date from non-machine-readable text,
   * returning false upon failure.
   *
   * @param string $dateText  description of date
   *
   * @return string  date in YYYY-MM-DD format
   */
  public static function parseDate($dateText)
  {
    $dateText = trim($dateText);
    if (is_numeric($dateText))
    {
      // return year as beginning date of year
      $dateData = array(
        'year'  => $dateText,
        'month' => '01',
        'day'   => '01'
      );
    }
    else
    {
      $dateData = date_parse($dateText);
    }

    if (!$dateData['year'] || !$dateData['month'] || !$dateData['day'])
    {
      return false;
    }
    else
    {
      // turn back to string
      $dateString = $dateData['year'] .'-'
        . $dateData['month'] .'-'
        . $dateData['day'];

      $date = new DateTime($dateString);

      // format to pad with leading 0s
      return $date->format('Y-m-d');
    }
  }

  /**
   * Set status variable value
   *
   * @param string $var  name of variable
   * @param value  value of variable (could be any type)
   *
   * @return void
   */
  public function setStatus($var, $value)
  {
    $this->status[$var] = $value;
  }

  /**
   * Determine whether or not a column exists
   *
   * @param string $column  name of column
   *
   * @return boolean
   */
  function columnExists($column)
  {
    $columnIndex = array_search($column, $this->columnNames);

    return is_numeric($columnIndex);
  }

  /**
   * Get/set values in internal representation of current row
   */
  function columnValue($column, $value =  false)
  {
    $columnIndex = array_search($column, $this->columnNames);

    if (is_numeric($columnIndex))
    {
      if ($value === false)
      {
        return $this->status['row'][$columnIndex];
      }
      else
      {
        $this->status['row'][$columnIndex] = $value;
      }
    }
    else
    {
      throw new sfException('Missing column "'. $column .'".');
    }
  }

  /**
   * Get status variable value
   *
   * @param string $var  name of variable
   *
   * @return value  value of variable (could be any type)
   */
  public function getStatus($var)
  {
    return $this->status[$var];
  }

  /**
   * Test whether a property is set and, if so, execute it
   *
   * @param string $property  name of property
   *
   * @return void
   */
  public function executeClosurePropertyIfSet($property)
  {
    // attempting to directly call an object property that's a
    // closure results in "Fatal error: Call to undefined method"
    if ($this->$property)
    {
      call_user_func_array($this->$property, array(&$this));
    }
  }

  /**
   * Start import timer
   *
   * @return void
   */
  protected function startTimer()
  {
    $this->timer = new QubitTimer;
    $this->timer->start();
  }

  /**
   * Stop import timer
   *
   * @return void
   */
  protected function stopTimer()
  {
    $this->timer->stop();
  }

  /**
   * Get time elapsed during import
   *
   * @return int  microseconds since import began
   */
  public function getTimeElapsed()
  {
    return $this->timer->elapsed();
  }

  /**
   * Log error message if an error log has been defined
   *
   * @param string $message  error message
   * @param boolean $includeCurrentRowNumber  prefix error message with row number
   *
   * @return string  message prefixed with current row number
   */
  public function logError($message, $includeCurrentRowNumber = true)
  {
    $message = ($includeCurrentRowNumber) ? sprintf("Row %d: %s\n", $this->getStatus('rows') + 1, $message) : $message;

    if ($this->errorLog)
    {
      file_put_contents($this->errorLog, $message, FILE_APPEND);
    }

    return $message;
  }

  /**
   * Append content to existing content, prepending a line break to new content
   * if necessary
   *
   * @param string $oldContent  existing content
   * @param string $newContent  new content to add to existing content
   *
   * @return string  both strings appended
   */
  public function appendWithLineBreakIfNeeded($oldContent, $newContent)
  {
    return ($oldContent) ? $oldContent ."\n". $newContent : $newContent;
  }

  /**
   * Combine column text, using optional pre-column prefixes
   *
   * @param array $prefixesAndColumns  array, optional keys specifying prefix
   * @param string $destinationColumn  optional destination column for result
   *
   * @return string  combined column text
   */
  public function amalgamateColumns($prefixesAndColumns, $destinationColumn = false)
  {
    $output = '';

    foreach ($prefixesAndColumns as $prefix => $column)
    {
      $columnValue = $this->columnValue($column);

      if ($columnValue)
      {
        // numeric keys are considered prefixes
        $prepend = (!is_numeric($prefix)) ? $prefix : '';

        $output = $this->appendWithLineBreakIfNeeded(
          $output,
          $prepend . $columnValue
        );
      }
    }

    // optional direct setting of column
    if ($destinationColumn)
    {
      $this->columnValue($destinationColumn, $output);
    }

    return $output;
  }

  /**
   * Convert human readable (e.g. 'This string') strings to camelCase
   * representation (e.g. 'thisString')
   *
   * @param string $str  input string
   *
   * @return string  camelCase string
   */
  public static function camelize($str)
  {
    $str = str_replace(' ', '_', $str);
    $str = sfInflector::camelize($str);
    $str = lcfirst($str);

    return $str;
  }

  /**
   * Combine two or more arrays, eliminating any duplicates
   *
   * @return array  combined array
   */
  protected function combineArraysWithoutDuplicates()
  {
    $args = func_get_args();
    $combined = array();

    // go through each array providesd
    for($index = 0; $index < count($args); $index++)
    {
      // for each element of array, add to combined array if element isn't a dupe
      foreach ($args[$index] as $element)
      {
        if (!in_array($element, $combined)) array_push($combined, $element);
      }
    }

    return $combined;
  }


  /*
   *
   *  Row processing methods
   *  ----------------------
   */

  protected function handleColumnRenaming()
  {
    if (isset($this->renameColumns))
    {
      foreach ($this->renameColumns as $sourceColumn => $newName)
      {
        if (is_numeric($position = array_search($sourceColumn, $this->columnNames)))
        {
          $this->columnNames[$position] = $newName;
        }
      }
    }
  }

  /**
   * Pull data from a csv file and process each row
   *
   * @param resource $fh  file handler for file containing CSV data
   * @param integer $skipRows  number of rows to skip (optional)
   *
   * @return void
   */
  public function csv($fh, $skipRows = 0)
  {
    $this->status['skippedRows'] = $skipRows;
    $this->columnNames = fgetcsv($fh, 60000);

    if ($this->columnNames === false)
    {
      throw new sfException('Could not read initial row. File could be empty.');
    }

    $this->handleColumnRenaming();

    // add virtual columns (for column amalgamation, etc.)
    foreach ($this->addColumns as $column)
    {
      $this->columnNames[] = $column;
    }

    // warn if column names contain whitespace
    foreach ($this->columnNames as $column)
    {
      if ($column != trim($column))
      {
        print $this->logError(
          sprintf("WARNING: Column '%s' has whitespace before or after its name.", $column)
        );
      }
    }

    // disabling search indexing improves import speed
    $this->searchIndexingDisabled ? QubitSearch::disable() : QubitSearch::enable();

    if ($skipRows) print "Skipped ". $skipRows ." rows...\n";

    $timerStarted = false;

    // import each row
    while ($item = fgetcsv($fh, 60000))
    {
      if ($this->status['rows'] >= $skipRows)
      {
        if (!$timerStarted)
        {
          $this->startTimer();
          $timerStarted = true;
        }

        $this->row($item);

        $this->status['rows']++;

        if ($this->displayProgress)
        {
          print $this->renderProgressDescription();
        }
      }
      else
      {
        $this->status['rows']++;
      }
    }

    if ($timerStarted)
    {
      $this->stopTimer();
    }

    if ($this->status['duplicates'])
    {
      $msg = sprintf('Duplicates found: %d', $this->status['duplicates']);
      print $this->logError($msg, false);
    }

    if ($this->status['updated'])
    {
      $msg = sprintf('Updated: %d', $this->status['updated']);
      print $this->logError($msg, false);
    }

    // add ability to define cleanup, etc. logic
    $this->executeClosurePropertyIfSet('completeLogic');
  }

  /**
   * Process a row of imported data
   *
   * @param array $row  array of column data
   *
   * @return void
   */
  public function row($row = array())
  {
    // stash raw row data so it's accessible to closure logic
    $this->status['row'] = $row;

    // make row data match columns (in case virtual columns have been added)
    foreach (array_keys($this->columnNames) as $key)
    {
      if (!isset($this->status['row'][$key]))
      {
        $this->status['row'][$key] = '';
      }
    }

    // add blank culture field if not present in import
    if (!in_array('culture', $this->columnNames))
    {
      $this->columnNames[] = 'culture';
      $this->addColumns[]  = 'culture';
    }

    // default culture to English
    if (0 == strlen($this->columnValue('culture')))
    {
      $this->columnValue('culture', 'en');
    }

    // set row status variables that are based on column values
    $this->rowProcessingBeforeObjectCreation($row);

    if (isset($this->className))
    {
      $tableName = sfInflector::underscore(substr($this->className, 5));
      $legacyId = $this->columnExists('legacyId') ? trim($this->columnValue('legacyId')) : null;

      if ($this->className == 'QubitRepository' && $this->status['options']['merge-existing'] == 1)
      {
        $this->object = $this->createOrFetchRepository($this->columnValue('authorizedFormOfName'));
      }
      else if ($legacyId && $mapEntry = $this->fetchKeymapEntryBySourceAndTargetName($legacyId,
               $this->status['sourceName'], $tableName))
      {
        $this->object = QubitInformationObject::getById($mapEntry->target_id);

        // Remove keymap entry if it doesn't point to a valid QubitInformationObject.
        if ($this->object === null)
        {
          $query = '
            DELETE FROM keymap WHERE source_id = ? AND target_id = ?
            AND source_name = ? AND target_name = ?
          ';

          $statement = self::sqlQuery(
            $query, array(
              $legacyId,
              $mapEntry->target_id,
              $this->status['sourceName'],
              $tableName
            )
          );

          // create new object
          $this->object = new $this->className;
        }
        else if ($this->object->sourceCulture == $this->columnValue('culture'))
        {
          $actionDescription = ($this->updateExisting) ? 'updating' : 'skipping';

          if ($this->updateExisting)
          {
            $this->status['updated']++;

            // execute ad-hoc row pre-update logic (remove related data, etc.)
            $this->executeClosurePropertyIfSet('updatePreparationLogic');
          }
          else
          {
            $this->status['duplicates']++;
            $skipRowProcessing = true;
          }

          $msg = sprintf('Duplicate legacyId in database found, %s row (id: %s, culture: %s, legacyId: %s)...',
                         $actionDescription, $this->object->id, $this->object->sourceCulture, $legacyId);

          print $this->logError($msg);
        }
      }
      else
      {
        $this->object = new $this->className;
      }

      // Set current culture
      if (isset($this->context) && 'sfContext' == get_class($this->context))
      {
        $this->context->getUser()->setCulture($this->columnValue('culture'));
      }

      // determine whether nested set should be updated, if applicable to object type
      if (property_exists(get_class($this->object), 'disableNestedSetUpdating'))
      {
        // enable nested set updating if search indexing is enabled
        $this->object->disableNestedSetUpdating = ($this->searchIndexingDisabled) ? true : false;
      }
    }
    else
    {
      // execute ad-hoc row initialization logic (which can make objects, load
      // them, etc.)
      $this->executeClosurePropertyIfSet('rowInitLogic');
    }

    if (!$skipRowProcessing)
    {
      // set fields in information object and execute custom column handlers
      $this->rowProcessingBeforeSave($row);

      // execute pre-save ad-hoc import logic
      $this->executeClosurePropertyIfSet('preSaveLogic');

      if (isset($this->className))
      {
        $this->object->save();
      }
      else
      {
        // execute row completion logic
        $this->executeClosurePropertyIfSet('saveLogic');
      }

      // execute post-save ad-hoc import logic
      $this->executeClosurePropertyIfSet('postSaveLogic');

      // process import columns that produce child data (properties and notes)
      $this->rowProcessingAfterSave($row);
    }

    // reset row-specific status variables
    $this->rowStatusVars = array();
  }

  /**
   * Log error message if an error log has been defined
   *
   * @param string $message  error message
   *
   * @return void
   */
  protected function rowProcessingBeforeObjectCreation($row)
  {
    // process import columns that don't produce child data
    $this->forEachRowColumn($row, function(&$self, $index, $columnName, $value)
    {
      if (
        isset($self->columnNames[$index])
        && in_array($self->columnNames[$index], $self->variableColumns)
      )
      {
        $self->rowStatusVars[$self->columnNames[$index]] = $value;
      }
      else if (
        isset($self->columnNames[$index])
        && isset($self->arrayColumns[($self->columnNames[$index])])
      )
      {
        $self->arrayColumnHandler($columnName, $self->arrayColumns[$columnName], $value);
      }
    });
  }

  /**
   * Perform row processing for before an object is saved such as setting
   * object properties and executing ad-hoc column handlers
   *
   * @param array $row  array of column data
   *
   * @return void
   */
  protected function rowProcessingBeforeSave($row)
  {
    // process import columns that don't produce child data
    $this->forEachRowColumn($row, function(&$self, $index, $columnName, $value)
    {
      // Trim whitespace
      $value = trim($value);

      // if column maps to an attribute, set the attribute
      if (isset($self->columnMap) && isset($self->columnMap[$columnName]))
      {
        $self->mappedColumnHandler($self->columnMap[$columnName], $value);
      }
      // if column maps to a property, set the property
      else if (
        isset($self->propertyMap)
        && isset($self->propertyMap[$columnName])
        && $value
      )
      {
        $self->object->addProperty(
          $self->propertyMap[$columnName],
          $self->content($value)
        );
      }
      else if (
        isset($self->columnNames[$index])
        && isset($self->handlers[($self->columnNames[$index])])
      )
      {
        // otherwise, if column is data and a handler for it is set, use it
        call_user_func_array(
          $self->handlers[$columnName],
          array($self, $value)
        );
      }
      else if (
        isset($self->columnNames[$index])
        && in_array($self->columnNames[$index], $self->standardColumns)
        && $value
      )
      {
        // otherwise, if column is data and it's a standard column, use it
        $self->object->{$self->columnNames[$index]} = $self->content($value);
      }
    });
  }

  /**
   * Perform row processing for after an object is saved and has an ID such
   * as creating child properties and notes
   *
   * @param array $row  array of column data
   *
   * @return void
   */
  protected function rowProcessingAfterSave($row)
  {
    $this->forEachRowColumn($row, function(&$self, $index, $columnName, $value)
    {
      if (isset($self->noteMap) && isset($self->noteMap[$columnName]) && $value)
      {
        // otherwise, if maps to a note, create it
        $transformationLogic = (isset($self->noteMap[$columnName]['transformationLogic']))
          ? $self->noteMap[$columnName]['transformationLogic']
          : false;
        $self->createOrUpdateNotes(
          $self->noteMap[$columnName]['typeId'],
          explode('|', $value),
          $transformationLogic
        );
      }
    });
  }

  /**
   * Execute logic, defined by a closure, on each column of a row
   *
   * @param array $row  array of column data
   *
   * @param closure $logic  logic that should be performed on the column value
   *
   * @return void
   */
  protected function forEachRowColumn($row, $logic)
  {
    for ($index = 0; $index < count($row); $index++)
    {
      // determine what type of data should be in this column
      $columnName = $this->columnNames[$index];

      // stash current column name so handlers can refer to it if need be
      $this->status['currentColumn'] = $columnName;

      // execute row logic
      $logic($this, $index, $columnName, $row[$index]);
    }
  }

  /**
   * Output import progress, time elapsed, and memory usage
   *
   * @return string  description of import progress
   */
  public function renderProgressDescription()
  {
    $output = '.';

    // return empty string if no intermittant progress display
    if (!isset($this->rowsUntilProgressDisplay)
      || !$this->rowsUntilProgressDisplay
    ) return $output;

    // row count isn't incremented until after this is displayed, so add one to reflect reality
    $rowsProcessed = $this->getStatus('rows') - $this->getStatus('skippedRows');
    $memoryUsageMB = round(memory_get_usage() / (1024 * 1024), 2);

    // if this show should be displayed, display it
    if (!($rowsProcessed % $this->rowsUntilProgressDisplay))
    {
      $elapsed = $this->getTimeElapsed();
      $elapsedMinutes = round($elapsed / 60, 2);
      $averageTime = round($elapsed / $rowsProcessed, 2);

      $output .= "\n". $rowsProcessed ." rows processed in ". $elapsedMinutes
      . " minutes (". $averageTime ." second/row average, ". $memoryUsageMB ." MB used).\n";
    }

    return $output;
  }


  /*
   *
   *  Column handlers
   *  ---------------
   */

  /**
   * Add an ad-hoc column handler
   *
   * @param string $column  name of column
   * @param closure $handler  column handling logic
   *
   * @return void
   */
  public function addColumnHandler($column, $handler)
  {
    $this->handlers[$column] = $handler;
  }

  /**
   * Add an ad-hoc column handler to multiple columns
   *
   * @param array $columns  names of columns
   * @param closure $handler  column handling logic
   *
   * @return void
   */
  public function addColumnHandlers($columns, $handler)
  {
    foreach ($columns as $column)
    {
      $this->addColumnHandler($column, $handler);
    }
  }

  /**
   * Handle mapping of column to object property
   *
   * @param array $mapDefinition  array defining which property to map to and
   *                              optional transformation logic
   * @param string $value  column value
   *
   * @return void
   */
  public function mappedColumnHandler($mapDefinition, $value)
  {
    if (isset($this->object) && is_object($this->object))
    {
      if (is_array($mapDefinition))
      {
        // tranform value is logic provided to do so
        if (is_callable($mapDefinition['transformationLogic']))
        {
          $this->object->{$mapDefinition['column']} =
            $this->content($mapDefinition['transformationLogic']($this, $value));
        }
        else
        {
          $this->object->{$mapDefinition['column']} = $this->content($value);
        }
      }
      else
      {
        $this->object->{$mapDefinition} = $this->content($value);
      }
    }
  }

  /**
   * Handle mapping of column, containing multiple values delimited by a
   * character, to an array. Any values set to 'NULL' will be filtered out.
   *
   * @param string $column  column name
   * @param array $delimiter  delimiting character
   * @param string $value  column value
   *
   * @return void
   */
  public function arrayColumnHandler($column, $delimiter, $value)
  {
    if ($value)
    {
      $this->rowStatusVars[$column] = explode($delimiter, $value);
    }
  }


  /*
   *
   *  Qubit data helpers
   *  ------------------
   */

  /**
   * Issue an SQL query
   *
   * @param string $query  SQL query
   * @param string $params  values to map to placeholders (optional)
   *
   * @return object  database statement object
   */
  public static function sqlQuery($query, $params = array())
  {
    $connection = Propel::getConnection();
    $statement = $connection->prepare($query);
    for($index = 0; $index < count($params); $index++)
    {
      $statement->bindValue($index + 1, $params[$index]);
    }
    $statement->execute();
    return $statement;
  }

  /**
   * Create one or more Qubit notes of a certain type
   *
   * @param integer $typeId  term ID of note type
   * @param string $array  Note text items
   * @param closure $transformationLogic  logic to manipulate note text
   *
   * @return array  Notes created
   */
  public function createOrUpdateNotes($typeId, $textArray, $transformationLogic = false)
  {
    $noteIds = array();

    // I18n row handler
    if ($this->columnValue('culture') != $this->object->sourceCulture)
    {
      $query = "SELECT id FROM note WHERE object_id = ? AND type_id = ?;";

      $statement = self::sqlQuery($query, array(
        $this->object->id, $typeId));

      while ($noteId = $statement->fetchColumn())
      {
        $noteIds[] = $noteId;
      }
    }

    foreach ($textArray as $i => $text)
    {
      $options = array();

      if ($transformationLogic)
      {
        $options['transformationLogic'] = $transformationLogic;
      }

      if (isset($noteIds[$i]))
      {
        $options['noteId'] = $noteIds[$i];
      }

      $this->createOrUpdateNote($typeId, $text, $options);

    }
  }

  /**
   * Create a Qubit note
   *
   * @param integer $typeId  term ID of note type
   * @param string $text  Note text
   * @param closure $transformationLogic  logic to manipulate note text
   *
   * @return QubitNote  created note
   */
  public function createOrUpdateNote($typeId, $text, $options = array())
  {
    // Trim whitespace
    $text = trim($text);

    if (isset($options['noteId']))
    {
      // Clearing the cache seems to prevent a weird issue with trying to save
      // a cached version of the note? In any case, it makes it work (!?)
      QubitNote::clearCache();

      $note = QubitNote::getById($options['noteId']);
    }
    else
    {
      $note = new QubitNote;
      $note->objectId = $this->object->id;
      $note->typeId = $typeId;
    }

    if (isset($options['transformationLogic']))
    {
      $transformer = $options['transformationLogic'];
      $text = $transformer($this, $text);
    }

    $note->content = $this->content($text);
    $note->culture = $this->columnValue('culture');
    $note->save();

    return $note;
  }

  /**
   * Create a Qubit event, or add an i18n row to existing event
   *
   * @param integer $typeId  term ID of event type
   * @param array $options  option parameter
   *
   * @return QubitEvent  created event
   */
  public function createOrUpdateEvent($typeId, $options = array())
  {
    if (isset($options['eventId']))
    {
      // Adding new i18n values to an existing event
      $event = QubitEvent::getById($options['eventId']);
      unset($options['eventId']);
    }
    else
    {
      // Create new event
      $event = new QubitEvent;
      $event->objectId = $this->object->id;
      $event->typeId = $typeId;
    }

    if (null === $event)
    {
      // Couldn't find event
      return;
    }

    $allowedProperties = array('date', 'description', 'startDate', 'endDate', 'typeId');
    $ignoreOptions  = array('actorName', 'actorHistory', 'place');

    $this->setPropertiesFromArray($event, $options, $allowedProperties, $ignoreOptions);

    // Save actor history in untitled actor if there is actorHistory without actorName
    if (isset($options['actorHistory']) && !isset($options['actorName']))
    {
      $options['actorName'] = '';
    }

    if (isset($options['actorName']))
    {
      if (isset($event->actorId))
      {
        // Update i18n values
        $event->actor->authorizedFormOfName = $options['actorName'];
        if (isset($options['actorHistory']))
        {
          $event->actor->history = $options['actorHistory'];
        }

        $event->actor->save();
      }
      else
      {
        // Link actor
        $actorOptions = array();
        if (isset($options['actorHistory']))
        {
          $actorOptions['history'] = $options['actorHistory'];
        }

        $actor = $this->createOrFetchActor($options['actorName'], $actorOptions);
        $event->actorId = $actor->id;
      }
    }

    $event->save();

    // Add relation with place
    if (isset($options['place']))
    {
      $placeTerm = $this->createOrFetchTerm(QubitTaxonomy::PLACE_ID, $options['place']);
      $this->createObjectTermRelation($event->id, $placeTerm->id);
    }

    return $event;
  }

  /**
   * Create a Qubit physical object or, if one already exists, fetch it
   *
   * @param string $name  name of physical object
   * @param string $location  location of physical object
   *
   * @return QubitPhysicalObject  created or fetched physical object
   */
  public function createOrFetchPhysicalObject($name, $location, $typeId)
  {
    $query = "SELECT id FROM physical_object_i18n WHERE name=? AND location=?";

    $statement = QubitFlatfileImport::sqlQuery($query, array($name, $location));
    $result = $statement->fetch(PDO::FETCH_OBJ);

    if ($result)
    {
      return QubitPhysicalObject::getById($result->id);
    }
    else
    {
      return $this->createPhysicalObject($name, $location, $typeId);
    }
  }

  /**
   * Create a Qubit repository or, if one already exists, fetch it
   *
   * @param string $name  name of repository
   *
   * @return QubitRepository  created or fetched repository
   */
  public static function createOrFetchRepository($name)
  {
    $query = "SELECT r.id FROM actor_i18n a \r
      INNER JOIN repository r ON a.id=r.id \r
      WHERE a.authorized_form_of_name=?";

    $statement = QubitFlatfileImport::sqlQuery($query, array($name));
    $result = $statement->fetch(PDO::FETCH_OBJ);

    if ($result && strlen($name) > 0)
    {
      return QubitRepository::getById($result->id);
    }
    else
    {
      return QubitFlatfileImport::createRepository($name);
    }
  }

  /**
   * Create a Qubit actor or, if one already exists, fetch it
   *
   * @param string $name     name of actor
   * @param string $options  optional data
   *
   * @return QubitActor  created or fetched actor
   */
  public static function createOrFetchActor($name, $options = array())
  {
    // Get actor or create a new one (don't match untitled actors).
    // If the actor exists the data is not overwritten
    if ($name == '' || null === $actor = QubitActor::getByAuthorizedFormOfName($name))
    {
      $actor = QubitFlatfileImport::createActor($name, $options);
    }

    return $actor;
  }

  /**
   * Create a Qubit rights holder or, if one already exists, fetch it
   *
   * @param string $name  name of rights holder
   *
   * @return QubitRightsHolder  created or fetched rights holder
   */
  public function createOrFetchRightsHolder($name)
  {
    $query = "SELECT object.id
      FROM object JOIN actor_i18n i18n
      ON object.id = i18n.id
      WHERE i18n.authorized_form_of_name = ?
      AND object.class_name = 'QubitRightsHolder';";

    $statement = QubitFlatfileImport::sqlQuery($query, array($name));

    $result = $statement->fetch(PDO::FETCH_OBJ);

    if (!$result)
    {
      $rightsHolder = new QubitRightsHolder;
      $rightsHolder->authorizedFormOfName = $name;
      $rightsHolder->save();
    }
    else
    {
      $rightsHolder = QubitRightsHolder::getById($result->id);
    }

    return $rightsHolder;
  }

  /**
   * Create a QubitDonor or, if one already exists, fetch it
   *
   * @param string $name     name of donor
   *
   * @return QubitDonor  created or fetched donor
   */
  public function createOrFetchDonor($name)
  {
    $query = "SELECT object.id
      FROM object JOIN actor_i18n i18n
      ON object.id = i18n.id
      WHERE i18n.authorized_form_of_name = ?
      AND object.class_name = 'QubitDonor';";

    $statement = QubitFlatfileImport::sqlQuery($query, array($name));

    $result = $statement->fetch(PDO::FETCH_OBJ);

    if (!$result)
    {
      $donor = new QubitDonor;
      $donor->authorizedFormOfName = $name;
      $donor->save();
    }
    else
    {
      $donor = QubitDonor::getById($result->id);
    }

    return $donor;
  }

  /**
   * Create Qubit contract information for an actor or, if it already exists,
   * fetch it
   *
   * @param integer $actorId  ID of actor
   * @param string $options  contact information creation properties
   *
   * @return QubitContactInformation  created or fetched contact info
   */
  public function createOrFetchContactInformation($actorId, $options)
  {
    $query = "SELECT id FROM contact_information WHERE actor_id=?";

    $statement = QubitFlatfileImport::sqlQuery($query, array($actorId));
    $result = $statement->fetch(PDO::FETCH_OBJ);

    if ($result)
    {
      return QubitContactInformation::getById($result->id);
    }
    else
    {
      return $this->createContactInformation($actorId, $options);
    }
  }

  /**
   * Create Qubit contact information for an actor
   *
   * @param string $actorId  ID of actor
   * @param string $options  property values for new object
   *
   * @return QubitContactInformation  created contact information
   */
  public function createContactInformation($actorId, $options)
  {
    $info = new QubitContactInformation;
    $info->actorId = $actorId;

    $allowedProperties = array(
      'email',
      'telephone',
      'streetAddress',
      'city',
      'region',
      'postalCode'
    );

    $this->setPropertiesFromArray($info, $options, $allowedProperties);
    $info->save();

    return $info;
  }

  /**
   * Create a Qubit term or, if it already exists, fetch it
   *
   * @param integer $taxonomyId  term taxonomy
   * @param string $name  name of term
   * @param string $culture  culture code (defaulting to English)
   *
   * @return object  created term or fetched object containing term data
   */
  public static function createOrFetchTerm($taxonomyId, $name, $culture = 'en')
  {
    $query = "SELECT t.id FROM term t LEFT JOIN term_i18n ti ON t.id=ti.id \r
      WHERE t.taxonomy_id=? AND ti.name=? AND ti.culture=?";
    $statement = QubitFlatfileImport::sqlQuery(
      $query,
      array($taxonomyId, $name, $culture)
    );
    $result = $statement->fetch(PDO::FETCH_OBJ);
    if ($result)
    {
      return QubitTerm::getById($result->id);
    }
    else
    {
      return QubitFlatfileImport::createTerm($taxonomyId, $name, $culture);
    }
  }

  /**
   * Create a Qubit term
   *
   * @param integer $taxonomyId  term taxonomy
   * @param string $name  name of term
   * @param string $culture  culture code (defaulting to English)
   *
   * @return QubitTerm  created term
   */
  public static function createTerm($taxonomyId, $name, $culture = 'en')
  {
    $term = new QubitTerm;
    $term->name = $name;
    $term->taxonomyId = $taxonomyId;
    $term->parentId = QubitTerm::ROOT_ID;
    $term->culture = $culture;
    $term->save();

    return $term;
  }

  /**
   * Create a Qubit physical object
   *
   * @param string $name  name of physical object
   * @param string $location  location of physical object
   * @param integer $typeId  physical object type ID
   * @return QubitPhysicalObject  created physical object
   */
  public function createPhysicalObject($name, $location, $typeId)
  {
    $object = new QubitPhysicalObject;
    $object->name = $name;
    $object->typeId = $typeId;

    if ($location)
    {
      $object->location = $location;
    }

    $object->save();
    return $object;
  }

  /**
   * Create a Qubit repository
   *
   * @param string $name  name of repository
   *
   * @return QubitRepository  created repository
   */
  public static function createRepository($name)
  {
    $repo = new QubitRepository;
    $repo->authorizedFormOfName = $name;
    $repo->save();
    return $repo;
  }

  /**
   * Create a Qubit actor
   *
   * @param string $name  name of actor
   * @param string $history  history of actor (optional)
   *
   * @return QubitActor  created actor
   */
  private static function createActor($name, $options = array())
  {
    $actor = new QubitActor;
    $actor->parentId = QubitActor::ROOT_ID;
    $actor->authorizedFormOfName = $name;

    if (isset($options['history']))
    {
      $actor->history = $options['history'];
    }

    if (isset($options['entityTypeId']))
    {
      $actor->entityTypeId = $options['entityTypeId'];
    }

    $actor->save();

    return $actor;
  }

  /**
   * Create a relation between two Qubit objects
   *
   * @param integer $subjectId  subject ID
   * @param integer $objectId  object ID
   * @param integer $typeId  relation type
   *
   * @return QubitRelation  created relation
   */
  public function createRelation($subjectId, $objectId, $typeId)
  {
    $relation = new QubitRelation;
    $relation->subjectId = $subjectId;
    $relation->objectId  = $objectId;
    $relation->typeId    = $typeId;
    $relation->save();
    return $relation;
  }

  /**
   * Create a relation between a term and a Qubit object
   *
   * @param integer $objectId  object ID
   * @param integer $termId  term ID
   *
   * @return QubitObjectTermRelation  created relation
   */
  public function createObjectTermRelation($objectId, $termId)
  {
    $relation = new QubitObjectTermRelation;
    $relation->termId = $termId;
    $relation->objectId = $objectId;
    $relation->save();

    return $relation;
  }

  /**
   * Create a term and relate it to an object
   *
   * @param integer $taxonomyId  taxonomy ID
   * @param string $name  name of term
   * @param string $culture  culture code (defaulting to English)
   *
   * @return void
   */
  public function createAccessPoint($taxonomyId, $name, $culture = null)
  {
    if (null === $culture)
    {
      $culture = sfContext::getInstance()->user->getCulture();
    }

    $query = "SELECT t.id FROM term t \r
      LEFT JOIN term_i18n ti ON t.id=ti.id \r
      WHERE t.taxonomy_id=? AND ti.name=? AND ti.culture=?";
    $statement = $this->sqlQuery($query, array($taxonomyId, $name, $culture));

    if (!($term = $statement->fetch(PDO::FETCH_OBJ)))
    {
      $term = $this->createTerm($taxonomyId, $name, $culture);
    }

    $this->createObjectTermRelation($this->object->id, $term->id);
  }

  /**
   * Get the terms in a taxonomy, optionally specifying culture
   *
   * @param integer $taxonomyId  taxonomy ID
   * @param string $culture  culture code (defaulting to English)
   *
   * @return array  array of term IDs and their respective names
   */
  public static function getTaxonomyTerms($taxonomyId, $culture = 'en')
  {
    $terms = array();

    $query = "SELECT * FROM term t \r
      LEFT JOIN term_i18n ti ON t.id=ti.id AND ti.culture=? \r
      WHERE taxonomy_id=?";
    $statement = QubitFlatfileImport::sqlQuery($query, array($culture, $taxonomyId));

    while($term = $statement->fetch(PDO::FETCH_OBJ))
    {
      $terms[$term->id] = $term;
    }

    return $terms;
  }

  /**
   * Get a term's ID using its name, optionally specifying culture
   *
   * @param integer $taxonomyId  taxonomy ID
   * @param string $termName  term name
   * @param string $culture  culture code (defaulting to English)
   *
   * @return integer/boolean  term ID/false
   */
  public function getTaxonomyTermIdUsingName($taxonomyId, $termName, $culture = 'en')
  {
    global $cachedTaxonomyTerms;

    $cachedTaxonomyTerms = (isset($cachedTaxonomyTerms))
      ? $cachedTaxonomyTerms
      : array();

    $cachedTaxonomyTerms[$culture] = (isset($cachedTaxonomyTerms[$culture]))
      ? $cachedTaxonomyTerms[$culture]
      : array();

    if (!isset($cachedTaxonomyTerms[$culture][$taxonomyId]))
    {
      $cachedTaxonomyTerms[$culture][$taxonomyId] = array();
      foreach (QubitFlatfileImport::getTaxonomyTerms($taxonomyId, $culture) as $term)
      {
        $cachedTaxonomyTerms[$culture][$taxonomyId][$term->id] = $term->name;
      }
    }

    return array_search($termName, $cachedTaxonomyTerms[$culture][$taxonomyId]);
  }

  /**
   * Load terms from one or more taxonomies and use the terms to populate one
   * or more array elements.
   *
   * @param array $taxonomies  array of taxonomy IDs and identifying names
   *
   * @return array  array of arrays containing taxonomy terms
   */
  public static function loadTermsFromTaxonomies($taxonomies)
  {
    $taxonomyTerms = array();

    foreach ($taxonomies as $taxonomyId => $varName)
    {
      $taxonomyTerms[$varName] = array();
      foreach (QubitFlatfileImport::getTaxonomyTerms($taxonomyId) as $termId => $term)
      {
        $taxonomyTerms[$varName][$termId] = $term->name;
      }
    }

    return $taxonomyTerms;
  }

  /**
   * Create a Qubit right and relate it to an information object. Valid
   * options include the ID of the rights holder (rightholderId), the ID of
   * the basis term (basisID), the ID of the act term (actID), and the ID of
   * the copyright status term (copyrightStatusId).
   *
   * @param array $options  options
   *
   * @return QubitRelation  result object
   */
  public function createRightAndRelation($options)
  {
    // add right
    $right = new QubitRights;

    $allowedProperties = array(
      'rightsHolderId',
      'basisId',
      'actId',
      'copyrightStatusId',
      'restriction',
      'endDate'
    );

    $this->setPropertiesFromArray($right, $options, $allowedProperties);
    $right->save();

    return $this->createRelation($this->object->id, $right->id, QubitTerm::RIGHT_ID);

    return $relation;
  }

  /**
   * Fetch keymap an entity's Qubit object ID (target ID) by looking up its
   * legacy ID (source ID), the name of the import where it was mapped (source
   * name), and the type of entity (target name)
   *
   * @param integer $sourceId  source ID
   * @param string $sourceName  source name
   * @param string $targetName  target name
   *
   * @return stdClass  result object
   */
  public function fetchKeymapEntryBySourceAndTargetName($sourceId, $sourceName, $targetName)
  {
    $query = "SELECT target_id FROM keymap \r
      WHERE source_id=? AND source_name=? AND target_name=?";

    $statement = QubitFlatfileImport::sqlQuery(
      $query,
      array($sourceId, $sourceName, $targetName)
    );

    return $statement->fetch(PDO::FETCH_OBJ);
  }

  /**
   * Map a value to its corresponding term name then return the term ID
   * corresponding to the term name
   *
   * @param string $description  description of subject (for error output)
   * @param string $value  value that needs to be mapped to a term ID
   * @param array $valueToTermNameMap  array mapping possible values to term names
   * @param array $terms  array mapping term IDs to term names
   *
   * @return integer  term ID
   */
  public function translateNameToTermId($description, $value, $valueToTermNameMap, $terms)
  {
    if (isset($valueToTermNameMap[$value]) || count($valueToTermNameMap) == 0)
    {
      $termName = (count($valueToTermNameMap)) ? $valueToTermNameMap[$value] : $value;

      if (in_array($termName, $terms))
      {
        $termId = array_search($termName, $terms);
        return $termId;
      }
      else
      {
        throw new sfException('Could not find "'. $termName .'" in '. $description .' terms array.');
      }
    }
    else
    {
      throw new sfException('Could not find a way to handle '. $description .' value "'. $value .'".');
    }
  }
}
